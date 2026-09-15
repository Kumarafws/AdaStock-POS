<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\RefundMethod;
use App\Enums\SalesReturnCondition;
use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\PosService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected User $admin;
    protected Location $store;
    protected Location $quarantine;
    protected CashierShift $activeShift;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->cashier = User::where('username', 'cashier')->first();
        $this->admin = User::where('username', 'admin')->first();
        $this->store = Location::where('code', 'STR-01')->first();
        $this->quarantine = Location::where('code', 'QRN-01')->first();

        // Open shift for cashier
        $shiftService = app(ShiftService::class);
        $this->activeShift = $shiftService->openShift(
            cashier: $this->cashier,
            store: $this->store,
            startingCash: 250000
        );

        $this->product = Product::with('units')->first();
    }

    /**
     * Helper to create a completed POS sale.
     */
    protected function createCompletedSale(int $quantity = 5): Sale
    {
        $unitPrice = (float) $this->product->default_selling_price;
        $total = $quantity * $unitPrice;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $quantity,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $total,
                ],
            ],
            'discount_amount' => 0,
        ]);

        $response->assertStatus(200);

        return Sale::where('sale_number', $response->json('sale_number'))->firstOrFail();
    }

    public function test_cannot_access_returns_create_without_active_shift(): void
    {
        // Cashier without active shift
        $cashierWithoutShift = User::factory()->create([
            'username' => 'cashier_no_shift',
            'role' => \App\Enums\UserRole::CASHIER,
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($cashierWithoutShift)->get('/returns/create');
        $response->assertRedirect(route('shifts.create'));
    }

    public function test_lookup_sale_by_invoice_number_returns_correct_data(): void
    {
        $sale = $this->createCompletedSale(quantity: 4);

        $response = $this->actingAs($this->cashier)->getJson('/returns/lookup-sale?query=' . $sale->sale_number);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                ],
            ]);

        $items = $response->json('sale.items');
        $this->assertCount(1, $items);
        $this->assertEquals(4, $items[0]['quantity_purchased']);
        $this->assertEquals(0, $items[0]['quantity_returned']);
        $this->assertEquals(4, $items[0]['quantity_remaining']);
        $this->assertTrue($items[0]['is_returnable']);
    }

    public function test_lookup_sale_fails_for_voided_sale(): void
    {
        $sale = $this->createCompletedSale(quantity: 3);

        // Void the sale
        $posService = app(PosService::class);
        $posService->voidSale($sale, $this->cashier, $this->admin, 'Pembatalan transaksi');

        $response = $this->actingAs($this->cashier)->getJson('/returns/lookup-sale?query=' . $sale->sale_number);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertStringContainsString('dibatalkan (void)', $response->json('message'));
    }

    public function test_successful_partial_sales_return_with_good_condition_routes_to_store(): void
    {
        $sale = $this->createCompletedSale(quantity: 5);
        $saleItem = $sale->items->first();

        $storeInventoryBefore = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');

        $shiftCashBefore = (float) $this->activeShift->fresh()->total_sales_cash;
        $returnQty = 2;
        $expectedRefund = $returnQty * (float) $saleItem->unit_price;

        $response = $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Salah beli ukuran',
            'notes' => 'Kemasan masih mulus dan utuh',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => $returnQty,
                    'condition' => 'good',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify SalesReturn document created
        $this->assertDatabaseHas('sales_returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'total_refund_amount' => $expectedRefund,
            'reason' => 'Salah beli ukuran',
        ]);

        $salesReturn = SalesReturn::latest('id')->first();
        $this->assertEquals($expectedRefund, (float) $salesReturn->total_refund_amount);

        // Verify SalesReturnItem
        $this->assertDatabaseHas('sales_return_items', [
            'sales_return_id' => $salesReturn->id,
            'sale_item_id' => $saleItem->id,
            'product_id' => $this->product->id,
            'quantity' => $returnQty,
            'condition' => 'good',
            'destination_location_id' => $this->store->id,
            'refund_amount' => $expectedRefund,
        ]);

        // Verify Store inventory increased by 2
        $storeInventoryAfter = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');
        $this->assertEquals($storeInventoryBefore + $returnQty, $storeInventoryAfter);

        // Verify stock movement ledger
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'quantity' => $returnQty,
            'movement_type' => MovementType::SALE_RETURN->value,
            'reference_type' => SalesReturn::class,
            'reference_id' => $salesReturn->id,
        ]);

        // Verify cashier shift cash deducted
        $shiftCashAfter = (float) $this->activeShift->fresh()->total_sales_cash;
        $this->assertEquals($shiftCashBefore - $expectedRefund, $shiftCashAfter);

        // Verify Sale status is RETURNED_PARTIAL
        $this->assertEquals(SaleStatus::RETURNED_PARTIAL, $sale->fresh()->status);
    }

    public function test_successful_sales_return_with_damaged_condition_routes_to_quarantine(): void
    {
        $sale = $this->createCompletedSale(quantity: 4);
        $saleItem = $sale->items->first();

        $storeInventoryBefore = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');

        $quarantineInventoryBefore = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->quarantine->id)
            ->value('quantity') ?? 0;

        $returnQty = 1;

        $response = $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Barang bocor / cacat kemasan',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => $returnQty,
                    'condition' => 'damaged',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Store inventory must NOT increase
        $storeInventoryAfter = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');
        $this->assertEquals($storeInventoryBefore, $storeInventoryAfter);

        // Quarantine inventory must increase
        $quarantineInventoryAfter = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->quarantine->id)
            ->value('quantity');
        $this->assertEquals($quarantineInventoryBefore + $returnQty, $quarantineInventoryAfter);

        // Item destination location must be quarantine
        $returnItem = SalesReturnItem::latest('id')->first();
        $this->assertEquals($this->quarantine->id, $returnItem->destination_location_id);
        $this->assertEquals(SalesReturnCondition::DAMAGED, $returnItem->condition);
    }

    public function test_full_sales_return_updates_sale_status_to_returned_full(): void
    {
        $sale = $this->createCompletedSale(quantity: 3);
        $saleItem = $sale->items->first();

        $response = $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Pengembalian seluruh barang',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 3, // All 3 items returned
                    'condition' => 'good',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Sale status must be RETURNED_FULL
        $this->assertEquals(SaleStatus::RETURNED_FULL, $sale->fresh()->status);

        // Subsequent lookup must be rejected
        $lookupResponse = $this->actingAs($this->cashier)->getJson('/returns/lookup-sale?query=' . $sale->sale_number);
        $lookupResponse->assertStatus(422);
        $this->assertStringContainsString('sudah selesai diretur', $lookupResponse->json('message'));
    }

    public function test_cannot_return_more_quantity_than_remaining(): void
    {
        $sale = $this->createCompletedSale(quantity: 2);
        $saleItem = $sale->items->first();

        $response = $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Kelebihan kuantitas',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 5, // More than 2
                    'condition' => 'good',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('melebihi sisa yang dapat diretur', $response->json('message'));
    }

    public function test_cannot_return_from_voided_sale(): void
    {
        $sale = $this->createCompletedSale(quantity: 2);
        $saleItem = $sale->items->first();

        // Void the sale
        $posService = app(PosService::class);
        $posService->voidSale($sale, $this->cashier, $this->admin, 'Salah transaksi');

        $response = $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Percobaan retur transaksi void',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'condition' => 'good',
                ],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('dibatalkan (void)', $response->json('message'));
    }

    public function test_sales_returns_index_displays_data_and_kpis(): void
    {
        $sale = $this->createCompletedSale(quantity: 2);
        $saleItem = $sale->items->first();

        $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Cacat pabrik',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'condition' => 'damaged',
                ],
            ],
        ]);

        $return = SalesReturn::latest('id')->first();

        $response = $this->actingAs($this->cashier)->get('/returns');
        $response->assertStatus(200);
        $response->assertSee($return->return_number);
        $response->assertSee($sale->sale_number);
    }

    public function test_sales_return_show_displays_details_and_movements(): void
    {
        $sale = $this->createCompletedSale(quantity: 2);
        $saleItem = $sale->items->first();

        $this->actingAs($this->cashier)->postJson('/returns', [
            'sale_id' => $sale->id,
            'refund_method' => 'cash',
            'reason' => 'Cacat pabrik',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'condition' => 'damaged',
                ],
            ],
        ]);

        $return = SalesReturn::latest('id')->first();

        $response = $this->actingAs($this->admin)->get('/returns/' . $return->id);
        $response->assertStatus(200);
        $response->assertSee($return->return_number);
        $response->assertSee($this->quarantine->name);
        $response->assertSee('Audit Mutasi Stok');
    }
}
