<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\OpnameStatus;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockOpnameService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Initiate a new Stock Opname session and freeze system inventory balances.
     */
    public function createOpname(
        Location $location,
        User $creator,
        ?int $categoryId = null,
        ?string $notes = null
    ): StockOpname {
        return DB::transaction(function () use ($location, $creator, $categoryId, $notes) {
            $opnameNumber = $this->generateOpnameNumber();

            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'location_id' => $location->id,
                'category_id' => $categoryId,
                'opname_date' => now()->toDateString(),
                'status' => OpnameStatus::IN_PROGRESS,
                'total_system_qty' => 0,
                'total_physical_qty' => 0,
                'total_variance_qty' => 0,
                'total_variance_amount' => 0,
                'notes' => $notes,
                'created_by' => $creator->id,
            ]);

            // Query active products for this opname scope
            $productQuery = Product::where('is_active', true);
            if ($categoryId) {
                $productQuery->where('category_id', $categoryId);
            }
            $products = $productQuery->orderBy('name')->get();

            $totalSystemQty = 0;

            // Load existing inventories for this location
            $inventories = Inventory::where('location_id', $location->id)
                ->whereIn('product_id', $products->pluck('id'))
                ->get()
                ->keyBy('product_id');

            foreach ($products as $product) {
                $inventory = $inventories->get($product->id);
                $systemQty = (int) ($inventory?->quantity ?? 0);
                
                // Unit cost: use moving average cost if available, otherwise default purchase price
                $unitCost = (float) (
                    ($inventory && $inventory->moving_average_cost > 0)
                        ? $inventory->moving_average_cost 
                        : ($product->default_purchase_price ?? 0)
                );

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'system_qty' => $systemQty,
                    'physical_qty' => null,
                    'difference_qty' => null,
                    'unit_cost' => $unitCost,
                    'difference_amount' => 0,
                ]);

                $totalSystemQty += $systemQty;
            }

            $opname->update([
                'total_system_qty' => $totalSystemQty,
            ]);

            return $opname->load(['location', 'creator', 'items.product']);
        });
    }

    /**
     * Update physical counts submitted by counting staff / scanner.
     *
     * @param StockOpname $opname
     * @param array $counts Array of ['item_id' => int, 'physical_qty' => int, 'notes' => ?string]
     * @return StockOpname
     */
    public function updatePhysicalCounts(StockOpname $opname, array $counts): StockOpname
    {
        if (!$opname->isEditable()) {
            throw new InvalidArgumentException("Sesi stock opname {$opname->opname_number} tidak dapat diubah karena telah berstatus {$opname->status->label()}.");
        }

        return DB::transaction(function () use ($opname, $counts) {
            $items = $opname->items()->get()->keyBy('id');

            foreach ($counts as $countData) {
                $itemId = $countData['item_id'] ?? null;
                if (!$itemId || !$items->has($itemId)) {
                    continue;
                }

                /** @var StockOpnameItem $item */
                $item = $items->get($itemId);
                
                if (!isset($countData['physical_qty']) || $countData['physical_qty'] === '' || $countData['physical_qty'] === null) {
                    continue;
                }

                $physicalQty = max(0, (int) $countData['physical_qty']);
                $diffQty = $physicalQty - $item->system_qty;
                $diffAmount = round($diffQty * $item->unit_cost, 2);
                $notes = $countData['notes'] ?? $item->notes;

                $item->update([
                    'physical_qty' => $physicalQty,
                    'difference_qty' => $diffQty,
                    'difference_amount' => $diffAmount,
                    'notes' => $notes,
                ]);
            }

            // Recalculate summary totals
            $refreshedItems = $opname->items()->get();
            $totalPhysical = 0;
            $totalVarianceQty = 0;
            $totalVarianceAmount = 0.0;

            foreach ($refreshedItems as $it) {
                if ($it->physical_qty !== null) {
                    $totalPhysical += $it->physical_qty;
                    $totalVarianceQty += $it->difference_qty;
                    $totalVarianceAmount += $it->difference_amount;
                }
            }

            $opname->update([
                'total_physical_qty' => $totalPhysical,
                'total_variance_qty' => $totalVarianceQty,
                'total_variance_amount' => $totalVarianceAmount,
            ]);

            return $opname->fresh(['location', 'creator', 'items.product']);
        });
    }

    /**
     * Finalize and approve stock opname: reconcile discrepancies to immutable ledger.
     */
    public function completeOpname(StockOpname $opname, User $approver): StockOpname
    {
        if (!$opname->isEditable()) {
            throw new InvalidArgumentException("Sesi stock opname {$opname->opname_number} sudah selesai atau dibatalkan sebelumnya.");
        }

        return DB::transaction(function () use ($opname, $approver) {
            $items = $opname->items()->with('product')->get();

            foreach ($items as $item) {
                // If item physical count was not entered, default to system quantity (0 discrepancy)
                if ($item->physical_qty === null) {
                    $item->update([
                        'physical_qty' => $item->system_qty,
                        'difference_qty' => 0,
                        'difference_amount' => 0,
                    ]);
                    continue;
                }

                $diffQty = (int) $item->difference_qty;

                // Only record stock movement if there is a discrepancy
                if ($diffQty !== 0) {
                    $this->inventoryService->recordMovement(
                        product: $item->product,
                        location: $opname->location,
                        quantityInBaseUnit: $diffQty, // Positive for surplus, negative for shortage
                        type: MovementType::STOCK_OPNAME,
                        refType: StockOpname::class,
                        refId: $opname->id,
                        refNumber: $opname->opname_number,
                        notes: "Penyesuaian Opname Fisik #{$opname->opname_number} (Selisih: " . ($diffQty > 0 ? "+{$diffQty}" : "{$diffQty}") . " {$item->product->base_unit_name})" . ($item->notes ? " - {$item->notes}" : ""),
                        actor: $approver
                    );
                }
            }

            // Recalculate summary totals
            $refreshedItems = $opname->items()->get();
            $totalPhysical = $refreshedItems->sum('physical_qty');
            $totalVarianceQty = $refreshedItems->sum('difference_qty');
            $totalVarianceAmount = $refreshedItems->sum('difference_amount');

            $opname->update([
                'status' => OpnameStatus::COMPLETED,
                'total_physical_qty' => $totalPhysical,
                'total_variance_qty' => $totalVarianceQty,
                'total_variance_amount' => $totalVarianceAmount,
                'approved_by' => $approver->id,
                'completed_at' => now(),
            ]);

            return $opname->fresh(['location', 'creator', 'approver', 'items.product', 'movements']);
        });
    }

    /**
     * Cancel an active stock opname session.
     */
    public function cancelOpname(StockOpname $opname, User $actor, string $reason): StockOpname
    {
        if (!$opname->isEditable()) {
            throw new InvalidArgumentException("Sesi stock opname {$opname->opname_number} tidak dapat dibatalkan karena berstatus {$opname->status->label()}.");
        }

        $notes = $opname->notes ? "{$opname->notes} | Dibatalkan: {$reason}" : "Dibatalkan: {$reason}";

        $opname->update([
            'status' => OpnameStatus::CANCELLED,
            'notes' => $notes,
        ]);

        return $opname->fresh();
    }

    /**
     * Generate unique opname document number: OPN/YYYYMMDD/XXXX
     */
    public function generateOpnameNumber(): string
    {
        $prefix = 'OPN/' . now()->format('Ymd') . '/';

        $lastOpname = StockOpname::where('opname_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if (!$lastOpname) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($lastOpname->opname_number, -4);
        $nextSeq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }
}
