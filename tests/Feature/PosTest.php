<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\ShiftStatus;
use App\Models\CashierShift;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Location $store;
    protected CashierShift $activeShift;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->cashier = User::where('username', 'cashier')->first();
        $this->store = Location::where('code', 'STR-01')->first();

        // Open shift for cashier
        $shiftService = app(ShiftService::class);
        $this->activeShift = $shiftService->openShift(
            cashier: $this->cashier,
            store: $this->store,
            startingCash: 200000,
            notes: 'Modal awal tes'
        );

        // Product with stock from seeder (120 Pcs at STR-01)
        $this->product = Product::with('units')->first();
    }

    public function test_cashier_can_access_pos_screen_with_active_shift(): void
    {
        $response = $this->actingAs($this->cashier)->get('/pos');

        $response->assertStatus(200);
        $response->assertSee($this->activeShift->shift_number);
        $response->assertSee($this->store->name);
        $response->assertSee('Point of Sale');
    }

    public function test_cashier_can_search_products_via_api(): void
    {
        $response = $this->actingAs($this->cashier)->getJson('/pos/search?q=' . urlencode($this->product->name));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->product->id,
            'name' => $this->product->name,
        ]);
    }

    public function test_cashier_can_checkout_sale_with_cash_payment(): void
    {
        $qty = 3;
        $unitPrice = (float) $this->product->selling_price;
        $expectedTotal = $qty * $unitPrice;
        $paidCash = $expectedTotal + 10000;
        $expectedChange = 10000;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => null,
                    'quantity' => $qty,
                    'discount_amount' => 0,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $paidCash,
                ],
            ],
            'customer_name' => 'Budi Santoso',
            'discount_amount' => 0,
            'notes' => 'Pembelian tunai',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Assert sale recorded in database
        $this->assertDatabaseHas('sales', [
            'cashier_shift_id' => $this->activeShift->id,
            'user_id' => $this->cashier->id,
            'location_id' => $this->store->id,
            'customer_name' => 'Budi Santoso',
            'total_amount' => $expectedTotal,
            'paid_amount' => $paidCash,
            'change_amount' => $expectedChange,
            'status' => SaleStatus::COMPLETED->value,
        ]);

        // Assert sale_items recorded
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product->id,
            'quantity' => $qty,
            'quantity_base' => $qty,
            'unit_price' => $unitPrice,
        ]);

        // Assert sale_payments recorded
        $this->assertDatabaseHas('sale_payments', [
            'payment_method' => PaymentMethod::CASH->value,
            'amount' => $paidCash,
        ]);

        // Assert inventory stock decremented by 3 (from 120 to 117)
        $inventory = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->first();
        $this->assertEquals(117, $inventory->quantity);

        // Assert cashier shift counters updated
        $this->activeShift->refresh();
        $this->assertEquals($expectedTotal, $this->activeShift->total_sales_amount);
        $this->assertEquals($expectedTotal, $this->activeShift->total_sales_cash);
        $this->assertEquals(1, $this->activeShift->total_transactions_count);
    }

    public function test_cashier_can_checkout_with_multi_unit_uom(): void
    {
        $unit = $this->product->units->first(); // e.g. Dus (40 Pcs)
        $this->assertNotNull($unit, 'Product should have multi-units configured');

        $qty = 2; // 2 Dus = 80 Pcs
        $unitPrice = (float) $unit->selling_price;
        $expectedTotal = $qty * $unitPrice;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $unit->id,
                    'quantity' => $qty,
                    'discount_amount' => 0,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $expectedTotal,
                ],
            ],
            'customer_name' => 'Toko Mitra',
        ]);

        $response->assertStatus(200);

        // Assert inventory stock decremented by 2 * 40 = 80 pcs (from 120 to 40)
        $inventory = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->first();
        $this->assertEquals(40, $inventory->quantity);

        // Assert sale item stored unit details
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $this->product->id,
            'product_unit_id' => $unit->id,
            'unit_name' => $unit->unit_name,
            'conversion_factor' => $unit->conversion_factor,
            'quantity' => $qty,
            'quantity_base' => $qty * $unit->conversion_factor,
        ]);
    }

    public function test_cashier_can_checkout_with_split_payment(): void
    {
        $unitPrice = (float) $this->product->selling_price;
        $qty = 4;
        $total = $qty * $unitPrice;

        $cashPortion = round($total / 2);
        $qrisPortion = $total - $cashPortion;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $qty,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $cashPortion,
                ],
                [
                    'payment_method' => 'qris',
                    'amount' => $qrisPortion,
                    'reference_number' => 'QRIS-REF-998822',
                ],
            ],
            'customer_name' => 'Pelanggan QRIS',
        ]);

        $response->assertStatus(200);

        $this->activeShift->refresh();
        $this->assertEquals($total, $this->activeShift->total_sales_amount);
        $this->assertEquals($cashPortion, $this->activeShift->total_sales_cash);
        $this->assertEquals($qrisPortion, $this->activeShift->total_sales_non_cash);
        $this->assertEquals(1, $this->activeShift->total_transactions_count);

        $this->assertDatabaseHas('sale_payments', [
            'payment_method' => 'qris',
            'amount' => $qrisPortion,
            'reference_number' => 'QRIS-REF-998822',
        ]);
    }

    public function test_checkout_fails_when_store_stock_is_insufficient(): void
    {
        // Product has 100 pcs, attempt to buy 200 pcs
        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 200,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 1000000,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Stok tidak mencukupi', $response->json('message'));

        // Assert no sale was created
        $this->assertEquals(0, Sale::count());
    }

    public function test_checkout_fails_when_payment_amount_is_short(): void
    {
        $unitPrice = (float) $this->product->selling_price;
        $total = 2 * $unitPrice;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $total - 5000, // Short 5000
                ],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertEquals(0, Sale::count());
    }

    public function test_cashier_can_hold_and_recall_cart(): void
    {
        // 1. Hold cart
        $holdResponse = $this->actingAs($this->cashier)->postJson('/pos/hold', [
            'reference' => 'Ibu Maya - Titip Bayar',
            'customer_name' => 'Ibu Maya',
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'subtotal' => 7000,
                ],
            ],
            'discount_amount' => 0,
        ]);

        $holdResponse->assertStatus(200);
        $holdResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('held_carts', [
            'cashier_shift_id' => $this->activeShift->id,
            'reference' => 'Ibu Maya - Titip Bayar',
        ]);

        $heldCart = \App\Models\HeldCart::first();

        // 2. Recall cart
        $recallResponse = $this->actingAs($this->cashier)->postJson('/pos/recall/' . $heldCart->id);
        $recallResponse->assertStatus(200);
        $recallResponse->assertJson([
            'success' => true,
            'data' => [
                'reference' => 'Ibu Maya - Titip Bayar',
                'customer_name' => 'Ibu Maya',
            ],
        ]);

        // Held cart should now be deleted from queue
        $this->assertDatabaseMissing('held_carts', [
            'id' => $heldCart->id,
        ]);
    }

    public function test_cashier_can_view_thermal_receipt(): void
    {
        // Create a sale
        $posService = app(\App\Services\PosService::class);
        $sale = $posService->checkout(
            shift: $this->activeShift,
            cashier: $this->cashier,
            cartItems: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
            payments: [
                [
                    'payment_method' => 'cash',
                    'amount' => $this->product->selling_price,
                ],
            ],
            customerName: 'Pak Joko'
        );

        $response = $this->actingAs($this->cashier)->get('/pos/receipt/' . $sale->id);

        $response->assertStatus(200);
        $response->assertSee($sale->sale_number);
        $response->assertSee('Pak Joko');
        $response->assertSee($this->store->name);
        $response->assertSee('Cetak Struk');
    }
}
