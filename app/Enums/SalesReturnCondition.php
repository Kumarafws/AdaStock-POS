<?php

namespace App\Enums;

enum SalesReturnCondition: string
{
    case GOOD = 'good';
    case DAMAGED = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::GOOD => 'Bagus / Layak Jual',
            self::DAMAGED => 'Rusak / Karantina',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::GOOD => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::DAMAGED => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public function isDamaged(): bool
    {
        return $this === self::DAMAGED;
    }
}
