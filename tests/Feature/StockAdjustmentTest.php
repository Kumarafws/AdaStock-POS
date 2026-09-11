<?php

namespace Tests\Feature;

use App\Enums\AdjustmentReason;
use App\Enums\MovementType;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
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

    public function test_manager_and_admin_can_access_adjustment_pages_cashier_is_forbidden(): void
    {
        $this->actingAs($this->admin)->get('/adjustments')->assertStatus(200);
        $this->actingAs($this->manager)->get('/adjustments')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/adjustments')->assertStatus(403);

        $this->actingAs($this->manager)->get('/adjustments/create')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/adjustments/create')->assertStatus(403);
    }

    public function test_manager_can_create_positive_stock_adjustment(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $initialStock = $inventoryService->getStock($this->product, $this->store);

        $response = $this->actingAs($this->manager)->post('/adjustments', [
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 25,
            'reason' => AdjustmentReason::FOUND->value,
            'action_type' => 'normal',
            'notes' => 'Barang ditemukan di bawah rak display',
        ]);

        $response->assertRedirect('/adjustments');
        $response->assertSessionHas('success');

        // Check StockAdjustment record
        $this->assertDatabaseHas('stock_adjustments', [
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 25,
            'reason' => AdjustmentReason::FOUND->value,
            'action_type' => 'normal',
        ]);

        // Check balance updated
        $this->assertEquals($initialStock + 25, $inventoryService->getStock($this->product, $this->store));

        // Check StockMovement recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'movement_type' => MovementType::ADJUSTMENT_IN->value,
            'quantity' => 25,
            'balance_after' => $initialStock + 25,
        ]);
    }

    public function test_manager_can_create_negative_stock_adjustment(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $initialStock = $inventoryService->getStock($this->product, $this->store);

        $response = $this->actingAs($this->manager)->post('/adjustments', [
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 10,
            'reason' => AdjustmentReason::LOSS->value,
            'action_type' => 'normal',
            'notes' => 'Selisih opname fisik berkurang',
        ]);

        $response->assertRedirect('/adjustments');
        $response->assertSessionHas('success');

        $this->assertEquals($initialStock - 10, $inventoryService->getStock($this->product, $this->store));

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'movement_type' => MovementType::ADJUSTMENT_OUT->value,
            'quantity' => -10,
            'balance_after' => $initialStock - 10,
        ]);
    }

    public function test_damaged_goods_flow_moves_stock_to_quarantine_location(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);

        $initialStoreStock = $inventoryService->getStock($this->product, $this->store);
        $initialQuarantineStock = $inventoryService->getStock($this->product, $this->quarantine);

        $damageQty = 4;

        $response = $this->actingAs($this->manager)->post('/adjustments', [
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => $damageQty,
            'reason' => AdjustmentReason::DAMAGE->value,
            'action_type' => 'to_quarantine',
            'notes' => 'Bungkus bocor / sobek saat handling',
        ]);

        $response->assertRedirect('/adjustments');
        $response->assertSessionHas('success');

        // Store stock decreased by damageQty
        $this->assertEquals($initialStoreStock - $damageQty, $inventoryService->getStock($this->product, $this->store));

        // Quarantine stock increased by damageQty
        $this->assertEquals($initialQuarantineStock + $damageQty, $inventoryService->getStock($this->product, $this->quarantine));

        // Two movements must exist
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->store->id,
            'movement_type' => MovementType::ADJUSTMENT_OUT->value,
            'quantity' => -$damageQty,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->quarantine->id,
            'movement_type' => MovementType::TRANSFER_IN->value,
            'quantity' => $damageQty,
        ]);
    }

    public function test_disposal_loss_movement_recorded_from_quarantine(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);

        $initialQuarantineStock = $inventoryService->getStock($this->product, $this->quarantine);
        $this->assertGreaterThanOrEqual(1, $initialQuarantineStock);

        $response = $this->actingAs($this->admin)->post('/adjustments', [
            'location_id' => $this->quarantine->id,
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 1,
            'reason' => AdjustmentReason::DAMAGE->value,
            'action_type' => 'disposal',
            'notes' => 'Pemusnahan resmi disaksikan manajer',
        ]);

        $response->assertRedirect('/adjustments');
        $response->assertSessionHas('success');

        $this->assertEquals($initialQuarantineStock - 1, $inventoryService->getStock($this->product, $this->quarantine));

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'location_id' => $this->quarantine->id,
            'movement_type' => MovementType::LOSS_DISPOSAL->value,
            'quantity' => -1,
        ]);
    }

    public function test_adjustment_fails_when_quantity_exceeds_current_stock(): void
    {
        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $currentStock = $inventoryService->getStock($this->product, $this->store);

        $response = $this->actingAs($this->manager)->post('/adjustments', [
            'location_id' => $this->store->id,
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => $currentStock + 999,
            'reason' => AdjustmentReason::LOSS->value,
            'action_type' => 'normal',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('stock_adjustments', [
            'product_id' => $this->product->id,
            'quantity' => $currentStock + 999,
        ]);
    }
}
