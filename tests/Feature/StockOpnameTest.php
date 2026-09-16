<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\OpnameStatus;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use App\Services\StockOpnameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Location $store;
    protected Location $warehouse;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
        $this->store = Location::where('code', 'STR-01')->first();
        $this->warehouse = Location::where('code', 'WHS-01')->first();

        // Ensure at least two products with known inventory in Store
        $products = Product::where('is_active', true)->take(2)->get();
        $this->productA = $products[0];
        $this->productB = $products[1];

        Inventory::updateOrCreate(
            ['product_id' => $this->productA->id, 'location_id' => $this->store->id],
            ['quantity' => 20, 'moving_average_cost' => 15000]
        );

        Inventory::updateOrCreate(
            ['product_id' => $this->productB->id, 'location_id' => $this->store->id],
            ['quantity' => 15, 'moving_average_cost' => 25000]
        );
    }

    public function test_cashier_cannot_access_or_create_stock_opname(): void
    {
        $response = $this->actingAs($this->cashier)->get('/opnames');
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->cashier)->get('/opnames/create');
        $createResponse->assertStatus(403);
    }

    public function test_manager_can_create_opname_session_and_freeze_balances(): void
    {
        $response = $this->actingAs($this->manager)->post('/opnames', [
            'location_id' => $this->store->id,
            'notes' => 'Opname bulanan toko utama',
        ]);

        $opname = StockOpname::latest('id')->first();
        $this->assertNotNull($opname);
        $response->assertRedirect(route('opnames.show', $opname));

        $this->assertEquals(OpnameStatus::IN_PROGRESS, $opname->status);
        $this->assertEquals($this->store->id, $opname->location_id);
        $this->assertEquals($this->manager->id, $opname->created_by);

        // Verify items snapshot created
        $itemA = $opname->items()->where('product_id', $this->productA->id)->first();
        $this->assertNotNull($itemA);
        $this->assertEquals(20, $itemA->system_qty);
        $this->assertNull($itemA->physical_qty);

        $itemB = $opname->items()->where('product_id', $this->productB->id)->first();
        $this->assertNotNull($itemB);
        $this->assertEquals(15, $itemB->system_qty);
        $this->assertNull($itemB->physical_qty);
    }

    public function test_updating_physical_counts_calculates_variances_correctly(): void
    {
        $service = app(StockOpnameService::class);
        $opname = $service->createOpname(
            location: $this->store,
            creator: $this->manager,
            notes: 'Test count'
        );

        $itemA = $opname->items()->where('product_id', $this->productA->id)->first();
        $itemB = $opname->items()->where('product_id', $this->productB->id)->first();

        // Product A: system 20 -> physical 25 (+5 surplus)
        // Product B: system 15 -> physical 12 (-3 deficit)
        $response = $this->actingAs($this->manager)->postJson("/opnames/{$opname->id}/counts", [
            'counts' => [
                [
                    'item_id' => $itemA->id,
                    'physical_qty' => 25,
                    'notes' => 'Ditemukan stok ekstra',
                ],
                [
                    'item_id' => $itemB->id,
                    'physical_qty' => 12,
                    'notes' => 'Hilang 3 bungkus',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $itemA->refresh();
        $this->assertEquals(25, $itemA->physical_qty);
        $this->assertEquals(5, $itemA->difference_qty);
        $this->assertEquals(5 * $itemA->unit_cost, $itemA->difference_amount);

        $itemB->refresh();
        $this->assertEquals(12, $itemB->physical_qty);
        $this->assertEquals(-3, $itemB->difference_qty);
        $this->assertEquals(-3 * $itemB->unit_cost, $itemB->difference_amount);

        $opname->refresh();
        // Total variance qty for the 2 items counted = 5 + (-3) = 2
        $this->assertEquals(2, $opname->total_variance_qty);
    }

    public function test_completing_opname_adjusts_inventory_and_creates_stock_movements(): void
    {
        $service = app(StockOpnameService::class);
        $opname = $service->createOpname(
            location: $this->store,
            creator: $this->manager,
            notes: 'Test complete'
        );

        $itemA = $opname->items()->where('product_id', $this->productA->id)->first();
        $itemB = $opname->items()->where('product_id', $this->productB->id)->first();

        // Submit counts
        $service->updatePhysicalCounts($opname, [
            ['item_id' => $itemA->id, 'physical_qty' => 24], // +4 surplus
            ['item_id' => $itemB->id, 'physical_qty' => 11], // -4 deficit
        ]);

        // Complete/Approve Opname
        $response = $this->actingAs($this->admin)->post("/opnames/{$opname->id}/complete");
        $response->assertRedirect(route('opnames.show', $opname));

        $opname->refresh();
        $this->assertEquals(OpnameStatus::COMPLETED, $opname->status);
        $this->assertEquals($this->admin->id, $opname->approved_by);
        $this->assertNotNull($opname->completed_at);

        // Verify inventory table updated to match physical counts
        $invA = Inventory::where('product_id', $this->productA->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');
        $this->assertEquals(24, $invA);

        $invB = Inventory::where('product_id', $this->productB->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');
        $this->assertEquals(11, $invB);

        // Verify immutable stock movements recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->productA->id,
            'location_id' => $this->store->id,
            'quantity' => 4,
            'movement_type' => MovementType::STOCK_OPNAME->value,
            'reference_type' => StockOpname::class,
            'reference_id' => $opname->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->productB->id,
            'location_id' => $this->store->id,
            'quantity' => -4,
            'movement_type' => MovementType::STOCK_OPNAME->value,
            'reference_type' => StockOpname::class,
            'reference_id' => $opname->id,
        ]);
    }

    public function test_cannot_modify_already_completed_opname(): void
    {
        $service = app(StockOpnameService::class);
        $opname = $service->createOpname(
            location: $this->store,
            creator: $this->manager
        );

        $itemA = $opname->items()->where('product_id', $this->productA->id)->first();
        $service->updatePhysicalCounts($opname, [
            ['item_id' => $itemA->id, 'physical_qty' => 20],
        ]);

        $service->completeOpname($opname, $this->admin);

        // Try updating counts on completed opname
        $response = $this->actingAs($this->manager)->postJson("/opnames/{$opname->id}/counts", [
            'counts' => [
                ['item_id' => $itemA->id, 'physical_qty' => 30],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cancelling_opname_sets_status_cancelled_without_adjusting_inventory(): void
    {
        $service = app(StockOpnameService::class);
        $opname = $service->createOpname(
            location: $this->store,
            creator: $this->manager
        );

        $itemA = $opname->items()->where('product_id', $this->productA->id)->first();
        $service->updatePhysicalCounts($opname, [
            ['item_id' => $itemA->id, 'physical_qty' => 50],
        ]);

        // Cancel
        $response = $this->actingAs($this->manager)->post("/opnames/{$opname->id}/cancel", [
            'reason' => 'Salah area penghitungan',
        ]);

        $opname->refresh();
        $this->assertEquals(OpnameStatus::CANCELLED, $opname->status);
        $this->assertStringContainsString('Salah area penghitungan', $opname->notes);

        // Inventory must remain at original 20
        $invA = Inventory::where('product_id', $this->productA->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');
        $this->assertEquals(20, $invA);
    }

    public function test_stock_opname_views_render_successfully(): void
    {
        $service = app(StockOpnameService::class);
        $opname = $service->createOpname(
            location: $this->store,
            creator: $this->manager
        );

        // Index view
        $indexResponse = $this->actingAs($this->manager)->get('/opnames');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($opname->opname_number);
        $indexResponse->assertSee($this->store->name);

        // Show view
        $showResponse = $this->actingAs($this->manager)->get("/opnames/{$opname->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($opname->opname_number);
        $showResponse->assertSee($this->productA->name);
        $showResponse->assertSee('Lembar Kerja Stock Opname');
    }
}
