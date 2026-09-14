<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\CashierShift;
use App\Models\HeldCart;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PosService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Process checkout transaction atomically.
     *
     * @throws InvalidArgumentException
     * @throws InsufficientStockException
     */
    public function checkout(
        CashierShift $shift,
        User $cashier,
        array $cartItems,
        array $payments,
        ?string $customerName = 'Pelanggan Umum',
        float $discountAmount = 0,
        ?string $notes = null
    ): Sale {
        if (!$shift->isOpen()) {
            throw new InvalidArgumentException("Shift kasir ({$shift->shift_number}) telah ditutup. Tidak dapat memproses transaksi.");
        }

        if (empty($cartItems)) {
            throw new InvalidArgumentException('Keranjang belanja kasir masih kosong.');
        }

        if (empty($payments)) {
            throw new InvalidArgumentException('Metode pembayaran belum ditentukan.');
        }

        return DB::transaction(function () use (
            $shift,
            $cashier,
            $cartItems,
            $payments,
            $customerName,
            $discountAmount,
            $notes
        ) {
            $location = $shift->location;

            // 1. Calculate item subtotals and COGS
            $subtotal = 0.0;
            $totalCogs = 0.0;
            $preparedItems = [];

            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $productUnitId = !empty($item['product_unit_id']) ? (int) $item['product_unit_id'] : null;

                $unitName = $product->base_unit_name ?? $product->base_unit ?? 'Pcs';
                $conversionFactor = 1;
                $unitPrice = (float) ($product->default_selling_price ?? $product->selling_price ?? 0);
                $baseUnitPrice = $unitPrice;

                if ($productUnitId) {
                    $unit = ProductUnit::where('id', $productUnitId)
                        ->where('product_id', $product->id)
                        ->firstOrFail();
                    $unitName = $unit->unit_name;
                    $conversionFactor = (int) $unit->conversion_factor;
                    $unitPrice = (float) $unit->selling_price;
                    $baseUnitPrice = $conversionFactor > 0 ? ($unitPrice / $conversionFactor) : $unitPrice;
                }

                $quantityBase = $quantity * $conversionFactor;
                $baseUnitCost = (float) $product->purchase_price; // Locked Moving Average Cost
                $unitCost = $baseUnitCost * $conversionFactor;
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $lineSubtotal = max(0.0, ($quantity * $unitPrice) - $lineDiscount);
                $lineCogs = $quantityBase * $baseUnitCost;
                $lineProfit = $lineSubtotal - $lineCogs;

                $subtotal += $lineSubtotal;
                $totalCogs += $lineCogs;

                $preparedItems[] = [
                    'product' => $product,
                    'product_unit_id' => $productUnitId,
                    'unit_name' => $unitName,
                    'conversion_factor' => $conversionFactor,
                    'quantity' => $quantity,
                    'quantity_base' => $quantityBase,
                    'unit_price' => $unitPrice,
                    'base_unit_price' => $baseUnitPrice,
                    'unit_cost' => $unitCost,
                    'base_unit_cost' => $baseUnitCost,
                    'discount_amount' => $lineDiscount,
                    'subtotal' => $lineSubtotal,
                    'cogs_total' => $lineCogs,
                    'gross_profit' => $lineProfit,
                ];
            }

            // 2. Transaction level totals
            $transactionDiscount = max(0.0, (float) $discountAmount);
            $totalAmount = max(0.0, $subtotal - $transactionDiscount);
            $totalProfit = $totalAmount - $totalCogs;

            // 3. Payment calculations & validation
            $totalPaid = 0.0;
            $cashPaid = 0.0;
            $nonCashPaid = 0.0;
            $preparedPayments = [];

            foreach ($payments as $p) {
                $method = $p['payment_method'] instanceof PaymentMethod
                    ? $p['payment_method']
                    : PaymentMethod::from($p['payment_method']);

                $amount = (float) ($p['amount'] ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                $totalPaid += $amount;
                if ($method->isCash()) {
                    $cashPaid += $amount;
                } else {
                    $nonCashPaid += $amount;
                }

                $preparedPayments[] = [
                    'payment_method' => $method,
                    'amount' => $amount,
                    'reference_number' => $p['reference_number'] ?? null,
                    'payment_details' => $p['payment_details'] ?? null,
                ];
            }

            if ($totalPaid < $totalAmount) {
                $shortage = $totalAmount - $totalPaid;
                throw new InvalidArgumentException('Pembayaran kurang sebesar Rp ' . number_format($shortage, 0, ',', '.'));
            }

            // Non-cash payments cannot exceed total amount
            if ($nonCashPaid > $totalAmount) {
                throw new InvalidArgumentException('Pembayaran non-tunai (QRIS/Debit/Transfer) tidak boleh melebihi total tagihan belanja.');
            }

            $changeAmount = max(0.0, $totalPaid - $totalAmount);

            // 4. Create Sale Record
            $saleNumber = $this->generateSaleNumber();

            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'cashier_shift_id' => $shift->id,
                'user_id' => $cashier->id,
                'location_id' => $location->id,
                'customer_name' => trim($customerName) ?: 'Pelanggan Umum',
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $transactionDiscount,
                'tax_amount' => 0,
                'rounding_amount' => 0,
                'total_amount' => $totalAmount,
                'total_cogs' => $totalCogs,
                'total_profit' => $totalProfit,
                'paid_amount' => $totalPaid,
                'change_amount' => $changeAmount,
                'status' => SaleStatus::COMPLETED,
                'notes' => $notes,
            ]);

            // 5. Create Sale Items & Deduct Stock
            foreach ($preparedItems as $itemData) {
                /** @var Product $product */
                $product = $itemData['product'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_unit_id' => $itemData['product_unit_id'],
                    'unit_name' => $itemData['unit_name'],
                    'conversion_factor' => $itemData['conversion_factor'],
                    'quantity' => $itemData['quantity'],
                    'quantity_base' => $itemData['quantity_base'],
                    'unit_price' => $itemData['unit_price'],
                    'base_unit_price' => $itemData['base_unit_price'],
                    'unit_cost' => $itemData['unit_cost'],
                    'base_unit_cost' => $itemData['base_unit_cost'],
                    'discount_amount' => $itemData['discount_amount'],
                    'subtotal' => $itemData['subtotal'],
                    'cogs_total' => $itemData['cogs_total'],
                    'gross_profit' => $itemData['gross_profit'],
                ]);

                // Atomic stock decrement via InventoryService
                $this->inventoryService->recordMovement(
                    product: $product,
                    location: $location,
                    quantityInBaseUnit: -$itemData['quantity_base'],
                    type: MovementType::SALE,
                    refType: Sale::class,
                    refId: $sale->id,
                    refNumber: $sale->sale_number,
                    notes: "Penjualan Kasir POS - {$sale->sale_number} ({$itemData['quantity']} {$itemData['unit_name']})",
                    actor: $cashier
                );
            }

            // 6. Create Payments
            foreach ($preparedPayments as $paymentData) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $paymentData['payment_method']->value,
                    'amount' => $paymentData['amount'],
                    'reference_number' => $paymentData['reference_number'],
                    'payment_details' => $paymentData['payment_details'],
                ]);
            }

            // 7. Update Cashier Shift Counters
            $netCashReceived = max(0.0, $cashPaid - $changeAmount);

            $shift->total_sales_amount = (float) $shift->total_sales_amount + $totalAmount;
            $shift->total_sales_cash = (float) $shift->total_sales_cash + $netCashReceived;
            $shift->total_sales_non_cash = (float) $shift->total_sales_non_cash + $nonCashPaid;
            $shift->total_transactions_count = (int) $shift->total_transactions_count + 1;
            $shift->save();

            return $sale->load(['items.product', 'payments', 'location', 'cashier']);
        });
    }

    /**
     * Put a cart on hold for later recall.
     */
    public function holdCart(
        CashierShift $shift,
        User $cashier,
        string $reference,
        array $cartItems,
        ?string $customerName = null,
        float $discountAmount = 0,
        ?string $notes = null
    ): HeldCart {
        if (empty($cartItems)) {
            throw new InvalidArgumentException('Keranjang belanja kosong. Tidak ada transaksi yang perlu ditahan.');
        }

        return HeldCart::create([
            'reference' => trim($reference) ?: ('Antrean #' . (HeldCart::where('cashier_shift_id', $shift->id)->count() + 1)),
            'cashier_shift_id' => $shift->id,
            'user_id' => $cashier->id,
            'customer_name' => $customerName,
            'cart_items' => $cartItems,
            'discount_amount' => max(0, $discountAmount),
            'notes' => $notes,
            'held_at' => now(),
        ]);
    }

    /**
     * Recall a held cart and remove it from held list.
     */
    public function recallCart(HeldCart $heldCart): array
    {
        $data = [
            'reference' => $heldCart->reference,
            'customer_name' => $heldCart->customer_name,
            'cart_items' => $heldCart->cart_items,
            'discount_amount' => $heldCart->discount_amount,
            'notes' => $heldCart->notes,
        ];

        $heldCart->delete();

        return $data;
    }

    /**
     * Fast search catalog for POS with current store inventory.
     */
    public function searchCatalog(Location $store, ?string $query = null, ?int $categoryId = null): Collection
    {
        $productsQuery = Product::with(['category', 'units', 'inventories' => function ($q) use ($store) {
            $q->where('location_id', $store->id);
        }])->active();

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        if ($query) {
            $q = trim($query);
            $productsQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhereHas('units', function ($unitQuery) use ($q) {
                        $unitQuery->where('barcode', 'like', "%{$q}%");
                    });
            });
        }

        return $productsQuery->orderBy('name')->limit(30)->get();
    }

    /**
     * Generate unique standard POS invoice number: INV/YYYYMMDD/XXXX
     */
    public function generateSaleNumber(): string
    {
        $prefix = 'INV/' . now()->format('Ymd') . '/';

        $lastSale = Sale::withTrashed()
            ->where('sale_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if (!$lastSale) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($lastSale->sale_number, -4);
        $nextSeq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }
}
