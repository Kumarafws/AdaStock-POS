<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_qty',
        'physical_qty',
        'difference_qty',
        'unit_cost',
        'difference_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_qty' => 'integer',
            'physical_qty' => 'integer',
            'difference_qty' => 'integer',
            'unit_cost' => 'float',
            'difference_amount' => 'float',
        ];
    }

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isCounted(): bool
    {
        return $this->physical_qty !== null;
    }

    public function isMatch(): bool
    {
        return $this->difference_qty === 0;
    }

    public function isSurplus(): bool
    {
        return $this->difference_qty > 0;
    }

    public function isDeficit(): bool
    {
        return $this->difference_qty < 0;
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_cost, 0, ',', '.');
    }

    public function getFormattedDifferenceAmountAttribute(): string
    {
        $prefix = $this->difference_amount > 0 ? '+Rp ' : ($this->difference_amount < 0 ? '-Rp ' : 'Rp ');
        return $prefix . number_format(abs($this->difference_amount), 0, ',', '.');
    }
}
