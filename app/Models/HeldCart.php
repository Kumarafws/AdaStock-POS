<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'cashier_shift_id',
        'user_id',
        'customer_name',
        'cart_items',
        'discount_amount',
        'notes',
        'held_at',
    ];

    protected function casts(): array
    {
        return [
            'cart_items' => 'array',
            'discount_amount' => 'float',
            'held_at' => 'datetime',
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

    public function getTotalItemsAttribute(): int
    {
        if (empty($this->cart_items) || !is_array($this->cart_items)) {
            return 0;
        }

        return (int) array_sum(array_column($this->cart_items, 'quantity'));
    }

    public function getEstimatedTotalAttribute(): float
    {
        if (empty($this->cart_items) || !is_array($this->cart_items)) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($this->cart_items as $item) {
            $sum += (float) ($item['subtotal'] ?? 0);
        }

        return max(0.0, $sum - (float) $this->discount_amount);
    }
}
