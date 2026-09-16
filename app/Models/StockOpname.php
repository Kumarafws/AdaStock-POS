<?php

namespace App\Models;

use App\Enums\OpnameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'location_id',
        'category_id',
        'opname_date',
        'status',
        'total_system_qty',
        'total_physical_qty',
        'total_variance_qty',
        'total_variance_amount',
        'notes',
        'created_by',
        'approved_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'opname_date' => 'date',
            'status' => OpnameStatus::class,
            'total_system_qty' => 'integer',
            'total_physical_qty' => 'integer',
            'total_variance_qty' => 'integer',
            'total_variance_amount' => 'float',
            'completed_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isCancelled(): bool
    {
        return $this->status->isCancelled();
    }

    public function getFormattedVarianceAmountAttribute(): string
    {
        $prefix = $this->total_variance_amount > 0 ? '+Rp ' : ($this->total_variance_amount < 0 ? '-Rp ' : 'Rp ');
        return $prefix . number_format(abs($this->total_variance_amount), 0, ',', '.');
    }

    public function hasDiscrepancy(): bool
    {
        return $this->total_variance_qty !== 0;
    }
}
