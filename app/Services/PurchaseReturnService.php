<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseReturnService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create a purchase return to supplier and deduct stock.
     */
    public function createReturn(array $data, array $items, User $actor): PurchaseReturn
    {
        return DB::transaction(function () use ($data, $items, $actor) {
            if (empty($items)) {
                throw new InvalidArgumentException('Retur pembelian harus memiliki minimal 1 item.');
            }

            $supplier = Supplier::findOrFail($data['supplier_id']);
            $location = Location::findOrFail($data['location_id']);
            $returnNumber = $this->generateReturnNumber();

            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (int) $itemData['quantity'];
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Jumlah retur untuk {$product->name} harus lebih dari 0.");
                }

                $unitCost = isset($itemData['unit_cost']) && is_numeric($itemData['unit_cost'])
                    ? (float) $itemData['unit_cost']
                    : (float) $product->purchase_price;

                $subtotal = round($qty * $unitCost, 2);
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ];
            }

            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'supplier_id' => $supplier->id,
                'location_id' => $location->id,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'goods_receipt_id' => $data['goods_receipt_id'] ?? null,
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? 'Barang Rusak / Cacat / Salah Kirim',
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'returned_by' => $actor->id,
            ]);

            foreach ($preparedItems as $prep) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $prep['product']->id,
                    'quantity' => $prep['quantity'],
                    'unit_cost' => $prep['unit_cost'],
                    'subtotal' => $prep['subtotal'],
                    'notes' => $prep['notes'],
                ]);

                // Atomically deduct inventory with PURCHASE_RETURN movement type
                $this->inventoryService->recordMovement(
                    product: $prep['product'],
                    location: $location,
                    quantityInBaseUnit: -$prep['quantity'],
                    type: MovementType::PURCHASE_RETURN,
                    refType: PurchaseReturn::class,
                    refId: $return->id,
                    refNumber: $returnNumber,
                    notes: "Retur Pembelian ke {$supplier->name}. Alasan: {$return->reason}" . ($prep['notes'] ? " - {$prep['notes']}" : ''),
                    actor: $actor
                );
            }

            return $return->load(['items.product', 'supplier', 'location']);
        });
    }

    /**
     * Generate unique Return number: PR/YYYYMMDD/0001
     */
    public function generateReturnNumber(): string
    {
        $prefix = 'PR/' . now()->format('Ymd') . '/';

        $lastNumber = PurchaseReturn::where('return_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('return_number');

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
