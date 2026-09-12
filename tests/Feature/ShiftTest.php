<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Enums\UserRole;
use App\Models\CashierShift;
use App\Models\Location;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShiftTest extends TestCase
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

    public function test_cashier_without_active_shift_is_redirected_to_open_shift_when_accessing_pos(): void
    {
        $response = $this->actingAs($this->cashier)->get('/pos');

        $response->assertRedirect(route('shifts.create'));
        $response->assertSessionHas('warning');
    }

    public function test_cashier_can_view_open_shift_form_and_open_register(): void
    {
        $this->actingAs($this->cashier)
            ->get(route('shifts.create'))
            ->assertStatus(200)
            ->assertSee('Buka Register Kasir Baru');

        $response = $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'starting_cash' => 200000,
            'notes' => 'Kas modal pagi',
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $this->cashier->id,
            'location_id' => $this->store->id,
            'starting_cash' => 200000,
            'status' => ShiftStatus::OPEN->value,
            'closed_at' => null,
        ]);
    }

    public function test_cashier_with_active_shift_can_access_pos_screen(): void
    {
        // Open shift first
        $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'starting_cash' => 150000,
        ]);

        // Access POS
        $response = $this->actingAs($this->cashier)->get('/pos');
        $response->assertStatus(200);
    }

    public function test_cashier_cannot_open_multiple_concurrent_shifts(): void
    {
        // Open first shift
        $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'starting_cash' => 100000,
        ]);

        $firstShift = CashierShift::where('user_id', $this->cashier->id)->first();

        // Visiting create should redirect to shift detail
        $this->actingAs($this->cashier)
            ->get(route('shifts.create'))
            ->assertRedirect(route('shifts.show', $firstShift));

        // Attempting to post another shift should fail
        $response = $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'starting_cash' => 200000,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, CashierShift::where('user_id', $this->cashier->id)->count());
    }

    public function test_cashier_can_close_shift_with_blind_count_and_reconciliation(): void
    {
        // Open shift with 100k
        $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'starting_cash' => 100000,
        ]);

        $shift = CashierShift::where('user_id', $this->cashier->id)->first();

        // Visit close form
        $this->actingAs($this->cashier)
            ->get(route('shifts.close.form', $shift))
            ->assertStatus(200)
            ->assertSee('Tutup Register');

        // Close shift with actual cash 95,000 (selisih kurang 5,000)
        $response = $this->actingAs($this->cashier)->post(route('shifts.close', $shift), [
            'actual_ending_cash' => 95000,
            'notes' => 'Kurang 5rb karena pecahan kecil',
        ]);

        $response->assertRedirect(route('shifts.show', $shift));
        $response->assertSessionHas('success');

        $shift->refresh();
        $this->assertEquals(ShiftStatus::CLOSED, $shift->status);
        $this->assertEquals(100000, $shift->expected_ending_cash);
        $this->assertEquals(95000, $shift->actual_ending_cash);
        $this->assertEquals(-5000, $shift->cash_difference);
        $this->assertNotNull($shift->closed_at);
        $this->assertEquals($this->cashier->id, $shift->closed_by);
    }

    public function test_cashier_cannot_view_or_close_another_cashiers_shift(): void
    {
        // Create second cashier
        $cashier2 = User::create([
            'name' => 'Cashier Dua',
            'username' => 'cashier2',
            'email' => 'cashier2@adastock.local',
            'password' => Hash::make('password'),
            'role' => UserRole::CASHIER,
            'assigned_store_id' => $this->store->id,
            'status' => 'active',
        ]);

        // Open shift for cashier2
        $shiftService = app(ShiftService::class);
        $shift2 = $shiftService->openShift($cashier2, $this->store, 150000);

        // Cashier 1 attempts to view cashier 2's shift -> 403
        $this->actingAs($this->cashier)
            ->get(route('shifts.show', $shift2))
            ->assertStatus(403);

        // Cashier 1 attempts to access close form -> 403
        $this->actingAs($this->cashier)
            ->get(route('shifts.close.form', $shift2))
            ->assertStatus(403);

        // Cashier 1 attempts to close cashier 2's shift -> 403
        $this->actingAs($this->cashier)
            ->post(route('shifts.close', $shift2), [
                'actual_ending_cash' => 150000,
            ])
            ->assertStatus(403);
    }

    public function test_admin_and_manager_can_view_any_shift_and_summary(): void
    {
        // Open shift for cashier
        $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'starting_cash' => 100000,
        ]);

        $shift = CashierShift::where('user_id', $this->cashier->id)->first();

        // Admin can view shift list and detail
        $this->actingAs($this->admin)->get(route('shifts.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('shifts.show', $shift))->assertStatus(200);

        // Manager can view shift list and detail
        $this->actingAs($this->manager)->get(route('shifts.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('shifts.show', $shift))->assertStatus(200);
    }

    public function test_shift_service_reconciles_multi_payment_correctly(): void
    {
        $shiftService = app(ShiftService::class);
        $shift = $shiftService->openShift($this->cashier, $this->store, 200000);

        // Simulate sales recording
        $shift->update([
            'total_sales_amount' => 500000,
            'total_sales_cash' => 350000,
            'total_sales_non_cash' => 150000,
            'total_transactions_count' => 5,
        ]);

        // Expected cash in drawer = starting_cash (200,000) + total_sales_cash (350,000) = 550,000
        // Physical count = 550,000 -> variance 0
        $closedShift = $shiftService->closeShift($shift, 550000, 'Tutup shift klop');

        $this->assertEquals(ShiftStatus::CLOSED, $closedShift->status);
        $this->assertEquals(550000, $closedShift->expected_ending_cash);
        $this->assertEquals(550000, $closedShift->actual_ending_cash);
        $this->assertEquals(0, $closedShift->cash_difference);
    }
}
