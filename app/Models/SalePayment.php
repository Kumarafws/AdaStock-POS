<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount',
        'reference_number',
        'payment_details',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'amount' => 'float',
            'payment_details' => 'array',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isCash(): bool
    {
        return $this->payment_method->isCash();
    }
}
