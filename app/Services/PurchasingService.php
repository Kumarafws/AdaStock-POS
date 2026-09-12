<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchasingService
{
    /**
     * Create a new Purchase Order atomically.
     */
    public function createPurchaseOrder(array $data, User $creator): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $poNumber = $this->generatePONumber();

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new InvalidArgumentException('Purchase Order harus memiliki minimal 1 item.');
            }

            $subtotal = 0;
            $preparedItems = [];

            foreach ($itemsData as $item) {
                $product = Product::findOrFail($item['product_id']);
                $orderedQty = (int) $item['ordered_quantity'];
                if ($orderedQty <= 0) {
                    throw new InvalidArgumentException("Kuantitas pesanan untuk {$product->name} harus lebih dari 0.");
                }

                $unitCost = (float) $item['unit_cost'];
                if ($unitCost < 0) {
                    throw new InvalidArgumentException("Harga beli per unit untuk {$product->name} tidak boleh negatif.");
                }

                $productUnitId = !empty($item['product_unit_id']) ? (int) $item['product_unit_id'] : null;
                $conversionFactor = 1;
                $unitName = $product->base_unit_name;

                if ($productUnitId) {
                    $productUnit = ProductUnit::where('id', $productUnitId)
                        ->where('product_id', $product->id)
                        ->first();

                    if ($productUnit) {
                        $conversionFactor = max(1, (int) $productUnit->conversion_factor);
                        $unitName = $productUnit->unit_name;
                    }
                }

                $orderedQtyBase = $orderedQty * $conversionFactor;
                $baseUnitCost = $conversionFactor > 0 ? round($unitCost / $conversionFactor, 2) : $unitCost;
                $itemSubtotal = round($orderedQty * $unitCost, 2);
                $subtotal += $itemSubtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_unit_id' => $productUnitId,
                    'unit_name' => $unitName,
                    'conversion_factor' => $conversionFactor,
                    'ordered_quantity' => $orderedQty,
                    'ordered_quantity_base' => $orderedQtyBase,
                    'received_quantity_base' => 0,
                    'unit_cost' => $unitCost,
                    'base_unit_cost' => $baseUnitCost,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'destination_location_id' => $data['destination_location_id'],
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => PurchaseOrderStatus::ORDERED,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
            ]);

            foreach ($preparedItems as $item) {
                $item['purchase_order_id'] = $po->id;
                PurchaseOrderItem::create($item);
            }

            return $po->load(['items.product', 'supplier', 'destinationLocation']);
        });
    }

    /**
     * Cancel an active Purchase Order.
     */
    public function cancelPurchaseOrder(PurchaseOrder $po, ?string $reason, User $actor): void
    {
        if (!$po->status->canCancel()) {
            throw new InvalidArgumentException("Purchase Order {$po->po_number} berstatus {$po->status->label()} dan tidak dapat dibatalkan.");
        }

        if ($po->goodsReceipts()->exists()) {
            throw new InvalidArgumentException("Purchase Order {$po->po_number} sudah memiliki catatan penerimaan barang dan tidak dapat dibatalkan.");
        }

        $po->status = PurchaseOrderStatus::CANCELLED;
        if ($reason) {
            $po->notes = trim(($po->notes ?? '') . "\n[Dibatalkan oleh {$actor->name}: {$reason}]");
        }
        $po->save();
    }

    /**
     * Generate unique PO number: PO/YYYYMMDD/0001
     */
    public function generatePONumber(): string
    {
        $prefix = 'PO/' . now()->format('Ymd') . '/';

        $lastNumber = PurchaseOrder::withTrashed()
            ->where('po_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('po_number');

        if ($lastNumber) {
            $parts = explode('/', $lastNumber);
            $lastSeq = (int) end($parts);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
