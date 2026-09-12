<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseReturnRequest;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Services\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $returnService
    ) {}

    /**
     * Display a listing of Purchase Returns to Suppliers.
     */
    public function index(Request $request): View
    {
        $query = PurchaseReturn::with(['supplier', 'location', 'returner', 'items.product']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->query('location_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $returns = $query->latest('id')->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();

        return view('purchasing.returns.index', [
            'returns' => $returns,
            'suppliers' => $suppliers,
            'locations' => $locations,
        ]);
    }

    /**
     * Show form to create a new Purchase Return.
     */
    public function create(Request $request): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get(); // Includes quarantine!
        $products = Product::active()->orderBy('name')->get();
        $orders = PurchaseOrder::whereIn('status', ['partial', 'received'])->orderByDesc('id')->get();

        return view('purchasing.returns.create', [
            'suppliers' => $suppliers,
            'locations' => $locations,
            'products' => $products,
            'orders' => $orders,
            'preselectedPoId' => $request->query('po_id'),
        ]);
    }

    /**
     * Store and process a Purchase Return.
     */
    public function store(PurchaseReturnRequest $request): RedirectResponse
    {
        try {
            $return = $this->returnService->createReturn(
                data: $request->validated(),
                items: $request->input('items', []),
                actor: $request->user()
            );

            return redirect()
                ->route('purchasing.returns.show', $return)
                ->with('success', "Retur Pembelian {$return->return_number} berhasil diproses. Stok barang telah dikeluarkan dari lokasi.");
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
                ->with('error', 'Gagal memproses retur pembelian: ' . $e->getMessage());
        }
    }

    /**
     * Display details of a Purchase Return.
     */
    public function show(PurchaseReturn $return): View
    {
        $return->load([
            'supplier',
            'location',
            'returner',
            'items.product',
            'purchaseOrder',
            'movements',
        ]);

        return view('purchasing.returns.show', [
            'return' => $return,
        ]);
    }
}
