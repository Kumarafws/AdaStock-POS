<?php

namespace App\Services;

use App\Enums\AdjustmentReason;
use App\Enums\LocationType;
use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockAdjustmentService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create and apply a stock adjustment atomically.
     *
     * @throws InsufficientStockException
     * @throws InvalidArgumentException
     */
    public function createAdjustment(array $data, User $actor): StockAdjustment
    {
        return DB::transaction(function () use ($data, $actor) {
            $location = Location::findOrFail($data['location_id']);
            $product = Product::findOrFail($data['product_id']);
            $quantity = abs((int) $data['quantity']);

            if ($quantity <= 0) {
                throw new InvalidArgumentException('Jumlah penyesuaian harus lebih dari 0.');
            }

            $type = strtolower($data['type'] ?? 'out');
            $actionType = strtolower($data['action_type'] ?? 'normal');
            $reason = is_string($data['reason']) ? AdjustmentReason::from($data['reason']) : $data['reason'];
            $notes = $data['notes'] ?? null;

            // Prevent quarantine-to-quarantine transfers
            if ($actionType === 'to_quarantine' && $location->type === LocationType::QUARANTINE) {
                throw new InvalidArgumentException('Lokasi sumber penyesuaian sudah merupakan Gudang Karantina.');
            }

            $adjustmentNumber = $this->generateAdjustmentNumber();

            // 1. Create StockAdjustment record
            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'location_id' => $location->id,
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
                'action_type' => $actionType,
                'cogs_at_time' => (float) ($product->purchase_price ?? 0),
                'notes' => $notes,
                'adjusted_by' => $actor->id,
            ]);

            // 2. Handle stock movements based on action type
            if ($actionType === 'to_quarantine') {
                $quarantineLocation = Location::where('type', LocationType::QUARANTINE)->first();
                if (!$quarantineLocation) {
                    throw new InvalidArgumentException('Gudang Karantina tidak ditemukan dalam sistem.');
                }

                // Step A: Deduct damaged goods from source location
                $this->inventoryService->recordMovement(
                    product: $product,
                    location: $location,
                    quantityInBaseUnit: -$quantity,
                    type: MovementType::ADJUSTMENT_OUT,
                    refType: StockAdjustment::class,
                    refId: $adjustment->id,
                    refNumber: $adjustmentNumber,
                    notes: "Pemindahan Barang Rusak ke {$quarantineLocation->name}. Alasan: {$reason->label()}" . ($notes ? " - {$notes}" : ''),
                    actor: $actor
                );

                // Step B: Receive damaged goods in quarantine location
                $this->inventoryService->recordMovement(
                    product: $product,
                    location: $quarantineLocation,
                    quantityInBaseUnit: $quantity,
                    type: MovementType::TRANSFER_IN,
                    refType: StockAdjustment::class,
                    refId: $adjustment->id,
                    refNumber: $adjustmentNumber,
                    notes: "Penerimaan Karantina dari {$location->name}. Alasan: {$reason->label()}" . ($notes ? " - {$notes}" : ''),
                    actor: $actor
                );

            } elseif ($actionType === 'disposal') {
                // Loss disposal (write-off / destruction of damaged goods)
                $this->inventoryService->recordMovement(
                    product: $product,
                    location: $location,
                    quantityInBaseUnit: -$quantity,
                    type: MovementType::LOSS_DISPOSAL,
                    refType: StockAdjustment::class,
                    refId: $adjustment->id,
                    refNumber: $adjustmentNumber,
                    notes: "Pemusnahan barang rusak / write-off. Alasan: {$reason->label()}" . ($notes ? " - {$notes}" : ''),
                    actor: $actor
                );

            } else {
                // Normal adjustment
                $signedQuantity = ($type === 'in') ? $quantity : -$quantity;
                $movementType = ($type === 'in') ? MovementType::ADJUSTMENT_IN : MovementType::ADJUSTMENT_OUT;

                $this->inventoryService->recordMovement(
                    product: $product,
                    location: $location,
                    quantityInBaseUnit: $signedQuantity,
                    type: $movementType,
                    refType: StockAdjustment::class,
                    refId: $adjustment->id,
                    refNumber: $adjustmentNumber,
                    notes: "Penyesuaian stok ({$type}). Alasan: {$reason->label()}" . ($notes ? " - {$notes}" : ''),
                    actor: $actor
                );
            }

            return $adjustment;
        });
    }

    /**
     * Generate unique adjustment document number: ADJ/YYYYMMDD/0001
     */
    public function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ/' . now()->format('Ymd') . '/';

        $lastNumber = StockAdjustment::where('adjustment_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('adjustment_number');

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
