<?php

namespace App\Enums;

enum AdjustmentReason: string
{
    case DAMAGE = 'damage';
    case EXPIRY = 'expiry';
    case LOSS = 'loss';
    case CORRECTION = 'correction';
    case FOUND = 'found';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DAMAGE => 'Barang Rusak / Pecah / Cacat',
            self::EXPIRY => 'Barang Kadaluarsa / Expired',
            self::LOSS => 'Barang Hilang / Selisih Kurang',
            self::CORRECTION => 'Koreksi Kesalahan Input',
            self::FOUND => 'Barang Ditemukan / Selisih Lebih',
            self::OTHER => 'Alasan Lainnya',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DAMAGE, self::EXPIRY => 'bg-rose-50 text-rose-700 border-rose-200',
            self::LOSS => 'bg-amber-50 text-amber-700 border-amber-200',
            self::CORRECTION, self::OTHER => 'bg-slate-100 text-slate-700 border-slate-200',
            self::FOUND => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }
}
