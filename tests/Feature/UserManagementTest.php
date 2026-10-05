<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Location $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
        $this->store = Location::where('code', 'STR-01')->first();
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Pengguna Sistem');
        $response->assertSee('Super Administrator');
        $response->assertSee('cashier');
    }

    public function test_manager_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->manager)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_cashier_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_cashier_assigned_to_store(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Doni Kasir Baru',
            'username' => 'doni.kasir',
            'email' => 'doni@adastock.local',
            'password' => 'secret123',
            'role' => UserRole::CASHIER->value,
            'assigned_store_id' => $this->store->id,
            'phone' => '081299998888',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username' => 'doni.kasir',
            'email' => 'doni@adastock.local',
            'role' => UserRole::CASHIER->value,
            'assigned_store_id' => $this->store->id,
            'status' => 'active',
        ]);

        $user = User::where('username', 'doni.kasir')->first();
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_admin_can_create_new_manager_with_supervisor_pin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Agus Manager Baru',
            'username' => 'agus.manager',
            'email' => 'agus@adastock.local',
            'password' => 'secret123',
            'role' => UserRole::MANAGER->value,
            'assigned_store_id' => $this->store->id,
            'supervisor_pin' => '789012',
            'phone' => '081277776666',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user = User::where('username', 'agus.manager')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('789012', $user->supervisor_pin));
    }

    public function test_admin_can_update_user_and_reassign_store(): void
    {
        $newStore = Location::create([
            'code' => 'STR-02',
            'name' => 'Toko Cabang Kedua',
            'type' => \App\Enums\LocationType::STORE,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->cashier), [
            'name' => 'Siti Rahma Senior',
            'username' => 'cashier',
            'email' => 'cashier@adastock.local',
            'role' => UserRole::CASHIER->value,
            'assigned_store_id' => $newStore->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->cashier->refresh();
        $this->assertSame('Siti Rahma Senior', $this->cashier->name);
        $this->assertSame($newStore->id, $this->cashier->assigned_store_id);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_delete_user_without_sales(): void
    {
        $testUser = User::create([
            'name' => 'Temporary User',
            'username' => 'temp.user',
            'email' => 'temp@adastock.local',
            'password' => Hash::make('password'),
            'role' => UserRole::CASHIER,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $testUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $testUser->id,
        ]);
    }
}
