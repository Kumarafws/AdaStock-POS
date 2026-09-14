<?php

namespace App\Enums;

enum SaleStatus: string
{
    case COMPLETED = 'completed';
    case VOIDED = 'voided';
    case RETURNED_PARTIAL = 'returned_partial';
    case RETURNED_FULL = 'returned_full';

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Selesai',
            self::VOIDED => 'Dibatalkan (Void)',
            self::RETURNED_PARTIAL => 'Retur Sebagian',
            self::RETURNED_FULL => 'Retur Total',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::VOIDED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::RETURNED_PARTIAL => 'bg-amber-50 text-amber-700 border-amber-200',
            self::RETURNED_FULL => 'bg-orange-50 text-orange-700 border-orange-200',
        };
    }
}
