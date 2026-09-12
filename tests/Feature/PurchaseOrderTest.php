<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Supplier $supplier;
    protected Location $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();

        $this->supplier = Supplier::first();
        $this->warehouse = Location::where('code', 'WHS-01')->first();
        $this->product = Product::with('units')->first();
    }

    public function test_manager_and_admin_can_access_po_pages_cashier_is_forbidden(): void
    {
        $this->actingAs($this->admin)->get('/purchasing/orders')->assertStatus(200);
        $this->actingAs($this->manager)->get('/purchasing/orders')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/purchasing/orders')->assertStatus(403);

        $this->actingAs($this->manager)->get('/purchasing/orders/create')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/purchasing/orders/create')->assertStatus(403);
    }

    public function test_manager_can_create_purchase_order_with_multi_units(): void
    {
        $dusUnit = $this->product->units->first(); // Dus (40x)

        $response = $this->actingAs($this->manager)->post('/purchasing/orders', [
            'supplier_id' => $this->supplier->id,
            'destination_location_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Pengadaan stok mi instan reguler',
            'tax_amount' => 10000,
            'discount_amount' => 5000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_unit_id' => $dusUnit?->id,
                    'ordered_quantity' => 10, // 10 Dus
                    'unit_cost' => 100000, // Rp 100.000 per Dus
                ]
            ]
        ]);

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $response->assertRedirect("/purchasing/orders/{$po->id}");

        $this->assertEquals(PurchaseOrderStatus::ORDERED, $po->status);
        $this->assertEquals($this->supplier->id, $po->supplier_id);
        $this->assertEquals($this->warehouse->id, $po->destination_location_id);
        $this->assertEquals(1000000, $po->subtotal); // 10 * 100.000
        $this->assertEquals(1005000, $po->total_amount); // 1.000.000 + 10.000 - 5.000

        // Check PO item conversion
        $item = $po->items->first();
        $this->assertNotNull($item);
        $this->assertEquals($this->product->id, $item->product_id);
        $this->assertEquals(10, $item->ordered_quantity);

        $factor = $dusUnit ? $dusUnit->conversion_factor : 1;
        $this->assertEquals(10 * $factor, $item->ordered_quantity_base);
        $this->assertEquals(0, $item->received_quantity_base);
    }

    public function test_manager_can_cancel_purchase_order(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO/TEST/001',
            'supplier_id' => $this->supplier->id,
            'destination_location_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::ORDERED,
            'subtotal' => 500000,
            'total_amount' => 500000,
            'created_by' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->post("/purchasing/orders/{$po->id}/cancel", [
                'cancel_reason' => 'Supplier kehabisan stok'
            ]);

        $response->assertRedirect("/purchasing/orders/{$po->id}");
        $this->assertEquals(PurchaseOrderStatus::CANCELLED, $po->fresh()->status);
    }
}
