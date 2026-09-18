<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Resolve start and end dates from request parameters or presets.
     */
    protected function resolveDateRange(Request $request): array
    {
        $preset = $request->query('preset');

        if ($preset === 'today') {
            return [Carbon::today(), Carbon::today()];
        } elseif ($preset === 'yesterday') {
            return [Carbon::yesterday(), Carbon::yesterday()];
        } elseif ($preset === 'last_7_days') {
            return [Carbon::today()->subDays(6), Carbon::today()];
        } elseif ($preset === 'last_30_days') {
            return [Carbon::today()->subDays(29), Carbon::today()];
        } elseif ($preset === 'this_month') {
            return [Carbon::today()->startOfMonth(), Carbon::today()];
        }

        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->query('start_date'))->startOfDay() 
            : Carbon::today()->startOfMonth();

        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->query('end_date'))->endOfDay() 
            : Carbon::today()->endOfDay();

        return [$startDate, $endDate];
    }

    /**
     * Display the Sales Performance Report.
     */
    public function sales(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $locationId = $request->filled('location_id') ? (int) $request->query('location_id') : null;
        $cashierId = $request->filled('cashier_id') ? (int) $request->query('cashier_id') : null;

        $report = $this->reportService->getSalesReport($startDate, $endDate, $locationId, $cashierId);

        $locations = Location::active()->orderBy('name')->get();
        $cashiers = User::orderBy('name')->get();

        return view('reports.sales', [
            'report' => $report,
            'locations' => $locations,
            'cashiers' => $cashiers,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'selectedPreset' => $request->query('preset', 'this_month'),
        ]);
    }

    /**
     * Export Sales Report as CSV.
     */
    public function exportSalesCsv(Request $request): StreamedResponse
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $locationId = $request->filled('location_id') ? (int) $request->query('location_id') : null;
        $cashierId = $request->filled('cashier_id') ? (int) $request->query('cashier_id') : null;

        return $this->reportService->exportSalesCsv($startDate, $endDate, $locationId, $cashierId);
    }

    /**
     * Display the Gross Profit & Margin Analysis Report.
     */
    public function profit(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $categoryId = $request->filled('category_id') ? (int) $request->query('category_id') : null;
        $brandId = $request->filled('brand_id') ? (int) $request->query('brand_id') : null;

        $report = $this->reportService->getGrossProfitReport($startDate, $endDate, $categoryId, $brandId);

        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('reports.profit', [
            'report' => $report,
            'categories' => $categories,
            'brands' => $brands,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'selectedPreset' => $request->query('preset', 'this_month'),
        ]);
    }

    /**
     * Display the Cash Register Shift Reconciliation Report.
     */
    public function shifts(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $locationId = $request->filled('location_id') ? (int) $request->query('location_id') : null;

        $report = $this->reportService->getShiftReconciliationReport($startDate, $endDate, $locationId);
        $locations = Location::active()->orderBy('name')->get();

        return view('reports.shifts', [
            'report' => $report,
            'locations' => $locations,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'selectedPreset' => $request->query('preset', 'this_month'),
        ]);
    }

    /**
     * Display the Inventory Valuation Report.
     */
    public function inventoryValuation(Request $request): View
    {
        $locationId = $request->filled('location_id') ? (int) $request->query('location_id') : null;

        $report = $this->reportService->getInventoryValuationReport($locationId);
        $locations = Location::active()->orderBy('name')->get();

        return view('reports.inventory_valuation', [
            'report' => $report,
            'locations' => $locations,
        ]);
    }
}
