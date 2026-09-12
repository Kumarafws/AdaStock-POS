<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case ORDERED = 'ordered';
    case PARTIAL = 'partial';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf Pesanan',
            self::ORDERED => 'Dipesan ke Supplier',
            self::PARTIAL => 'Diterima Sebagian',
            self::RECEIVED => 'Selesai Diterima',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700 border-slate-200',
            self::ORDERED => 'bg-blue-50 text-blue-700 border-blue-200',
            self::PARTIAL => 'bg-amber-50 text-amber-700 border-amber-200',
            self::RECEIVED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public function canReceive(): bool
    {
        return in_array($this, [self::ORDERED, self::PARTIAL], true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::ORDERED], true);
    }
}
