<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_unit_id',
        'unit_name',
        'conversion_factor',
        'quantity',
        'quantity_base',
        'unit_price',
        'base_unit_price',
        'unit_cost',
        'base_unit_cost',
        'discount_amount',
        'subtotal',
        'cogs_total',
        'gross_profit',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'integer',
            'quantity' => 'integer',
            'quantity_base' => 'integer',
            'unit_price' => 'float',
            'base_unit_price' => 'float',
            'unit_cost' => 'float',
            'base_unit_cost' => 'float',
            'discount_amount' => 'float',
            'subtotal' => 'float',
            'cogs_total' => 'float',
            'gross_profit' => 'float',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}
