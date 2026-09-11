<?php

namespace App\Models;

use App\Enums\AdjustmentReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'location_id',
        'product_id',
        'type',
        'quantity',
        'reason',
        'action_type',
        'cogs_at_time',
        'notes',
        'adjusted_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reason' => AdjustmentReason::class,
            'cogs_at_time' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
