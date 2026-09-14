<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case DEBIT_CARD = 'debit_card';
    case CREDIT_CARD = 'credit_card';
    case QRIS = 'qris';
    case BANK_TRANSFER = 'bank_transfer';
    case E_WALLET = 'e_wallet';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai (Cash)',
            self::DEBIT_CARD => 'Kartu Debit',
            self::CREDIT_CARD => 'Kartu Kredit',
            self::QRIS => 'QRIS',
            self::BANK_TRANSFER => 'Transfer Bank',
            self::E_WALLET => 'E-Wallet',
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::CASH => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::DEBIT_CARD, self::CREDIT_CARD => 'bg-blue-50 text-blue-700 border-blue-200',
            self::QRIS => 'bg-amber-50 text-amber-700 border-amber-200',
            self::BANK_TRANSFER => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::E_WALLET => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }
}
