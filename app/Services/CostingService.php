<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CostingService
{
    /**
     * Calculate and update product's moving average cost (HPP).
     *
     * Formula:
     * New Cost = ((Current Total Stock * Current Cost) + (Incoming Qty * New Unit Price)) / (Current Total Stock + Incoming Qty)
     */
    public function calculateAndApplyMovingAverage(
        Product $product,
        int $incomingQtyInBaseUnit,
        float $newPurchasePrice
    ): float {
        return DB::transaction(function () use ($product, $incomingQtyInBaseUnit, $newPurchasePrice) {
            $newAverage = $this->calculateProjectedAverage($product, $incomingQtyInBaseUnit, $newPurchasePrice);

            $product->purchase_price = $newAverage;
            $product->save();

            return $newAverage;
        });
    }

    /**
     * Preview calculated moving average cost without saving.
     */
    public function calculateProjectedAverage(
        Product $product,
        int $incomingQtyInBaseUnit,
        float $newPurchasePrice
    ): float {
        if ($incomingQtyInBaseUnit <= 0) {
            return (float) $product->purchase_price;
        }

        // Sum current stock across all locations
        $currentStock = (int) $product->inventories()->sum('quantity');
        $currentCost = (float) $product->purchase_price;

        if ($currentStock <= 0) {
            return round($newPurchasePrice, 2);
        }

        $totalOldValue = $currentStock * $currentCost;
        $totalIncomingValue = $incomingQtyInBaseUnit * $newPurchasePrice;
        $newTotalQty = $currentStock + $incomingQtyInBaseUnit;

        if ($newTotalQty <= 0) {
            return round($newPurchasePrice, 2);
        }

        return round(($totalOldValue + $totalIncomingValue) / $newTotalQty, 2);
    }
}
