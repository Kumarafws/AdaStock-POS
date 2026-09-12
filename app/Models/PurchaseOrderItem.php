<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_unit_id',
        'unit_name',
        'conversion_factor',
        'ordered_quantity',
        'ordered_quantity_base',
        'received_quantity_base',
        'unit_cost',
        'base_unit_cost',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'integer',
            'ordered_quantity' => 'integer',
            'ordered_quantity_base' => 'integer',
            'received_quantity_base' => 'integer',
            'unit_cost' => 'decimal:2',
            'base_unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function goodsReceiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Sisa kuantitas Base Unit yang belum diterima.
     */
    public function getRemainingQuantityBaseAttribute(): int
    {
        return max(0, $this->ordered_quantity_base - $this->received_quantity_base);
    }

    /**
     * Apakah item sudah terpenuhi seluruhnya.
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity_base >= $this->ordered_quantity_base;
    }
}
