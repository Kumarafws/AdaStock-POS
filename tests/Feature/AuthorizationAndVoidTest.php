<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\ShiftStatus;
use App\Models\CashierShift;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleVoidLog;
use App\Models\User;
use App\Services\PosService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationAndVoidTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Location $store;
    protected CashierShift $activeShift;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
        $this->store = Location::where('code', 'STR-01')->first();

        // Active shift
        $shiftService = app(ShiftService::class);
        $this->activeShift = $shiftService->openShift(
            cashier: $this->cashier,
            store: $this->store,
            startingCash: 200000
        );

        $this->product = Product::with('units')->first();
    }

    public function test_supervisor_pin_verification_succeeds_for_manager_or_admin(): void
    {
        // Manager PIN is '654321' from DatabaseSeeder
        $response = $this->actingAs($this->cashier)->postJson('/pos/verify-pin', [
            'pin' => '654321',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'supervisor' => [
                'id' => $this->manager->id,
                'name' => $this->manager->name,
            ],
        ]);

        // Admin PIN is '123456' from DatabaseSeeder
        $responseAdmin = $this->actingAs($this->cashier)->postJson('/pos/verify-pin', [
            'pin' => '123456',
        ]);

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertJson([
            'success' => true,
            'supervisor' => [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
            ],
        ]);
    }

    public function test_supervisor_pin_verification_fails_for_invalid_pin(): void
    {
        $response = $this->actingAs($this->cashier)->postJson('/pos/verify-pin', [
            'pin' => '999999',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_checkout_with_high_discount_fails_without_supervisor_pin(): void
    {
        $qty = 10;
        $unitPrice = (float) $this->product->default_selling_price;
        $subtotal = $qty * $unitPrice;
        $highDiscount = 25000; // > 20,000 threshold

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $qty,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $subtotal - $highDiscount,
                ],
            ],
            'discount_amount' => $highDiscount,
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Otorisasi PIN Supervisor diperlukan', $response->json('message'));
        $this->assertEquals(0, Sale::count());
    }

    public function test_checkout_with_high_discount_succeeds_with_valid_supervisor_pin(): void
    {
        $qty = 10;
        $unitPrice = (float) $this->product->default_selling_price;
        $subtotal = $qty * $unitPrice;
        $highDiscount = 25000;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $qty,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $subtotal - $highDiscount,
                ],
            ],
            'discount_amount' => $highDiscount,
            'supervisor_pin' => '654321', // Manager PIN
        ]);

        $response->assertStatus(200);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals($highDiscount, $sale->discount_amount);
        $this->assertEquals($this->manager->id, $sale->discount_authorized_by);
    }

    public function test_cashier_can_void_completed_sale_with_supervisor_pin(): void
    {
        // 1. Create a sale
        $initialStock = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');

        $posService = app(PosService::class);
        $sale = $posService->checkout(
            shift: $this->activeShift,
            cashier: $this->cashier,
            cartItems: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
            payments: [
                [
                    'payment_method' => 'cash',
                    'amount' => $this->product->default_selling_price * 2,
                ],
            ],
            customerName: 'Pelanggan Test Void'
        );

        $this->assertEquals($initialStock - 2, Inventory::where('product_id', $this->product->id)->where('location_id', $this->store->id)->value('quantity'));
        $this->assertEquals($sale->total_amount, $this->activeShift->fresh()->total_sales_amount);

        // 2. Void the sale
        $response = $this->actingAs($this->cashier)->postJson('/pos/sales/' . $sale->id . '/void', [
            'pin' => '654321', // Manager PIN
            'reason' => 'Pelanggan batal beli',
            'notes' => 'Uang tunai telah dikembalikan ke pelanggan',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert sale status updated
        $sale->refresh();
        $this->assertEquals(SaleStatus::VOIDED, $sale->status);

        // Assert stock restored
        $restoredStock = Inventory::where('product_id', $this->product->id)
            ->where('location_id', $this->store->id)
            ->value('quantity');
        $this->assertEquals($initialStock, $restoredStock);

        // Assert shift totals decremented
        $this->activeShift->refresh();
        $this->assertEquals(0, $this->activeShift->total_sales_amount);
        $this->assertEquals(0, $this->activeShift->total_sales_cash);
        $this->assertEquals(0, $this->activeShift->total_transactions_count);

        // Assert void audit log created
        $this->assertDatabaseHas('sale_void_logs', [
            'sale_id' => $sale->id,
            'cashier_id' => $this->cashier->id,
            'supervisor_id' => $this->manager->id,
            'reason' => 'Pelanggan batal beli',
        ]);
    }

    public function test_cannot_void_already_voided_sale(): void
    {
        $posService = app(PosService::class);
        $sale = $posService->checkout(
            shift: $this->activeShift,
            cashier: $this->cashier,
            cartItems: [['product_id' => $this->product->id, 'quantity' => 1]],
            payments: [['payment_method' => 'cash', 'amount' => $this->product->default_selling_price]]
        );

        // Void first time
        $this->actingAs($this->cashier)->postJson('/pos/sales/' . $sale->id . '/void', [
            'pin' => '654321',
            'reason' => 'Salah input',
        ])->assertStatus(200);

        // Attempt void second time
        $response = $this->actingAs($this->cashier)->postJson('/pos/sales/' . $sale->id . '/void', [
            'pin' => '654321',
            'reason' => 'Mencoba void ulang',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('sudah dibatalkan sebelumnya', $response->json('message'));
    }

    public function test_cannot_void_sale_if_cashier_shift_is_closed(): void
    {
        $posService = app(PosService::class);
        $sale = $posService->checkout(
            shift: $this->activeShift,
            cashier: $this->cashier,
            cartItems: [['product_id' => $this->product->id, 'quantity' => 1]],
            payments: [['payment_method' => 'cash', 'amount' => $this->product->default_selling_price]]
        );

        // Close shift
        $shiftService = app(ShiftService::class);
        $shiftService->closeShift($this->activeShift, 250000, 'Tutup shift');

        // Open a new shift for cashier
        $shiftService->openShift($this->cashier, $this->store, 200000, 'Shift baru');

        // Attempt void sale from previous closed shift
        $response = $this->actingAs($this->cashier)->postJson('/pos/sales/' . $sale->id . '/void', [
            'pin' => '654321',
            'reason' => 'Pelanggan komplain setelah shift tutup',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('shift kasir sudah ditutup', $response->json('message'));
    }

    public function test_admin_and_manager_can_view_void_logs_cashier_is_forbidden(): void
    {
        $this->actingAs($this->admin)->get('/pos/void-logs')->assertStatus(200);
        $this->actingAs($this->manager)->get('/pos/void-logs')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/pos/void-logs')->assertStatus(403);
    }
}
