<?php

namespace App\Http\Controllers;

use App\Enums\AdjustmentReason;
use App\Enums\LocationType;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockAdjustmentService $adjustmentService
    ) {}

    /**
     * List stock adjustment documents.
     */
    public function index(Request $request): View
    {
        $query = StockAdjustment::with(['location', 'product', 'adjuster']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->query('reason'));
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->query('action_type'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        $adjustments = $query->latest('id')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::active()->orderBy('type')->orderBy('name')->get();
        $reasons = AdjustmentReason::cases();

        return view('adjustments.index', [
            'adjustments' => $adjustments,
            'locations' => $locations,
            'reasons' => $reasons,
        ]);
    }

    /**
     * Show form to record a stock adjustment.
     */
    public function create(Request $request): View
    {
        $locations = Location::active()->orderBy('type')->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $reasons = AdjustmentReason::cases();

        $selectedLocationId = $request->query('location_id', $locations->first()?->id);
        $selectedProductId = $request->query('product_id');

        return view('adjustments.create', [
            'locations' => $locations,
            'products' => $products,
            'reasons' => $reasons,
            'selectedLocationId' => $selectedLocationId,
            'selectedProductId' => $selectedProductId,
        ]);
    }

    /**
     * Store and apply a new stock adjustment.
     */
    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        try {
            $adjustment = $this->adjustmentService->createAdjustment(
                $request->validated(),
                $request->user()
            );

            return redirect()
                ->route('adjustments.index')
                ->with('success', "Berita Acara Penyesuaian {$adjustment->adjustment_number} berhasil diproses dan dicatat ke Buku Besar Stok.");
        } catch (InsufficientStockException | InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memproses penyesuaian stok: ' . $e->getMessage());
        }
    }
}
