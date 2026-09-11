<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Sistem');
        $response->assertSee('Username / Email');
    }

    public function test_user_can_authenticate_using_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@adastock.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_authenticate_using_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'cashier',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@adastock.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::create([
            'name' => 'Inactive User',
            'username' => 'inactive',
            'email' => 'inactive@adastock.local',
            'password' => Hash::make('password'),
            'role' => UserRole::CASHIER,
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'login' => 'inactive',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
