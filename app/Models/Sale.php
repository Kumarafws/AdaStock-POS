<?php

namespace App\Models;

use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_number',
        'cashier_shift_id',
        'user_id',
        'location_id',
        'customer_name',
        'transaction_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'rounding_amount',
        'total_amount',
        'total_cogs',
        'total_profit',
        'paid_amount',
        'change_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'subtotal' => 'float',
            'discount_amount' => 'float',
            'tax_amount' => 'float',
            'rounding_amount' => 'float',
            'total_amount' => 'float',
            'total_cogs' => 'float',
            'total_profit' => 'float',
            'paid_amount' => 'float',
            'change_amount' => 'float',
            'status' => SaleStatus::class,
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class, 'cashier_shift_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === SaleStatus::COMPLETED;
    }

    public function isVoided(): bool
    {
        return $this->status === SaleStatus::VOIDED;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getTotalItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
