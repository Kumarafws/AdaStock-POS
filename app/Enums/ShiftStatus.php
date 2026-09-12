<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Shift Aktif (Buka)',
            self::CLOSED => 'Selesai (Tutup)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::OPEN => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CLOSED => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }
}
