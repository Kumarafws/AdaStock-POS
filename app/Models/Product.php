<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'category_id',
        'brand_id',
        'base_unit_name',
        'purchase_price',
        'default_selling_price',
        'min_stock',
        'reorder_point',
        'image_path',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'default_selling_price' => 'decimal:2',
            'min_stock' => 'integer',
            'reorder_point' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class)->orderBy('conversion_factor', 'asc');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get stock for a specific location or total across all locations.
     */
    public function getStockForLocation(?int $locationId = null): int
    {
        if ($locationId) {
            return (int) $this->inventories()->where('location_id', $locationId)->value('quantity') ?? 0;
        }

        return (int) $this->inventories()->sum('quantity');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhereHas('units', function ($uq) use ($term) {
                  $uq->where('barcode', 'like', "%{$term}%")
                     ->orWhere('unit_name', 'like', "%{$term}%");
              });
        });
    }

    public function scopeCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    public function scopeBrand($query, $brandId)
    {
        if ($brandId) {
            return $query->where('brand_id', $brandId);
        }
        return $query;
    }

    /**
     * Format base unit quantity into secondary pack breakdown (e.g. 1 Dus + 8 Pcs).
     */
    public function formatQuantityBreakdown(int $quantity): string
    {
        $primaryUnit = $this->base_unit_name;
        // Check if there is a secondary unit defined
        $largerUnit = $this->units->sortByDesc('conversion_factor')->first();
        if (!$largerUnit || $largerUnit->conversion_factor <= 1 || $quantity < $largerUnit->conversion_factor) {
            return "{$quantity} {$primaryUnit}";
        }

        $largerQty = intdiv($quantity, $largerUnit->conversion_factor);
        $remainder = $quantity % $largerUnit->conversion_factor;

        if ($remainder === 0) {
            return "{$largerQty} {$largerUnit->unit_name} ({$quantity} {$primaryUnit})";
        }

        return "{$largerQty} {$largerUnit->unit_name} + {$remainder} {$primaryUnit}";
    }
}
