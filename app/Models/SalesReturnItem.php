<?php

namespace App\Models;

use App\Enums\SalesReturnCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sale_item_id',
        'product_id',
        'unit_name',
        'conversion_factor',
        'quantity',
        'quantity_base',
        'unit_price',
        'refund_amount',
        'condition',
        'destination_location_id',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'integer',
            'quantity' => 'integer',
            'quantity_base' => 'integer',
            'unit_price' => 'float',
            'refund_amount' => 'float',
            'condition' => SalesReturnCondition::class,
        ];
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedRefundAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->refund_amount, 0, ',', '.');
    }
}
