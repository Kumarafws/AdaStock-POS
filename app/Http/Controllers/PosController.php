<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\HoldCartRequest;
use App\Http\Requests\PosCheckoutRequest;
use App\Http\Requests\VerifyPinRequest;
use App\Http\Requests\VoidSaleRequest;
use App\Models\Category;
use App\Models\HeldCart;
use App\Models\Sale;
use App\Models\SaleVoidLog;
use App\Services\AuthorizationService;
use App\Services\PosService;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected ShiftService $shiftService,
        protected AuthorizationService $authorizationService
    ) {}

    /**
     * Display interactive cashier POS screen.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $activeShift = $this->shiftService->getActiveShift($user);

        if (!$activeShift) {
            return redirect()
                ->route('shifts.create')
                ->with('warning', 'Silakan buka register shift kasir terlebih dahulu sebelum mengakses layar POS.');
        }

        $store = $activeShift->location;
        $categories = Category::active()->orderBy('name')->get();
        $heldCarts = HeldCart::where('cashier_shift_id', $activeShift->id)
            ->latest('held_at')
            ->get();

        // Initial product catalog
        $products = $this->posService->searchCatalog($store);

        // Format products for JSON consumption in Alpine.js
        $formattedProducts = $products->map(function ($product) use ($store) {
            $currentStock = (int) ($product->inventories->firstWhere('location_id', $store->id)?->quantity ?? 0);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'base_unit' => $product->base_unit,
                'selling_price' => (float) $product->selling_price,
                'current_stock' => $currentStock,
                'units' => $product->units->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'unit_name' => $unit->unit_name,
                        'conversion_factor' => (int) $unit->conversion_factor,
                        'selling_price' => (float) $unit->selling_price,
                        'barcode' => $unit->barcode,
                    ];
                })->values()->all(),
            ];
        });

        $paymentMethods = array_map(function ($pm) {
            return [
                'value' => $pm->value,
                'label' => $pm->label(),
                'is_cash' => $pm->isCash(),
            ];
        }, PaymentMethod::cases());

        return view('pos.index', [
            'activeShift' => $activeShift,
            'store' => $store,
            'categories' => $categories,
            'heldCarts' => $heldCarts,
            'initialProducts' => $formattedProducts,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Search products catalog dynamically for barcode scanner or query.
     */
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeShift = $this->shiftService->getActiveShift($user);

        if (!$activeShift) {
            return response()->json(['error' => 'Shift kasir tidak aktif'], 403);
        }

        $store = $activeShift->location;
        $query = $request->query('q');
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;

        $products = $this->posService->searchCatalog($store, $query, $categoryId);

        $results = $products->map(function ($product) use ($store) {
            $currentStock = (int) ($product->inventories->firstWhere('location_id', $store->id)?->quantity ?? 0);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'base_unit' => $product->base_unit,
                'selling_price' => (float) $product->selling_price,
                'current_stock' => $currentStock,
                'units' => $product->units->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'unit_name' => $unit->unit_name,
                        'conversion_factor' => (int) $unit->conversion_factor,
                        'selling_price' => (float) $unit->selling_price,
                        'barcode' => $unit->barcode,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json($results);
    }

    /**
     * Process checkout transaction.
     */
    public function checkout(PosCheckoutRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $activeShift = $this->shiftService->getActiveShift($user);

        if (!$activeShift) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Shift kasir tidak aktif.'], 403);
            }
            return redirect()->route('shifts.create')->with('error', 'Buka shift terlebih dahulu.');
        }

        $discountSupervisor = null;
        if ($request->filled('supervisor_pin')) {
            try {
                $discountSupervisor = $this->authorizationService->verifySupervisorPin($request->input('supervisor_pin'));
            } catch (InvalidArgumentException $e) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        try {
            $sale = $this->posService->checkout(
                shift: $activeShift,
                cashier: $user,
                cartItems: $request->input('cart_items'),
                payments: $request->input('payments'),
                customerName: $request->input('customer_name', 'Pelanggan Umum'),
                discountAmount: (float) $request->input('discount_amount', 0),
                notes: $request->input('notes'),
                discountAuthorizedBy: $discountSupervisor
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$sale->sale_number} berhasil diproses.",
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'receipt_url' => route('pos.receipt', $sale),
                ]);
            }

            return redirect()
                ->route('pos.receipt', $sale)
                ->with('success', "Transaksi {$sale->sale_number} berhasil diselesaikan.");
        } catch (InsufficientStockException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display printable thermal receipt for a sale.
     */
    public function receipt(Sale $sale): View
    {
        $sale->load(['items.product', 'payments', 'location', 'cashier']);

        return view('pos.receipt', [
            'sale' => $sale,
        ]);
    }

    /**
     * Hold current cart for later recall.
     */
    public function hold(HoldCartRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $activeShift = $this->shiftService->getActiveShift($user);

        if (!$activeShift) {
            return response()->json(['message' => 'Shift kasir tidak aktif.'], 403);
        }

        try {
            $heldCart = $this->posService->holdCart(
                shift: $activeShift,
                cashier: $user,
                reference: $request->input('reference', ''),
                cartItems: $request->input('cart_items'),
                customerName: $request->input('customer_name'),
                discountAmount: (float) $request->input('discount_amount', 0),
                notes: $request->input('notes')
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Keranjang '{$heldCart->reference}' berhasil ditahan.",
                    'held_cart' => $heldCart,
                ]);
            }

            return redirect()->route('pos.index')->with('success', "Keranjang '{$heldCart->reference}' berhasil ditahan.");
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Recall a held cart back into memory.
     */
    public function recall(Request $request, HeldCart $heldCart): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $activeShift = $this->shiftService->getActiveShift($user);

        if (!$activeShift || $heldCart->cashier_shift_id !== $activeShift->id) {
            return response()->json(['message' => 'Tidak dapat memanggil keranjang dari shift lain.'], 403);
        }

        $data = $this->posService->recallCart($heldCart);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Keranjang '{$data['reference']}' berhasil dimuat kembali.",
                'data' => $data,
            ]);
        }

        return redirect()->route('pos.index')->with('success', "Keranjang '{$data['reference']}' berhasil dimuat kembali.");
    }

    /**
     * Verify supervisor PIN via API.
     */
    public function verifyPin(VerifyPinRequest $request): JsonResponse
    {
        try {
            $supervisor = $this->authorizationService->verifySupervisorPin($request->input('pin'));

            return response()->json([
                'success' => true,
                'message' => "Otorisasi berhasil oleh {$supervisor->name} ({$supervisor->role->label()}).",
                'supervisor' => [
                    'id' => $supervisor->id,
                    'name' => $supervisor->name,
                    'role' => $supervisor->role->value,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Void a completed sale with supervisor authorization.
     */
    public function voidSale(VoidSaleRequest $request, Sale $sale): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        try {
            // 1. Verify Supervisor PIN
            $supervisor = $this->authorizationService->verifySupervisorPin($request->input('pin'));

            // 2. Execute Void via PosService
            $voidedSale = $this->posService->voidSale(
                sale: $sale,
                cashier: $user,
                supervisor: $supervisor,
                reason: $request->input('reason'),
                notes: $request->input('notes')
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$voidedSale->sale_number} berhasil dibatalkan (VOID).",
                    'sale_id' => $voidedSale->id,
                    'sale_number' => $voidedSale->sale_number,
                    'redirect_url' => route('pos.receipt', $voidedSale),
                ]);
            }

            return redirect()
                ->route('pos.receipt', $voidedSale)
                ->with('success', "Transaksi {$voidedSale->sale_number} berhasil dibatalkan (VOID).");
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display void audit logs (Admin & Manager).
     */
    public function voidLogs(Request $request): View
    {
        $query = SaleVoidLog::with(['sale.location', 'cashier', 'supervisor']);

        if ($request->filled('date_from')) {
            $query->whereDate('voided_at', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('voided_at', '<=', $request->query('date_to'));
        }
        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $query->whereHas('sale', function ($q) use ($s) {
                $q->where('sale_number', 'like', "%{$s}%");
            });
        }

        $logs = $query->latest('voided_at')->paginate(15)->withQueryString();

        return view('pos.void_logs', [
            'logs' => $logs,
        ]);
    }
}
