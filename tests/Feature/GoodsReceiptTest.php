<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\PurchaseOrderStatus;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Supplier $supplier;
    protected Location $warehouse;
    protected Product $product;
    protected PurchaseOrder $po;
    protected PurchaseOrderItem $poItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();

        $this->supplier = Supplier::first();
        $this->warehouse = Location::where('code', 'WHS-01')->first();
        $this->product = Product::first();

        // Create a dedicated PO for testing receiving
        $this->po = PurchaseOrder::create([
            'po_number' => 'PO/TEST/RECEIPT/001',
            'supplier_id' => $this->supplier->id,
            'destination_location_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::ORDERED,
            'subtotal' => 300000,
            'total_amount' => 300000,
            'created_by' => $this->manager->id,
        ]);

        $this->poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $this->po->id,
            'product_id' => $this->product->id,
            'unit_name' => 'Pcs',
            'conversion_factor' => 1,
            'ordered_quantity' => 100,
            'ordered_quantity_base' => 100,
            'received_quantity_base' => 0,
            'unit_cost' => 3000,
            'base_unit_cost' => 3000,
            'subtotal' => 300000,
        ]);
    }

    public function test_manager_can_access_goods_receipt_pages_cashier_is_forbidden(): void
    {
        $this->actingAs($this->admin)->get('/purchasing/receipts')->assertStatus(200);
        $this->actingAs($this->manager)->get('/purchasing/receipts')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/purchasing/receipts')->assertStatus(403);

        $this->actingAs($this->manager)
            ->get("/purchasing/receipts/create?po_id={$this->po->id}")
            ->assertStatus(200);

        $this->actingAs($this->cashier)
            ->get("/purchasing/receipts/create?po_id={$this->po->id}")
            ->assertStatus(403);
    }

    public function test_partial_receiving_updates_po_to_partial_and_increments_stock(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $initialStock = $inventoryService->getStock($this->product, $this->warehouse);

        $response = $this->actingAs($this->manager)->post('/purchasing/receipts', [
            'purchase_order_id' => $this->po->id,
            'received_date' => now()->format('Y-m-d H:i:s'),
            'delivery_order_number' => 'SJ-SUP-001',
            'invoice_number' => 'INV-SUP-001',
            'items' => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'received_quantity_base' => 40, // 40 of 100
                    'actual_unit_cost' => 3000,
                ]
            ]
        ]);

        $receipt = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($receipt);
        $response->assertRedirect("/purchasing/receipts/{$receipt->id}");

        // Assert PO status updated to PARTIAL
        $this->assertEquals(PurchaseOrderStatus::PARTIAL, $this->po->fresh()->status);
        $this->assertEquals(40, $this->poItem->fresh()->received_quantity_base);
        $this->assertEquals(60, $this->poItem->fresh()->remaining_quantity_base);

        // Assert physical stock incremented
        $this->assertEquals($initialStock + 40, $inventoryService->getStock($this->product, $this->warehouse));

        // Assert StockMovement recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'movement_type' => MovementType::PURCHASE_RECEIPT->value,
            'quantity' => 40,
            'reference_type' => GoodsReceipt::class,
            'reference_id' => $receipt->id,
        ]);
    }

    public function test_full_receiving_completes_purchase_order(): void
    {
        // First batch: 40
        $this->actingAs($this->manager)->post('/purchasing/receipts', [
            'purchase_order_id' => $this->po->id,
            'received_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'received_quantity_base' => 40,
                ]
            ]
        ]);

        // Second batch: remaining 60
        $this->actingAs($this->manager)->post('/purchasing/receipts', [
            'purchase_order_id' => $this->po->id,
            'received_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'received_quantity_base' => 60,
                ]
            ]
        ]);

        // Assert PO is now RECEIVED
        $this->assertEquals(PurchaseOrderStatus::RECEIVED, $this->po->fresh()->status);
        $this->assertEquals(100, $this->poItem->fresh()->received_quantity_base);
        $this->assertTrue($this->poItem->fresh()->is_fully_received);
    }

    public function test_receiving_more_than_remaining_order_is_rejected(): void
    {
        $response = $this->actingAs($this->manager)->post('/purchasing/receipts', [
            'purchase_order_id' => $this->po->id,
            'received_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'received_quantity_base' => 150, // exceeds 100
                ]
            ]
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, $this->poItem->fresh()->received_quantity_base);
    }
}
