<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna');
    }

    public function test_manager_cannot_access_user_management(): void
    {
        $manager = User::where('username', 'manager')->first();

        $response = $this->actingAs($manager)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_cashier_cannot_access_user_management(): void
    {
        $cashier = User::where('username', 'cashier')->first();

        $response = $this->actingAs($cashier)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_and_manager_can_access_locations(): void
    {
        $admin = User::where('username', 'admin')->first();
        $manager = User::where('username', 'manager')->first();

        $this->actingAs($admin)->get('/locations')->assertStatus(200);
        $this->actingAs($manager)->get('/locations')->assertStatus(200);
    }

    public function test_cashier_cannot_access_locations(): void
    {
        $cashier = User::where('username', 'cashier')->first();

        $response = $this->actingAs($cashier)->get('/locations');

        $response->assertStatus(403);
    }

    public function test_all_authenticated_roles_can_access_dashboard(): void
    {
        $admin = User::where('username', 'admin')->first();
        $manager = User::where('username', 'manager')->first();
        $cashier = User::where('username', 'cashier')->first();

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($manager)->get('/dashboard')->assertStatus(200);
        $this->actingAs($cashier)->get('/dashboard')->assertStatus(200);
    }
}
