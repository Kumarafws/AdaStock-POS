<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Store Manager',
            self::CASHIER => 'Kasir (POS)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-indigo-50 text-indigo-700 ring-indigo-700/10 border-indigo-200',
            self::MANAGER => 'bg-amber-50 text-amber-700 ring-amber-600/10 border-amber-200',
            self::CASHIER => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10 border-emerald-200',
        };
    }

    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }

    public function canApproveOperations(): bool
    {
        return in_array($this, [self::ADMIN, self::MANAGER]);
    }
}
