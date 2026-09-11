<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record an atomic stock movement with pessimistic locking and negative stock protection.
     *
     * @throws InsufficientStockException
     */
    public function recordMovement(
        Product $product,
        Location $location,
        int $quantityInBaseUnit,
        MovementType $type,
        ?string $refType = null,
        ?int $refId = null,
        ?string $refNumber = null,
        ?string $notes = null,
        ?User $actor = null
    ): StockMovement {
        return DB::transaction(function () use (
            $product,
            $location,
            $quantityInBaseUnit,
            $type,
            $refType,
            $refId,
            $refNumber,
            $notes,
            $actor
        ) {
            // 1. Ensure inventory row exists and lock for update
            $existing = Inventory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            /** @var Inventory $inventory */
            $inventory = Inventory::where('id', $existing->id)
                ->lockForUpdate()
                ->firstOrFail();

            $balanceBefore = $inventory->quantity;

            // 2. Validate against negative stock if outgoing
            if ($quantityInBaseUnit < 0) {
                $requestedQty = abs($quantityInBaseUnit);
                if ($inventory->quantity < $requestedQty) {
                    throw new InsufficientStockException(
                        "Stok tidak mencukupi untuk {$product->name} di {$location->name}. Saldo saat ini: {$inventory->quantity} {$product->base_unit_name}, diminta: {$requestedQty} {$product->base_unit_name}."
                    );
                }
            }

            // 3. Update inventory balance
            $balanceAfter = $balanceBefore + $quantityInBaseUnit;
            $inventory->quantity = $balanceAfter;
            $inventory->save();

            // 4. Resolve creator actor
            $creatorId = $actor?->id 
                ?? auth()->id() 
                ?? User::where('role', 'admin')->value('id') 
                ?? 1;

            // 5. Create immutable stock movement record
            return StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'quantity' => $quantityInBaseUnit,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'movement_type' => $type,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'reference_number' => $refNumber,
                'cogs_per_unit' => (float) ($product->purchase_price ?? 0),
                'notes' => $notes,
                'created_by' => $creatorId,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Get stock balance of a product at a specific location.
     */
    public function getStock(Product $product, Location $location): int
    {
        return (int) Inventory::where('product_id', $product->id)
            ->where('location_id', $location->id)
            ->value('quantity') ?? 0;
    }

    /**
     * Get or create inventory instance.
     */
    public function getInventory(Product $product, Location $location): Inventory
    {
        return Inventory::firstOrCreate(
            [
                'product_id' => $product->id,
                'location_id' => $location->id,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
            ]
        );
    }
}
