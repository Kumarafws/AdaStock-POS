<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\CostingService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Location $warehouse;
    protected Location $store;
    protected Location $quarantine;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();

        $this->warehouse = Location::where('code', 'WHS-01')->first();
        $this->store = Location::where('code', 'STR-01')->first();
        $this->quarantine = Location::where('code', 'QRN-01')->first();

        $this->product = Product::first();
    }

    public function test_authenticated_users_can_view_inventory_monitor(): void
    {
        $this->actingAs($this->admin)->get('/inventory')->assertStatus(200)->assertSee('Saldo Persediaan Real-Time');
        $this->actingAs($this->manager)->get('/inventory')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/inventory')->assertStatus(200);
    }

    public function test_cashier_is_scoped_to_assigned_store_only(): void
    {
        $response = $this->actingAs($this->cashier)->get('/inventory');

        $response->assertStatus(200);
        $response->assertSee($this->store->name);
        // Cashier should not see warehouse selector
        $response->assertDontSee('Semua Lokasi');
    }

    public function test_manager_and_admin_can_view_stock_ledger_cashier_is_forbidden(): void
    {
        $this->actingAs($this->admin)->get('/inventory/ledger')->assertStatus(200);
        $this->actingAs($this->manager)->get('/inventory/ledger')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/inventory/ledger')->assertStatus(403);
    }

    public function test_check_stock_api_returns_accurate_balance_and_breakdown(): void
    {
        $currentStoreStock = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');

        $response = $this->actingAs($this->manager)
            ->getJson("/inventory/check-stock?product_id={$this->product->id}&location_id={$this->store->id}");

        $response->assertStatus(200)
            ->assertJson([
                'stock' => $currentStoreStock,
                'unit' => $this->product->base_unit_name,
            ]);
    }

    public function test_inventory_service_records_movement_with_immutable_audit_trail(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);

        $initialStock = $inventoryService->getStock($this->product, $this->store);

        // Record incoming adjustment
        $movement = $inventoryService->recordMovement(
            product: $this->product,
            location: $this->store,
            quantityInBaseUnit: 50,
            type: MovementType::ADJUSTMENT_IN,
            notes: 'Test stock addition',
            actor: $this->manager
        );

        $this->assertEquals($initialStock, $movement->balance_before);
        $this->assertEquals($initialStock + 50, $movement->balance_after);
        $this->assertEquals(50, $movement->quantity);
        $this->assertEquals($initialStock + 50, $inventoryService->getStock($this->product, $this->store));

        // Verify row exists in stock_movements table
        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'movement_type' => MovementType::ADJUSTMENT_IN->value,
            'quantity' => 50,
            'balance_after' => $initialStock + 50,
        ]);
    }

    public function test_inventory_service_prevents_negative_stock(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);

        $currentStock = $inventoryService->getStock($this->product, $this->store);
        $excessiveDeduction = -($currentStock + 100);

        $this->expectException(InsufficientStockException::class);

        $inventoryService->recordMovement(
            product: $this->product,
            location: $this->store,
            quantityInBaseUnit: $excessiveDeduction,
            type: MovementType::ADJUSTMENT_OUT,
            actor: $this->manager
        );
    }

    public function test_costing_service_calculates_moving_average_cost_accurately(): void
    {
        /** @var CostingService $costingService */
        $costingService = app(CostingService::class);

        // Create a dedicated fresh product for testing formula
        $testProduct = Product::create([
            'sku' => 'TEST-COST-01',
            'name' => 'Formula Test Item',
            'category_id' => \App\Models\Category::first()->id,
            'base_unit_name' => 'Pcs',
            'purchase_price' => 2000,
            'default_selling_price' => 3000,
            'min_stock' => 10,
        ]);

        // Put initial 100 units in warehouse
        Inventory::create([
            'product_id' => $testProduct->id,
            'location_id' => $this->warehouse->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);

        // Current: 100 units @ Rp 2.000 = Rp 200.000
        // Incoming: 50 units @ Rp 2.600 = Rp 130.000
        // Total: 150 units, Total Value: Rp 330.000
        // Expected Moving Average: 330.000 / 150 = Rp 2.200
        $newCost = $costingService->calculateAndApplyMovingAverage(
            product: $testProduct,
            incomingQtyInBaseUnit: 50,
            newPurchasePrice: 2600
        );

        $this->assertEquals(2200.00, $newCost);
        $this->assertEquals(2200.00, (float) $testProduct->fresh()->purchase_price);
    }
}
