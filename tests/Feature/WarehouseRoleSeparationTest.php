<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseRoleSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $warehouse;
    protected User $cashier;
    protected Location $warehouseLoc;
    protected Location $storeLoc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->warehouse = User::where('username', 'warehouse')->first();
        $this->cashier = User::where('username', 'cashier')->first();
        $this->warehouseLoc = Location::where('code', 'WHS-01')->first();
        $this->storeLoc = Location::where('code', 'STR-01')->first();
    }

    public function test_warehouse_staff_can_access_goods_receipt_pages(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('purchasing.receipts.index'));
        $response->assertStatus(200);
        $response->assertSee('Penerimaan Barang');
    }

    public function test_store_manager_cannot_access_goods_receipt_pages(): void
    {
        $response = $this->actingAs($this->manager)->get(route('purchasing.receipts.index'));
        $response->assertStatus(403);
    }

    public function test_warehouse_staff_can_access_stock_adjustments(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('adjustments.index'));
        $response->assertStatus(200);
    }

    public function test_store_manager_cannot_access_stock_adjustments(): void
    {
        $response = $this->actingAs($this->manager)->get(route('adjustments.index'));
        $response->assertStatus(403);
    }

    public function test_warehouse_staff_can_access_stock_ledger(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('inventory.ledger'));
        $response->assertStatus(200);
    }

    public function test_store_manager_cannot_access_stock_ledger(): void
    {
        $response = $this->actingAs($this->manager)->get(route('inventory.ledger'));
        $response->assertStatus(403);
    }

    public function test_warehouse_staff_can_access_purchase_returns(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('purchasing.returns.index'));
        $response->assertStatus(200);
    }

    public function test_store_manager_cannot_access_purchase_returns(): void
    {
        $response = $this->actingAs($this->manager)->get(route('purchasing.returns.index'));
        $response->assertStatus(403);
    }

    public function test_warehouse_staff_can_view_purchase_orders_list(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('purchasing.orders.index'));
        $response->assertStatus(200);
    }

    public function test_warehouse_staff_cannot_create_purchase_order(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('purchasing.orders.create'));
        $response->assertStatus(403);
    }

    public function test_store_manager_can_create_purchase_order(): void
    {
        $response = $this->actingAs($this->manager)->get(route('purchasing.orders.create'));
        $response->assertStatus(200);
    }

    public function test_warehouse_staff_cannot_access_pos_screen(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('pos.index'));
        $response->assertStatus(403);
    }

    public function test_warehouse_staff_cannot_access_financial_profit_report(): void
    {
        $response = $this->actingAs($this->warehouse)->get(route('reports.profit'));
        $response->assertStatus(403);
    }

    public function test_admin_retains_full_access_to_all_operations(): void
    {
        $this->actingAs($this->admin)->get(route('purchasing.receipts.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('purchasing.orders.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('adjustments.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('inventory.ledger'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('reports.profit'))->assertStatus(200);
    }
}
