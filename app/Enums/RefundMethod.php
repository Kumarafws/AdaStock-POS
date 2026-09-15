<?php

namespace App\Enums;

enum RefundMethod: string
{
    case CASH = 'cash';
    case STORE_CREDIT = 'store_credit';
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai (Kas Kasir)',
            self::STORE_CREDIT => 'Kredit Toko / Voucher',
            self::BANK_TRANSFER => 'Transfer Bank',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CASH => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STORE_CREDIT => 'bg-purple-50 text-purple-700 border-purple-200',
            self::BANK_TRANSFER => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }
}
