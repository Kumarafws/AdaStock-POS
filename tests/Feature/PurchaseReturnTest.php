<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Supplier $supplier;
    protected Location $warehouse;
    protected Location $quarantine;
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
        $this->quarantine = Location::where('code', 'QRN-01')->first();
        $this->product = Product::first();
    }

    public function test_manager_can_access_purchase_return_pages_cashier_is_forbidden(): void
    {
        $this->actingAs($this->admin)->get('/purchasing/returns')->assertStatus(200);
        $this->actingAs($this->manager)->get('/purchasing/returns')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/purchasing/returns')->assertStatus(403);

        $this->actingAs($this->manager)->get('/purchasing/returns/create')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/purchasing/returns/create')->assertStatus(403);
    }

    public function test_manager_can_create_purchase_return_and_deduct_inventory(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $initialStock = $inventoryService->getStock($this->product, $this->warehouse);

        $returnQty = 10;

        $response = $this->actingAs($this->manager)->post('/purchasing/returns', [
            'supplier_id' => $this->supplier->id,
            'location_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Barang kemasan penyok / bocor saat pengiriman',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $returnQty,
                    'unit_cost' => $this->product->purchase_price,
                    'notes' => '10 pcs kemasan bocor',
                ]
            ]
        ]);

        $return = PurchaseReturn::latest('id')->first();
        $this->assertNotNull($return);
        $response->assertRedirect("/purchasing/returns/{$return->id}");

        // Assert physical inventory deducted
        $this->assertEquals($initialStock - $returnQty, $inventoryService->getStock($this->product, $this->warehouse));

        // Assert StockMovement recorded with PURCHASE_RETURN
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->warehouse->id,
            'movement_type' => MovementType::PURCHASE_RETURN->value,
            'quantity' => -$returnQty,
            'reference_type' => PurchaseReturn::class,
            'reference_id' => $return->id,
        ]);
    }

    public function test_manager_can_return_goods_from_quarantine_location(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $initialQuarantineStock = $inventoryService->getStock($this->product, $this->quarantine);

        $this->assertGreaterThanOrEqual(1, $initialQuarantineStock);

        $response = $this->actingAs($this->manager)->post('/purchasing/returns', [
            'supplier_id' => $this->supplier->id,
            'location_id' => $this->quarantine->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Retur barang rusak dari karantina ke distributor',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_cost' => $this->product->purchase_price,
                ]
            ]
        ]);

        $return = PurchaseReturn::latest('id')->first();
        $this->assertNotNull($return);

        $this->assertEquals($initialQuarantineStock - 1, $inventoryService->getStock($this->product, $this->quarantine));

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->quarantine->id,
            'movement_type' => MovementType::PURCHASE_RETURN->value,
            'quantity' => -1,
        ]);
    }
}
