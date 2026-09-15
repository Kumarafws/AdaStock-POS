<?php

namespace App\Http\Controllers;

use App\Enums\RefundMethod;
use App\Enums\SalesReturnCondition;
use App\Enums\SaleStatus;
use App\Http\Requests\CreateSalesReturnRequest;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\SalesReturnService;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class SalesReturnController extends Controller
{
    public function __construct(
        protected SalesReturnService $salesReturnService,
        protected ShiftService $shiftService
    ) {}

    /**
     * Display a listing of sales returns.
     */
    public function index(Request $request): View
    {
        $query = SalesReturn::with(['sale', 'cashier', 'shift', 'items.product', 'items.destinationLocation']);

        // Search by return number, invoice number, or customer name
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($sq) use ($search) {
                        $sq->where('sale_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by refund method
        if ($request->filled('refund_method')) {
            $query->where('refund_method', $request->query('refund_method'));
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('return_date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('return_date', '<=', $request->query('end_date'));
        }

        $returns = $query->latest('id')->paginate(15)->withQueryString();

        // Statistics
        $totalReturnsCount = SalesReturn::count();
        $totalRefundAmount = (float) SalesReturn::sum('total_refund_amount');
        $todayRefundAmount = (float) SalesReturn::whereDate('return_date', Carbon::today())->sum('total_refund_amount');
        $damagedItemsCount = (int) SalesReturnItem::where('condition', SalesReturnCondition::DAMAGED)->sum('quantity');

        return view('sales_returns.index', [
            'returns' => $returns,
            'totalReturnsCount' => $totalReturnsCount,
            'totalRefundAmount' => $totalRefundAmount,
            'todayRefundAmount' => $todayRefundAmount,
            'damagedItemsCount' => $damagedItemsCount,
            'refundMethods' => RefundMethod::cases(),
        ]);
    }

    /**
     * Show form to create a new sales return.
     */
    public function create(Request $request): View
    {
        $activeShift = $this->shiftService->getActiveShift(auth()->user());

        $saleId = $request->query('sale_id');
        $initialSale = null;

        if ($saleId) {
            $initialSale = Sale::with(['items.product', 'items.returnItems', 'location'])->find($saleId);
        }

        return view('sales_returns.create', [
            'activeShift' => $activeShift,
            'initialSale' => $initialSale,
            'refundMethods' => RefundMethod::cases(),
            'conditions' => SalesReturnCondition::cases(),
        ]);
    }

    /**
     * Lookup a sale invoice by sale_number or ID for return processing.
     */
    public function lookupSale(Request $request): JsonResponse
    {
        $query = trim($request->query('query', ''));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Masukkan nomor faktur atau ID transaksi.',
            ], 422);
        }

        /** @var Sale|null $sale */
        $sale = Sale::with(['items.product', 'items.returnItems', 'location'])
            ->where('sale_number', $query)
            ->orWhere('id', $query)
            ->first();

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => "Faktur '{$query}' tidak ditemukan.",
            ], 404);
        }

        if ($sale->isVoided()) {
            return response()->json([
                'success' => false,
                'message' => "Faktur {$sale->sale_number} telah dibatalkan (void) dan tidak dapat diretur.",
            ], 422);
        }

        if ($sale->status === SaleStatus::RETURNED_FULL) {
            return response()->json([
                'success' => false,
                'message' => "Seluruh item pada faktur {$sale->sale_number} sudah selesai diretur sebelumnya.",
            ], 422);
        }

        $items = $sale->items->map(function ($item) {
            $alreadyReturned = (int) $item->returnItems->sum('quantity');
            $remaining = max(0, $item->quantity - $alreadyReturned);
            $netUnitPrice = $item->quantity > 0 
                ? round($item->subtotal / $item->quantity, 2) 
                : $item->unit_price;

            return [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'sku' => $item->product->sku,
                'unit_name' => $item->unit_name,
                'conversion_factor' => $item->conversion_factor,
                'quantity_purchased' => $item->quantity,
                'quantity_returned' => $alreadyReturned,
                'quantity_remaining' => $remaining,
                'unit_price' => $item->unit_price,
                'net_unit_price' => $netUnitPrice,
                'subtotal' => $item->subtotal,
                'is_returnable' => $remaining > 0,
            ];
        });

        return response()->json([
            'success' => true,
            'sale' => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer_name' => $sale->customer_name ?: 'Pelanggan Umum',
                'transaction_date' => $sale->transaction_date->format('d/m/Y H:i'),
                'total_amount' => $sale->total_amount,
                'formatted_total' => $sale->formatted_total,
                'status' => $sale->status->value,
                'status_label' => $sale->status->label(),
                'status_badge' => $sale->status->badgeClass(),
                'location_name' => $sale->location?->name ?? 'Toko',
                'items' => $items,
            ],
        ]);
    }

    /**
     * Store a newly created sales return in storage.
     */
    public function store(CreateSalesReturnRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $sale = Sale::findOrFail($request->sale_id);
            $activeShift = $this->shiftService->getActiveShift(auth()->user());

            $salesReturn = $this->salesReturnService->createReturn(
                sale: $sale,
                shift: $activeShift,
                cashier: auth()->user(),
                returnItems: $request->items,
                refundMethod: $request->refund_method,
                reason: $request->reason,
                notes: $request->notes
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Retur berhasil diproses dengan nomor {$salesReturn->return_number}.",
                    'return_number' => $salesReturn->return_number,
                    'redirect_url' => route('returns.show', $salesReturn),
                ]);
            }

            return redirect()->route('returns.show', $salesReturn)
                ->with('success', "Retur berhasil diproses dengan nomor {$salesReturn->return_number}.");

        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified sales return document.
     */
    public function show(SalesReturn $salesReturn): View
    {
        $salesReturn->load([
            'sale.items.product',
            'sale.cashier',
            'cashier',
            'shift',
            'items.product',
            'items.destinationLocation',
            'movements.location',
        ]);

        return view('sales_returns.show', [
            'salesReturn' => $salesReturn,
        ]);
    }
}
