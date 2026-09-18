<?php

namespace App\Services;

use App\Enums\LocationType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Brand;
use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SalesReturn;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Generate Comprehensive Sales Performance Report.
     */
    public function getSalesReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null,
        ?int $cashierId = null
    ): array {
        // Base sales query
        $salesQuery = Sale::with(['cashier', 'location', 'payments', 'items.product', 'returns'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if ($locationId) {
            $salesQuery->where('location_id', $locationId);
        }

        if ($cashierId) {
            $salesQuery->where('user_id', $cashierId);
        }

        $allSales = $salesQuery->latest('transaction_date')->get();

        // Valid sales (exclude voided)
        $validSales = $allSales->where('status', '!=', SaleStatus::VOIDED);
        $voidedSales = $allSales->where('status', SaleStatus::VOIDED);

        // Returns query in date range
        $returnsQuery = SalesReturn::with('sale')
            ->whereDate('return_date', '>=', $startDate)
            ->whereDate('return_date', '<=', $endDate);

        if ($locationId) {
            $returnsQuery->whereHas('sale', fn($q) => $q->where('location_id', $locationId));
        }

        if ($cashierId) {
            $returnsQuery->where('user_id', $cashierId);
        }

        $returns = $returnsQuery->get();

        // Financial KPIs
        $grossSales = (float) $validSales->sum('subtotal');
        $totalDiscounts = (float) $validSales->sum('discount_amount');
        $totalRefunds = (float) $returns->sum('total_refund_amount');
        $netSales = max(0.0, (float) $validSales->sum('total_amount') - $totalRefunds);
        $totalCogs = (float) $validSales->sum('total_cogs');
        $grossProfit = $netSales - $totalCogs;
        $totalTransactions = $validSales->count();
        $averageOrderValue = $totalTransactions > 0 ? round($netSales / $totalTransactions, 2) : 0.0;

        $voidedCount = $voidedSales->count();
        $voidedAmount = (float) $voidedSales->sum('total_amount');

        // Payment Methods Breakdown
        $paymentsQuery = SalePayment::whereHas('sale', function ($q) use ($startDate, $endDate, $locationId, $cashierId) {
            $q->where('status', '!=', SaleStatus::VOIDED)
                ->whereDate('transaction_date', '>=', $startDate)
                ->whereDate('transaction_date', '<=', $endDate);
            if ($locationId) $q->where('location_id', $locationId);
            if ($cashierId) $q->where('user_id', $cashierId);
        });

        $allPayments = $paymentsQuery->get();
        $paymentMethodsBreakdown = [];

        foreach (PaymentMethod::cases() as $method) {
            $matching = $allPayments->where('payment_method', $method);
            $totalAmount = (float) $matching->sum('amount');
            $paymentMethodsBreakdown[] = [
                'method' => $method->value,
                'label' => $method->label(),
                'badgeClass' => $method->badgeClass(),
                'count' => $matching->count(),
                'total_amount' => $totalAmount,
                'percentage' => $netSales > 0 ? round(($totalAmount / $netSales) * 100, 1) : 0,
            ];
        }

        // Daily Trend Breakdown
        $dailyTrend = $validSales->groupBy(fn($s) => $s->transaction_date->format('Y-m-d'))
            ->map(function ($daySales, $date) {
                return [
                    'date' => Carbon::parse($date)->format('d/m/Y'),
                    'transactions_count' => $daySales->count(),
                    'gross_sales' => (float) $daySales->sum('subtotal'),
                    'discounts' => (float) $daySales->sum('discount_amount'),
                    'net_sales' => (float) $daySales->sum('total_amount'),
                    'gross_profit' => (float) $daySales->sum('total_profit'),
                ];
            })->values();

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'gross_sales' => $grossSales,
            'total_discounts' => $totalDiscounts,
            'total_refunds' => $totalRefunds,
            'net_sales' => $netSales,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'total_transactions' => $totalTransactions,
            'average_order_value' => $averageOrderValue,
            'voided_count' => $voidedCount,
            'voided_amount' => $voidedAmount,
            'payment_methods' => $paymentMethodsBreakdown,
            'daily_trend' => $dailyTrend,
            'sales' => $allSales,
        ];
    }

    /**
     * Stream CSV Export of Sales Report.
     */
    public function exportSalesCsv(
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null,
        ?int $cashierId = null
    ): StreamedResponse {
        $reportData = $this->getSalesReport($startDate, $endDate, $locationId, $cashierId);
        $sales = $reportData['sales'];

        $filename = 'laporan-penjualan-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'No. Faktur',
                'Tanggal & Waktu',
                'Lokasi',
                'Kasir',
                'Pelanggan',
                'Subtotal (Rp)',
                'Diskon (Rp)',
                'Total Akhir (Rp)',
                'Metode Pembayaran',
                'Status',
            ]);

            foreach ($sales as $sale) {
                $paymentMethods = $sale->payments->map(fn($p) => $p->payment_method->label())->implode(', ');

                fputcsv($handle, [
                    $sale->sale_number,
                    $sale->transaction_date->format('d/m/Y H:i'),
                    $sale->location->name ?? '-',
                    $sale->cashier->name ?? '-',
                    $sale->customer_name ?: 'Pelanggan Umum',
                    $sale->subtotal,
                    $sale->discount_amount,
                    $sale->total_amount,
                    $paymentMethods,
                    $sale->status->label(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Generate Gross Profit & Margin Analysis Report.
     */
    public function getGrossProfitReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $categoryId = null,
        ?int $brandId = null
    ): array {
        $itemsQuery = SaleItem::with(['product.category', 'product.brand', 'sale'])
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', '!=', SaleStatus::VOIDED)
                    ->whereDate('transaction_date', '>=', $startDate)
                    ->whereDate('transaction_date', '<=', $endDate);
            });

        if ($categoryId) {
            $itemsQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }

        if ($brandId) {
            $itemsQuery->whereHas('product', fn($q) => $q->where('brand_id', $brandId));
        }

        $items = $itemsQuery->get();

        // Overall Totals
        $totalRevenue = (float) $items->sum('subtotal');
        $totalCogs = (float) $items->sum('cogs_total');
        $totalProfit = (float) $items->sum('gross_profit');
        $overallMarginPercent = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0.0;

        // Group by Product
        $productStats = $items->groupBy('product_id')->map(function ($prodItems) {
            $product = $prodItems->first()->product;
            $revenue = (float) $prodItems->sum('subtotal');
            $cogs = (float) $prodItems->sum('cogs_total');
            $profit = (float) $prodItems->sum('gross_profit');
            $qty = (int) $prodItems->sum('quantity');

            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category_name' => $product->category?->name ?? 'Uncategorized',
                'brand_name' => $product->brand?->name ?? 'Unbranded',
                'quantity_sold' => $qty,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit,
                'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0,
            ];
        });

        // Top 10 Best Sellers by Volume
        $topSellers = $productStats->sortByDesc('quantity_sold')->take(10)->values();

        // Top 10 Most Profitable by Nominal Profit
        $topProfitable = $productStats->sortByDesc('profit')->take(10)->values();

        // Category Margin Contribution
        $categoryBreakdown = $items->groupBy(fn($i) => $i->product->category?->name ?? 'Lainnya')
            ->map(function ($catItems, $catName) {
                $rev = (float) $catItems->sum('subtotal');
                $cogs = (float) $catItems->sum('cogs_total');
                $profit = (float) $catItems->sum('gross_profit');
                return [
                    'name' => $catName,
                    'quantity_sold' => (int) $catItems->sum('quantity'),
                    'revenue' => $rev,
                    'cogs' => $cogs,
                    'profit' => $profit,
                    'margin_percent' => $rev > 0 ? round(($profit / $rev) * 100, 1) : 0.0,
                ];
            })->sortByDesc('profit')->values();

        // Brand Margin Contribution
        $brandBreakdown = $items->groupBy(fn($i) => $i->product->brand?->name ?? 'Unbranded')
            ->map(function ($brandItems, $brandName) {
                $rev = (float) $brandItems->sum('subtotal');
                $cogs = (float) $brandItems->sum('cogs_total');
                $profit = (float) $brandItems->sum('gross_profit');
                return [
                    'name' => $brandName,
                    'quantity_sold' => (int) $brandItems->sum('quantity'),
                    'revenue' => $rev,
                    'cogs' => $cogs,
                    'profit' => $profit,
                    'margin_percent' => $rev > 0 ? round(($profit / $rev) * 100, 1) : 0.0,
                ];
            })->sortByDesc('profit')->values();

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalProfit,
            'overall_margin_percent' => $overallMarginPercent,
            'top_sellers' => $topSellers,
            'top_profitable' => $topProfitable,
            'category_breakdown' => $categoryBreakdown,
            'brand_breakdown' => $brandBreakdown,
            'all_products' => $productStats->sortByDesc('revenue')->values(),
        ];
    }

    /**
     * Generate Cash Register Shift Reconciliation Report.
     */
    public function getShiftReconciliationReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $locationId = null
    ): array {
        $query = CashierShift::with(['cashier', 'location', 'closer'])
            ->whereDate('opened_at', '>=', $startDate)
            ->whereDate('opened_at', '<=', $endDate);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $shifts = $query->latest('opened_at')->get();

        $totalStartingCash = (float) $shifts->sum('starting_cash');
        $totalSalesCash = (float) $shifts->sum('total_sales_cash');
        $totalSalesNonCash = (float) $shifts->sum('total_sales_non_cash');
        $totalSalesAmount = (float) $shifts->sum('total_sales_amount');

        // Differences calculation (for closed shifts)
        $closedShifts = $shifts->where('status', 'closed');
        $totalOverCash = (float) $closedShifts->where('cash_difference', '>', 0)->sum('cash_difference');
        $totalShortCash = (float) $closedShifts->where('cash_difference', '<', 0)->sum('cash_difference');
        $netCashDifference = (float) $closedShifts->sum('cash_difference');

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_shifts_count' => $shifts->count(),
            'closed_shifts_count' => $closedShifts->count(),
            'open_shifts_count' => $shifts->where('status', 'open')->count(),
            'total_starting_cash' => $totalStartingCash,
            'total_sales_cash' => $totalSalesCash,
            'total_sales_non_cash' => $totalSalesNonCash,
            'total_sales_amount' => $totalSalesAmount,
            'total_over_cash' => $totalOverCash,
            'total_short_cash' => $totalShortCash,
            'net_cash_difference' => $netCashDifference,
            'shifts' => $shifts,
        ];
    }

    /**
     * Generate Inventory Valuation Report.
     */
    public function getInventoryValuationReport(?int $locationId = null): array
    {
        $query = Inventory::with(['product.category', 'location']);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $inventories = $query->get();

        $totalAssetValue = 0.0;
        $totalStockQty = 0;
        $criticalCount = 0;
        $overstockCount = 0;

        $items = $inventories->map(function ($inv) use (&$totalAssetValue, &$totalStockQty, &$criticalCount, &$overstockCount) {
            $qty = (int) $inv->quantity;
            $cost = (float) ($inv->moving_average_cost > 0 ? $inv->moving_average_cost : ($inv->product->default_purchase_price ?? 0));
            $subtotalValue = round($qty * $cost, 2);

            $totalAssetValue += $subtotalValue;
            $totalStockQty += $qty;

            $minStock = (int) $inv->product->min_stock;
            if ($qty <= $minStock) {
                $criticalCount++;
            }

            return [
                'id' => $inv->id,
                'product_name' => $inv->product->name,
                'sku' => $inv->product->sku,
                'category_name' => $inv->product->category?->name ?? '-',
                'location_name' => $inv->location->name,
                'location_code' => $inv->location->code,
                'location_type' => $inv->location->type->value,
                'quantity' => $qty,
                'unit_name' => $inv->product->base_unit_name,
                'moving_average_cost' => $cost,
                'total_value' => $subtotalValue,
                'min_stock' => $minStock,
                'is_critical' => $qty <= $minStock,
            ];
        });

        // Location asset breakdown
        $locationBreakdown = $inventories->groupBy('location_id')->map(function ($locInvs) {
            $loc = $locInvs->first()->location;
            $val = $locInvs->sum(fn($i) => $i->quantity * ($i->moving_average_cost > 0 ? $i->moving_average_cost : ($i->product->default_purchase_price ?? 0)));
            $qty = (int) $locInvs->sum('quantity');

            return [
                'location_id' => $loc->id,
                'name' => $loc->name,
                'code' => $loc->code,
                'type' => $loc->type->label(),
                'type_badge' => $loc->type->badgeClass(),
                'total_quantity' => $qty,
                'total_value' => (float) $val,
            ];
        })->values();

        return [
            'total_asset_value' => $totalAssetValue,
            'total_stock_qty' => $totalStockQty,
            'critical_count' => $criticalCount,
            'items' => $items->sortByDesc('total_value')->values(),
            'locations' => $locationBreakdown,
        ];
    }
}
