<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected GoodsReceiptService $goodsReceiptService
    ) {}

    /**
     * Display a listing of Goods Receipts (Penerimaan Barang).
     */
    public function index(Request $request): View
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'location', 'receiver', 'items.product']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhere('delivery_order_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn($pq) => $pq->where('po_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('received_date', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('received_date', '<=', $request->query('date_to'));
        }

        $receipts = $query->latest('id')->paginate(15)->withQueryString();
        $locations = Location::active()->orderBy('name')->get();

        return view('purchasing.receipts.index', [
            'receipts' => $receipts,
            'locations' => $locations,
        ]);
    }

    /**
     * Show form to receive goods for a specific Purchase Order.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $poId = $request->query('po_id');
        if (!$poId) {
            return redirect()
                ->route('purchasing.orders.index')
                ->with('error', 'Silakan pilih Purchase Order terlebih dahulu untuk melakukan penerimaan barang.');
        }

        $order = PurchaseOrder::with(['items.product.units', 'destinationLocation', 'supplier'])->findOrFail($poId);

        if (!$order->status->canReceive()) {
            return redirect()
                ->route('purchasing.orders.show', $order)
                ->with('error', "Purchase Order {$order->po_number} berstatus {$order->status->label()} dan tidak dapat menerima barang lagi.");
        }

        return view('purchasing.receipts.create', [
            'order' => $order,
        ]);
    }

    /**
     * Store and apply a Goods Receipt atomically.
     */
    public function store(GoodsReceiptRequest $request): RedirectResponse
    {
        $po = PurchaseOrder::with(['items.product', 'destinationLocation'])->findOrFail($request->input('purchase_order_id'));

        try {
            $receipt = $this->goodsReceiptService->receiveGoods(
                po: $po,
                grData: $request->only(['received_date', 'delivery_order_number', 'invoice_number', 'notes']),
                receivedItems: $request->input('items', []),
                receiver: $request->user()
            );

            return redirect()
                ->route('purchasing.receipts.show', $receipt)
                ->with('success', "Penerimaan Barang {$receipt->receipt_number} berhasil dicatat. Saldo stok bertambah dan HPP produk telah disesuaikan secara otomatis.");
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
                ->with('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage());
        }
    }

    /**
     * Display details of a Goods Receipt.
     */
    public function show(GoodsReceipt $receipt): View
    {
        $receipt->load([
            'purchaseOrder.supplier',
            'location',
            'receiver',
            'items.product.units',
            'items.purchaseOrderItem',
            'movements',
        ]);

        return view('purchasing.receipts.show', [
            'receipt' => $receipt,
        ]);
    }
}
