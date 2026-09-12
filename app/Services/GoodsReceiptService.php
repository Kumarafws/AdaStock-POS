<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\PurchaseOrderStatus;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoodsReceiptService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CostingService $costingService
    ) {}

    /**
     * Record a partial or full Goods Receipt against a Purchase Order.
     * Atomically increments physical inventory and triggers Moving Average Costing.
     */
    public function receiveGoods(
        PurchaseOrder $po,
        array $grData,
        array $receivedItems,
        User $receiver
    ): GoodsReceipt {
        return DB::transaction(function () use ($po, $grData, $receivedItems, $receiver) {
            if (!$po->status->canReceive()) {
                throw new InvalidArgumentException("Purchase Order {$po->po_number} berstatus {$po->status->label()} dan tidak dapat menerima pengiriman barang lagi.");
            }

            $receiptNumber = $this->generateReceiptNumber();
            $destinationLocation = $po->destinationLocation;

            // 1. Create Goods Receipt header
            $receipt = GoodsReceipt::create([
                'receipt_number' => $receiptNumber,
                'purchase_order_id' => $po->id,
                'location_id' => $destinationLocation->id,
                'received_date' => $grData['received_date'] ?? now(),
                'delivery_order_number' => $grData['delivery_order_number'] ?? null,
                'invoice_number' => $grData['invoice_number'] ?? null,
                'notes' => $grData['notes'] ?? null,
                'received_by' => $receiver->id,
            ]);

            $totalItemsReceived = 0;

            // 2. Process each received item
            foreach ($receivedItems as $itemData) {
                $poItemId = (int) $itemData['purchase_order_item_id'];
                $receivedQtyBase = (int) $itemData['received_quantity_base'];

                if ($receivedQtyBase <= 0) {
                    continue;
                }

                /** @var PurchaseOrderItem $poItem */
                $poItem = PurchaseOrderItem::with('product')
                    ->where('id', $poItemId)
                    ->where('purchase_order_id', $po->id)
                    ->firstOrFail();

                $remainingQty = $poItem->remaining_quantity_base;
                if ($receivedQtyBase > $remainingQty) {
                    throw new InvalidArgumentException(
                        "Kuantitas penerimaan untuk {$poItem->product->name} ({$receivedQtyBase} {$poItem->product->base_unit_name}) melebihi sisa pesanan yang belum datang ({$remainingQty} {$poItem->product->base_unit_name})."
                    );
                }

                $actualUnitCost = isset($itemData['actual_unit_cost']) && is_numeric($itemData['actual_unit_cost'])
                    ? (float) $itemData['actual_unit_cost']
                    : (float) $poItem->base_unit_cost;

                // Create Goods Receipt Item
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'received_quantity_base' => $receivedQtyBase,
                    'actual_unit_cost' => $actualUnitCost,
                ]);

                // Update received accumulator on PO item
                $poItem->received_quantity_base += $receivedQtyBase;
                $poItem->save();

                // 3. Atomically update inventory physical balance and record StockMovement
                $this->inventoryService->recordMovement(
                    product: $poItem->product,
                    location: $destinationLocation,
                    quantityInBaseUnit: $receivedQtyBase,
                    type: MovementType::PURCHASE_RECEIPT,
                    refType: GoodsReceipt::class,
                    refId: $receipt->id,
                    refNumber: $receiptNumber,
                    notes: "Penerimaan PO: {$po->po_number} - Surat Jalan: " . ($grData['delivery_order_number'] ?? '-'),
                    actor: $receiver
                );

                // 4. Atomically recalculate and apply Moving Average Cost (HPP Bergerak)
                $this->costingService->calculateAndApplyMovingAverage(
                    product: $poItem->product,
                    incomingQtyInBaseUnit: $receivedQtyBase,
                    newPurchasePrice: $actualUnitCost
                );

                $totalItemsReceived++;
            }

            if ($totalItemsReceived === 0) {
                throw new InvalidArgumentException('Minimal harus ada 1 item barang yang diterima dengan kuantitas lebih dari 0.');
            }

            // 5. Check and update overall Purchase Order status
            $po->refresh();
            $allItemsFullyReceived = $po->items->every(fn($item) => $item->is_fully_received);

            if ($allItemsFullyReceived) {
                $po->status = PurchaseOrderStatus::RECEIVED;
            } else {
                $po->status = PurchaseOrderStatus::PARTIAL;
            }
            $po->save();

            return $receipt->load(['items.product', 'location', 'purchaseOrder.supplier']);
        });
    }

    /**
     * Generate unique Goods Receipt number: GR/YYYYMMDD/0001
     */
    public function generateReceiptNumber(): string
    {
        $prefix = 'GR/' . now()->format('Ymd') . '/';

        $lastNumber = GoodsReceipt::where('receipt_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('receipt_number');

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
