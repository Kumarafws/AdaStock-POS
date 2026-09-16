<?php

namespace App\Http\Controllers;

use App\Enums\OpnameStatus;
use App\Http\Requests\CreateStockOpnameRequest;
use App\Http\Requests\UpdatePhysicalCountRequest;
use App\Models\Category;
use App\Models\Location;
use App\Models\StockOpname;
use App\Services\StockOpnameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StockOpnameController extends Controller
{
    public function __construct(
        protected StockOpnameService $stockOpnameService
    ) {}

    /**
     * Display a listing of Stock Opname sessions.
     */
    public function index(Request $request): View
    {
        $query = StockOpname::with(['location', 'category', 'creator', 'approver']);

        // Search by opname number
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where('opname_number', 'like', "%{$search}%");
        }

        // Filter by location
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('opname_date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('opname_date', '<=', $request->query('end_date'));
        }

        $opnames = $query->latest('id')->paginate(15)->withQueryString();

        // Statistics
        $activeCount = StockOpname::where('status', OpnameStatus::IN_PROGRESS)->count();
        $completedCount = StockOpname::where('status', OpnameStatus::COMPLETED)->count();
        $totalVarianceQty = (int) StockOpname::where('status', OpnameStatus::COMPLETED)->sum('total_variance_qty');
        $totalVarianceAmount = (float) StockOpname::where('status', OpnameStatus::COMPLETED)->sum('total_variance_amount');

        $locations = Location::active()->orderBy('name')->get();

        return view('stock_opnames.index', [
            'opnames' => $opnames,
            'activeCount' => $activeCount,
            'completedCount' => $completedCount,
            'totalVarianceQty' => $totalVarianceQty,
            'totalVarianceAmount' => $totalVarianceAmount,
            'locations' => $locations,
            'statuses' => OpnameStatus::cases(),
        ]);
    }

    /**
     * Show the form for creating a new Stock Opname session.
     */
    public function create(): View
    {
        $locations = Location::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        return view('stock_opnames.create', [
            'locations' => $locations,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created Stock Opname session in storage.
     */
    public function store(CreateStockOpnameRequest $request): RedirectResponse
    {
        $location = Location::findOrFail($request->location_id);
        $categoryId = $request->filled('category_id') ? (int) $request->category_id : null;

        $opname = $this->stockOpnameService->createOpname(
            location: $location,
            creator: auth()->user(),
            categoryId: $categoryId,
            notes: $request->notes
        );

        return redirect()->route('opnames.show', $opname)
            ->with('success', "Sesi Stock Opname {$opname->opname_number} berhasil dimulai. Silakan lakukan pencatatan hitungan fisik.");
    }

    /**
     * Display the specified Stock Opname session & count sheet.
     */
    public function show(StockOpname $stockOpname): View
    {
        $stockOpname->load([
            'location',
            'category',
            'creator',
            'approver',
            'items.product.category',
            'movements.location',
        ]);

        return view('stock_opnames.show', [
            'opname' => $stockOpname,
        ]);
    }

    /**
     * Update physical counts submitted by count team.
     */
    public function updateCounts(UpdatePhysicalCountRequest $request, StockOpname $stockOpname): JsonResponse|RedirectResponse
    {
        try {
            $updatedOpname = $this->stockOpnameService->updatePhysicalCounts($stockOpname, $request->counts);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hitungan fisik berhasil disimpan.',
                    'total_physical_qty' => $updatedOpname->total_physical_qty,
                    'total_variance_qty' => $updatedOpname->total_variance_qty,
                    'total_variance_amount' => $updatedOpname->total_variance_amount,
                    'formatted_variance_amount' => $updatedOpname->formatted_variance_amount,
                ]);
            }

            return redirect()->back()->with('success', 'Hitungan fisik berhasil diperbarui.');
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Finalize and approve Stock Opname, adjusting ledger inventory.
     */
    public function complete(Request $request, StockOpname $stockOpname): RedirectResponse
    {
        try {
            $this->stockOpnameService->completeOpname($stockOpname, auth()->user());

            return redirect()->route('opnames.show', $stockOpname)
                ->with('success', "Sesi Stock Opname {$stockOpname->opname_number} berhasil disetujui. Seluruh saldo inventori telah diselaraskan persis dengan data fisik.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel an active Stock Opname session.
     */
    public function cancel(Request $request, StockOpname $stockOpname): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->stockOpnameService->cancelOpname($stockOpname, auth()->user(), $request->input('reason'));

            return redirect()->route('opnames.show', $stockOpname)
                ->with('warning', "Sesi Stock Opname {$stockOpname->opname_number} telah dibatalkan.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
