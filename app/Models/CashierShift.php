<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_number',
        'user_id',
        'location_id',
        'opened_at',
        'closed_at',
        'starting_cash',
        'expected_ending_cash',
        'actual_ending_cash',
        'cash_difference',
        'total_sales_cash',
        'total_sales_non_cash',
        'total_sales_amount',
        'total_transactions_count',
        'status',
        'notes',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'status' => ShiftStatus::class,
            'starting_cash' => 'decimal:2',
            'expected_ending_cash' => 'decimal:2',
            'actual_ending_cash' => 'decimal:2',
            'cash_difference' => 'decimal:2',
            'total_sales_cash' => 'decimal:2',
            'total_sales_non_cash' => 'decimal:2',
            'total_sales_amount' => 'decimal:2',
            'total_transactions_count' => 'integer',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function heldCarts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HeldCart::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', ShiftStatus::OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', ShiftStatus::CLOSED);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === ShiftStatus::CLOSED;
    }

    /**
     * Human-readable shift duration.
     */
    public function getDurationAttribute(): string
    {
        $endTime = $this->closed_at ?? now();
        $diff = $this->opened_at->diff($endTime);

        if ($diff->d > 0) {
            return "{$diff->d} hari {$diff->h} jam";
        }
        if ($diff->h > 0) {
            return "{$diff->h} jam {$diff->i} mnt";
        }
        return "{$diff->i} menit";
    }
}
