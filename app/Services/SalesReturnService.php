<?php

namespace App\Services;

use App\Enums\LocationType;
use App\Enums\MovementType;
use App\Enums\RefundMethod;
use App\Enums\SalesReturnCondition;
use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\Location;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesReturnService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Process a customer sales return with item-level routing and shift reconciliation.
     *
     * @param Sale $sale
     * @param CashierShift|null $shift
     * @param User $cashier
     * @param array $returnItems Array of ['sale_item_id' => int, 'quantity' => int, 'condition' => string|SalesReturnCondition]
     * @param string|RefundMethod $refundMethod
     * @param string $reason
     * @param string|null $notes
     * @return SalesReturn
     *
     * @throws InvalidArgumentException
     */
    public function createReturn(
        Sale $sale,
        ?CashierShift $shift,
        User $cashier,
        array $returnItems,
        string|RefundMethod $refundMethod,
        string $reason,
        ?string $notes = null
    ): SalesReturn {
        return DB::transaction(function () use (
            $sale,
            $shift,
            $cashier,
            $returnItems,
            $refundMethod,
            $reason,
            $notes
        ) {
            // 1. Validate Sale Status
            if ($sale->isVoided()) {
                throw new InvalidArgumentException("Transaksi faktur {$sale->sale_number} sudah dibatalkan (void) dan tidak dapat diretur.");
            }

            if ($sale->status === SaleStatus::RETURNED_FULL) {
                throw new InvalidArgumentException("Seluruh item pada faktur {$sale->sale_number} sudah selesai diretur sebelumnya.");
            }

            // 2. Validate Refund Method & Shift
            $refundEnum = $refundMethod instanceof RefundMethod 
                ? $refundMethod 
                : RefundMethod::tryFrom($refundMethod) ?? RefundMethod::CASH;

            if ($refundEnum === RefundMethod::CASH) {
                if (!$shift || !$shift->isOpen()) {
                    throw new InvalidArgumentException("Pengembalian tunai membutuhkan sesi shift kasir yang aktif dan terbuka.");
                }
            }

            // 3. Validate Return Items
            if (empty($returnItems)) {
                throw new InvalidArgumentException("Minimal satu barang harus dipilih untuk diretur.");
            }

            // Filter out items with zero or negative quantity
            $validItems = array_filter($returnItems, fn($item) => isset($item['quantity']) && (int) $item['quantity'] > 0);
            if (empty($validItems)) {
                throw new InvalidArgumentException("Jumlah barang yang diretur harus lebih dari 0.");
            }

            // Resolve Quarantine Location in advance
            $quarantineLocation = Location::where('code', 'QRN-01')->first()
                ?? Location::where('type', LocationType::QUARANTINE)->first();

            if (!$quarantineLocation) {
                $quarantineLocation = Location::create([
                    'code' => 'QRN-01',
                    'name' => 'Gudang Karantina & Rusak',
                    'type' => LocationType::QUARANTINE,
                    'address' => 'Zona Karantina Produk',
                    'is_active' => true,
                ]);
            }

            $storeLocation = $sale->location ?? Location::where('type', LocationType::STORE)->firstOrFail();

            // 4. Generate Return Number & Instantiate Header
            $returnNumber = $this->generateReturnNumber();
            $salesReturn = SalesReturn::create([
                'return_number' => $returnNumber,
                'sale_id' => $sale->id,
                'cashier_shift_id' => $shift?->id,
                'user_id' => $cashier->id,
                'return_date' => now(),
                'total_refund_amount' => 0,
                'refund_method' => $refundEnum,
                'reason' => $reason,
                'notes' => $notes,
            ]);

            $totalRefundAmount = 0.0;

            // 5. Process each returned item
            foreach ($validItems as $itemData) {
                $saleItemId = $itemData['sale_item_id'];
                $returnQty = (int) $itemData['quantity'];
                $conditionInput = $itemData['condition'] ?? 'good';
                $condition = $conditionInput instanceof SalesReturnCondition 
                    ? $conditionInput 
                    : SalesReturnCondition::tryFrom($conditionInput) ?? SalesReturnCondition::GOOD;

                /** @var SaleItem $saleItem */
                $saleItem = $sale->items()->where('id', $saleItemId)->first();
                if (!$saleItem) {
                    throw new InvalidArgumentException("Item ID {$saleItemId} tidak ditemukan pada faktur {$sale->sale_number}.");
                }

                $alreadyReturned = (int) $saleItem->returnItems()->sum('quantity');
                $remainingQty = max(0, $saleItem->quantity - $alreadyReturned);

                if ($returnQty > $remainingQty) {
                    throw new InvalidArgumentException(
                        "Jumlah retur ({$returnQty}) untuk {$saleItem->product->name} melebihi sisa yang dapat diretur ({$remainingQty})."
                    );
                }

                // Calculate unit refund based on proportional net price paid in sale item
                $netUnitPrice = $saleItem->quantity > 0 
                    ? ($saleItem->subtotal / $saleItem->quantity) 
                    : $saleItem->unit_price;

                $lineRefundAmount = round($netUnitPrice * $returnQty, 2);
                $totalRefundAmount += $lineRefundAmount;

                $quantityBase = $returnQty * ($saleItem->conversion_factor ?: 1);

                // Physical routing: 'good' -> store location; 'damaged' -> quarantine location
                $destinationLocation = $condition->isDamaged() ? $quarantineLocation : $storeLocation;

                // Create SalesReturnItem
                $salesReturnItem = SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'unit_name' => $saleItem->unit_name,
                    'conversion_factor' => $saleItem->conversion_factor ?: 1,
                    'quantity' => $returnQty,
                    'quantity_base' => $quantityBase,
                    'unit_price' => $saleItem->unit_price,
                    'refund_amount' => $lineRefundAmount,
                    'condition' => $condition,
                    'destination_location_id' => $destinationLocation->id,
                ]);

                // Record Inventory Movement (Incoming into destination location)
                $this->inventoryService->recordMovement(
                    product: $saleItem->product,
                    location: $destinationLocation,
                    quantityInBaseUnit: $quantityBase, // Incoming is positive
                    type: MovementType::SALE_RETURN,
                    refType: SalesReturn::class,
                    refId: $salesReturn->id,
                    refNumber: $salesReturn->return_number,
                    notes: "Retur Penjualan #{$salesReturn->return_number} (Ref: {$sale->sale_number}) - {$condition->label()}",
                    actor: $cashier
                );
            }

            // 6. Update Header Total Refund Amount
            $salesReturn->update([
                'total_refund_amount' => $totalRefundAmount,
            ]);

            // 7. Reconcile Shift Cash if cash refund
            if ($refundEnum === RefundMethod::CASH && $shift && $shift->isOpen()) {
                $shift->total_sales_cash = max(0.0, (float) $shift->total_sales_cash - $totalRefundAmount);
                $shift->total_sales_amount = max(0.0, (float) $shift->total_sales_amount - $totalRefundAmount);
                $shift->save();
            }

            // 8. Update Sale Status (Partial vs Full Return)
            $sale->load('items.returnItems');
            $allFullyReturned = true;

            foreach ($sale->items as $item) {
                $totalReturned = (int) $item->returnItems->sum('quantity');
                if ($totalReturned < $item->quantity) {
                    $allFullyReturned = false;
                    break;
                }
            }

            $sale->status = $allFullyReturned ? SaleStatus::RETURNED_FULL : SaleStatus::RETURNED_PARTIAL;
            $sale->save();

            return $salesReturn->load(['items.product', 'items.destinationLocation', 'sale', 'cashier', 'shift']);
        });
    }

    /**
     * Generate unique return document number (Format: RET/YYYYMMDD/XXXX).
     */
    public function generateReturnNumber(): string
    {
        $prefix = 'RET/' . now()->format('Ymd') . '/';

        $lastReturn = SalesReturn::where('return_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if (!$lastReturn) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($lastReturn->return_number, -4);
        $nextSeq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }
}
