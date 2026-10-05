<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
    }

    public function test_admin_and_manager_can_view_locations_page(): void
    {
        // Admin
        $responseAdmin = $this->actingAs($this->admin)->get('/locations');
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Manajemen Toko');
        $responseAdmin->assertSee('Tambah Toko / Gudang');

        // Manager can view but does not see the Add Location button
        $responseManager = $this->actingAs($this->manager)->get('/locations');
        $responseManager->assertOk();
        $responseManager->assertSee('Manajemen Toko');
        $responseManager->assertDontSee('Tambah Toko / Gudang');
    }

    public function test_cashier_cannot_access_locations_page(): void
    {
        $response = $this->actingAs($this->cashier)->get('/locations');
        $response->assertForbidden();
    }

    public function test_admin_can_create_new_store(): void
    {
        $payload = [
            'code' => 'STR-SBY',
            'name' => 'Toko Cabang Surabaya',
            'type' => LocationType::STORE->value,
            'address' => 'Jl. Pemuda No. 12, Surabaya',
            'phone' => '031-5550192',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertRedirect('/locations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'code' => 'STR-SBY',
            'name' => 'Toko Cabang Surabaya',
            'type' => 'store',
            'phone' => '031-5550192',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_new_warehouse(): void
    {
        $payload = [
            'code' => 'whs-west', // Test auto-uppercase
            'name' => 'Gudang Hub Barat',
            'type' => LocationType::WAREHOUSE->value,
            'address' => 'Kawasan Industri Cikande Blok A2',
            'phone' => '0254-889123',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertRedirect('/locations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'code' => 'WHS-WEST',
            'name' => 'Gudang Hub Barat',
            'type' => 'warehouse',
        ]);
    }

    public function test_location_code_must_be_unique(): void
    {
        $existing = Location::first();

        $payload = [
            'code' => $existing->code,
            'name' => 'Toko Duplikat',
            'type' => LocationType::STORE->value,
        ];

        $response = $this->actingAs($this->admin)->post('/locations', $payload);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_manager_cannot_create_location(): void
    {
        $payload = [
            'code' => 'STR-BDG',
            'name' => 'Toko Cabang Bandung',
            'type' => LocationType::STORE->value,
        ];

        $response = $this->actingAs($this->manager)->post('/locations', $payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('locations', ['code' => 'STR-BDG']);
    }

    public function test_admin_can_update_location(): void
    {
        $location = Location::where('type', LocationType::STORE)->first();

        $response = $this->actingAs($this->admin)->put("/locations/{$location->id}", [
            'code' => $location->code,
            'name' => 'Toko Flagship Sudirman Baru',
            'type' => $location->type->value,
            'address' => 'Jl. Sudirman No. 99, Jakarta Pusat',
            'phone' => '021-9998887',
            'is_active' => true,
        ]);

        $response->assertRedirect('/locations');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Toko Flagship Sudirman Baru',
            'phone' => '021-9998887',
        ]);
    }

    public function test_location_with_existing_inventory_cannot_be_deleted(): void
    {
        $store = Location::where('type', LocationType::STORE)->first();
        $this->assertTrue($store->inventories()->exists());

        $response = $this->actingAs($this->admin)->delete("/locations/{$store->id}");

        $response->assertRedirect('/locations');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('locations', ['id' => $store->id]);
    }

    public function test_empty_location_can_be_deleted_by_admin(): void
    {
        $newLocation = Location::create([
            'code' => 'TEMP-01',
            'name' => 'Gudang Sementara',
            'type' => LocationType::WAREHOUSE,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete("/locations/{$newLocation->id}");

        $response->assertRedirect('/locations');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('locations', ['id' => $newLocation->id]);
    }
}
