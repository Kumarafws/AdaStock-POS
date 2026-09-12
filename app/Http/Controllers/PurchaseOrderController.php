<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchasingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchasingService $purchasingService
    ) {}

    /**
     * Display a listing of Purchase Orders with filters and stats.
     */
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'destinationLocation', 'items.product']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('destination_location_id')) {
            $query->where('destination_location_id', $request->query('destination_location_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->query('date_to'));
        }

        $orders = $query->latest('id')->paginate(15)->withQueryString();

        // Summary statistics
        $totalOrders = PurchaseOrder::count();
        $activeOrders = PurchaseOrder::whereIn('status', [PurchaseOrderStatus::ORDERED, PurchaseOrderStatus::PARTIAL])->count();
        $totalSpend = (float) PurchaseOrder::where('status', '!=', PurchaseOrderStatus::CANCELLED)->sum('total_amount');
        $receivedOrders = PurchaseOrder::where('status', PurchaseOrderStatus::RECEIVED)->count();

        $suppliers = Supplier::active()->orderBy('name')->get();
        $locations = Location::active()->where('type', '!=', 'quarantine')->orderBy('name')->get();
        $statuses = PurchaseOrderStatus::cases();

        return view('purchasing.orders.index', [
            'orders' => $orders,
            'suppliers' => $suppliers,
            'locations' => $locations,
            'statuses' => $statuses,
            'summary' => [
                'total_orders' => $totalOrders,
                'active_orders' => $activeOrders,
                'total_spend' => $totalSpend,
                'received_orders' => $receivedOrders,
            ],
        ]);
    }

    /**
     * Show the form for creating a new Purchase Order.
     */
    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $locations = Location::active()->where('type', '!=', 'quarantine')->orderBy('name')->get();
        $products = Product::active()->with('units')->orderBy('name')->get();

        return view('purchasing.orders.create', [
            'suppliers' => $suppliers,
            'locations' => $locations,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created Purchase Order.
     */
    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        try {
            $po = $this->purchasingService->createPurchaseOrder(
                $request->validated(),
                $request->user()
            );

            return redirect()
                ->route('purchasing.orders.show', $po)
                ->with('success', "Purchase Order {$po->po_number} berhasil dibuat dan diterbitkan.");
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Purchase Order with receipt progress.
     */
    public function show(PurchaseOrder $order): View
    {
        $order->load([
            'supplier',
            'destinationLocation',
            'creator',
            'items.product.units',
            'goodsReceipts.receiver',
            'goodsReceipts.items.product',
        ]);

        return view('purchasing.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Cancel an active Purchase Order.
     */
    public function cancel(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->purchasingService->cancelPurchaseOrder(
                $order,
                $request->input('cancel_reason'),
                $request->user()
            );

            return redirect()
                ->route('purchasing.orders.show', $order)
                ->with('success', "Purchase Order {$order->po_number} berhasil dibatalkan.");
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
