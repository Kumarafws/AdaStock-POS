<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\User;
use App\Services\PosService;
use App\Services\ReportService;
use App\Services\SalesReturnService;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Location $store;
    protected Product $product;
    protected CashierShift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
        $this->store = Location::where('code', 'STR-01')->first();

        $shiftService = app(ShiftService::class);
        $this->shift = $shiftService->openShift(
            cashier: $this->cashier,
            store: $this->store,
            startingCash: 300000
        );

        $this->product = Product::with('units')->first();
    }

    /**
     * Helper to perform a completed POS sale.
     */
    protected function performSale(int $quantity = 2, float $discount = 0): Sale
    {
        $unitPrice = (float) $this->product->default_selling_price;
        $total = ($quantity * $unitPrice) - $discount;

        $response = $this->actingAs($this->cashier)->postJson('/pos/checkout', [
            'cart_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $quantity,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => $total,
                ],
            ],
            'discount_amount' => $discount,
            'supervisor_pin' => $discount > 0 ? '123456' : null,
        ]);

        $response->assertStatus(200);

        return Sale::where('sale_number', $response->json('sale_number'))->firstOrFail();
    }

    public function test_cashier_cannot_access_financial_reports(): void
    {
        $endpoints = [
            '/reports/sales',
            '/reports/profit',
            '/reports/shifts',
            '/reports/inventory-valuation',
        ];

        foreach ($endpoints as $url) {
            $response = $this->actingAs($this->cashier)->get($url);
            $response->assertStatus(403);
        }
    }

    public function test_admin_and_manager_can_access_all_report_views(): void
    {
        $endpoints = [
            '/reports/sales',
            '/reports/profit',
            '/reports/shifts',
            '/reports/inventory-valuation',
        ];

        foreach ($endpoints as $url) {
            $response = $this->actingAs($this->manager)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_sales_report_calculation_accuracy(): void
    {
        $unitPrice = (float) $this->product->default_selling_price;
        $qty = 2;
        $discount = 5000;
        $sale = $this->performSale(quantity: $qty, discount: $discount);

        $reportService = app(ReportService::class);
        $data = $reportService->getSalesReport(
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        $expectedGross = $qty * $unitPrice;
        $expectedNet = $expectedGross - $discount;

        $this->assertEquals($expectedGross, $data['gross_sales']);
        $this->assertEquals($discount, $data['total_discounts']);
        $this->assertEquals($expectedNet, $data['net_sales']);
        $this->assertEquals(1, $data['total_transactions']);
        $this->assertEquals(0, $data['voided_count']);
    }

    public function test_sales_report_excludes_voided_sales_from_net_sales(): void
    {
        $sale1 = $this->performSale(quantity: 2);
        $sale2 = $this->performSale(quantity: 3);

        // Void sale2
        $posService = app(PosService::class);
        $posService->voidSale($sale2, $this->cashier, $this->admin, 'Salah transaksi');

        $reportService = app(ReportService::class);
        $data = $reportService->getSalesReport(
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        // Net sales should only reflect sale1
        $this->assertEquals($sale1->total_amount, $data['net_sales']);
        $this->assertEquals(1, $data['total_transactions']);
        $this->assertEquals(1, $data['voided_count']);
        $this->assertEquals($sale2->total_amount, $data['voided_amount']);
    }

    public function test_sales_report_factors_in_sales_returns(): void
    {
        $sale = $this->performSale(quantity: 4);
        $saleItem = $sale->items->first();

        // Process a return of 1 item
        $returnService = app(SalesReturnService::class);
        $salesReturn = $returnService->createReturn(
            sale: $sale,
            shift: $this->shift,
            cashier: $this->cashier,
            returnItems: [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'condition' => 'good',
                ],
            ],
            refundMethod: 'cash',
            reason: 'Kemasan rusak'
        );

        $reportService = app(ReportService::class);
        $data = $reportService->getSalesReport(
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        $this->assertEquals($salesReturn->total_refund_amount, $data['total_refunds']);
        $this->assertEquals($sale->total_amount - $salesReturn->total_refund_amount, $data['net_sales']);
    }

    public function test_sales_report_csv_export_streams_valid_content(): void
    {
        $this->performSale(quantity: 2);

        $response = $this->actingAs($this->manager)->get('/reports/sales/export-csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=laporan-penjualan-', $response->headers->get('Content-Disposition'));
    }

    public function test_gross_profit_report_calculates_margins_and_top_sellers(): void
    {
        $sale = $this->performSale(quantity: 3);

        $reportService = app(ReportService::class);
        $data = $reportService->getGrossProfitReport(
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        $this->assertGreaterThan(0, $data['total_revenue']);
        $this->assertGreaterThan(0, $data['total_cogs']);
        $this->assertEquals($data['total_revenue'] - $data['total_cogs'], $data['total_profit']);
        $this->assertNotEmpty($data['top_sellers']);
        $this->assertEquals($this->product->id, $data['top_sellers'][0]['product_id']);
    }

    public function test_shift_reconciliation_report_shows_drawer_cash_and_variance(): void
    {
        // Add a sale so shift has cash
        $this->performSale(quantity: 2);

        // Close shift with a discrepancy: expected is starting + cash, we close with Rp 10.000 short
        $shiftService = app(ShiftService::class);
        $freshShift = $this->shift->fresh();
        $expected = $freshShift->starting_cash + $freshShift->total_sales_cash;
        $actual = $expected - 10000;

        $shiftService->closeShift(
            shift: $freshShift,
            actualCash: $actual,
            notes: 'Uang kembalian tercecer',
            closer: $this->admin
        );

        $response = $this->actingAs($this->manager)->get('/reports/shifts');
        $response->assertStatus(200);
        $response->assertSee($this->shift->shift_number);
        $response->assertSee('-Rp 10.000');
    }

    public function test_inventory_valuation_report_calculates_total_asset_value(): void
    {
        $response = $this->actingAs($this->manager)->get('/reports/inventory-valuation');

        $response->assertStatus(200);
        $response->assertSee($this->store->name);
        $response->assertSee($this->product->name);
    }
}
