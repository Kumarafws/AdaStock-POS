<?php

namespace App\Enums;

enum LocationType: string
{
    case STORE = 'store';
    case WAREHOUSE = 'warehouse';
    case QUARANTINE = 'quarantine';

    public function label(): string
    {
        return match ($this) {
            self::STORE => 'Toko Retail',
            self::WAREHOUSE => 'Gudang Penyimpanan',
            self::QUARANTINE => 'Karantina Barang Rusak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::STORE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::WAREHOUSE => 'bg-blue-50 text-blue-700 border-blue-200',
            self::QUARANTINE => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public function isSellable(): bool
    {
        return $this === self::STORE;
    }
}
