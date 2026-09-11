<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display real-time inventory balances across locations.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $isCashier = $user->role->value === 'cashier';

        // Scoping for Cashier: strictly assigned store only
        $assignedStoreId = $isCashier ? $user->assigned_store_id : null;
        $selectedLocationId = $isCashier ? $assignedStoreId : $request->query('location_id');

        $query = Inventory::with(['product.category', 'product.units', 'location']);

        if ($selectedLocationId) {
            $query->where('location_id', $selectedLocationId);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->query('category_id'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->search($search);
            });
        }

        if ($request->filled('stock_status')) {
            $status = $request->query('stock_status');
            if ($status === 'out') {
                $query->where('quantity', '<=', 0);
            } elseif ($status === 'low') {
                $query->where('quantity', '>', 0)
                    ->whereHas('product', function ($q) {
                        $q->whereColumn('inventories.quantity', '<=', 'products.min_stock');
                    });
            } elseif ($status === 'safe') {
                $query->whereHas('product', function ($q) {
                    $q->whereColumn('inventories.quantity', '>', 'products.min_stock');
                });
            }
        }

        $inventories = $query->orderBy('location_id')
            ->orderByDesc('quantity')
            ->paginate(15)
            ->withQueryString();

        // Calculate summary statistics
        $baseStatsQuery = Inventory::query();
        if ($selectedLocationId) {
            $baseStatsQuery->where('location_id', $selectedLocationId);
        }

        $totalItems = (clone $baseStatsQuery)->distinct('product_id')->count('product_id');
        $totalQuantity = (clone $baseStatsQuery)->sum('quantity');
        
        $totalValuation = (clone $baseStatsQuery)
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity * products.purchase_price) as total_value')
            ->value('total_value') ?? 0;

        $lowStockCount = (clone $baseStatsQuery)
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.quantity', '>', 0)
            ->whereColumn('inventories.quantity', '<=', 'products.min_stock')
            ->count();

        $outOfStockCount = (clone $baseStatsQuery)
            ->where('quantity', '<=', 0)
            ->count();

        $locations = $isCashier 
            ? Location::where('id', $assignedStoreId)->get()
            : Location::active()->orderBy('type')->orderBy('name')->get();

        $categories = Category::active()->orderBy('name')->get();

        return view('inventory.index', [
            'inventories' => $inventories,
            'locations' => $locations,
            'categories' => $categories,
            'selectedLocationId' => $selectedLocationId,
            'isCashier' => $isCashier,
            'summary' => [
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_valuation' => (float) $totalValuation,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
            ],
        ]);
    }

    /**
     * Display immutable stock ledger / audit trail.
     */
    public function ledger(Request $request): View
    {
        // Only admin and manager can view global stock ledger
        if ($request->user()->role->value === 'cashier') {
            abort(403, 'Akses terbatas untuk Kasir. Buku besar mutasi hanya dapat diakses oleh Supervisor/Manager/Admin.');
        }

        $query = StockMovement::with(['product.category', 'location', 'creator']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->query('movement_type'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        $movements = $query->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $locations = Location::active()->orderBy('type')->orderBy('name')->get();
        $movementTypes = MovementType::cases();

        return view('inventory.ledger', [
            'movements' => $movements,
            'locations' => $locations,
            'movementTypes' => $movementTypes,
        ]);
    }

    /**
     * API to query current stock for an item at a location (used in Alpine.js forms).
     */
    public function checkStock(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        $locationId = $request->query('location_id');

        if (!$productId || !$locationId) {
            return response()->json(['error' => 'Missing product_id or location_id'], 422);
        }

        $product = Product::with('units')->find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $inventory = Inventory::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();

        $stock = $inventory ? $inventory->quantity : 0;
        $breakdown = $product->formatQuantityBreakdown($stock);

        return response()->json([
            'stock' => $stock,
            'unit' => $product->base_unit_name,
            'breakdown' => $breakdown,
            'purchase_price' => (float) $product->purchase_price,
            'selling_price' => (float) $product->default_selling_price,
        ]);
    }
}
