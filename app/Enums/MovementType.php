<?php

namespace App\Enums;

enum MovementType: string
{
    case PURCHASE_RECEIPT = 'purchase_receipt';
    case SALE = 'sale';
    case SALE_VOID = 'sale_void';
    case SALE_RETURN = 'sale_return';
    case PURCHASE_RETURN = 'purchase_return';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';
    case STOCK_OPNAME = 'stock_opname';
    case LOSS_DISPOSAL = 'loss_disposal';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE_RECEIPT => 'Penerimaan Pembelian (PO)',
            self::SALE => 'Penjualan Kasir (POS)',
            self::SALE_VOID => 'Pembatalan Transaksi (Void)',
            self::SALE_RETURN => 'Retur Penjualan Pelanggan',
            self::PURCHASE_RETURN => 'Retur Pembelian ke Supplier',
            self::TRANSFER_IN => 'Transfer Masuk Lokasi',
            self::TRANSFER_OUT => 'Transfer Keluar Lokasi',
            self::ADJUSTMENT_IN => 'Penyesuaian Masuk (+)',
            self::ADJUSTMENT_OUT => 'Penyesuaian Keluar (-)',
            self::STOCK_OPNAME => 'Selisih Stock Opname',
            self::LOSS_DISPOSAL => 'Pemusnahan Barang Rusak',
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this, [
            self::PURCHASE_RECEIPT,
            self::SALE_VOID,
            self::SALE_RETURN,
            self::TRANSFER_IN,
            self::ADJUSTMENT_IN,
        ], true);
    }

    public function isOutgoing(): bool
    {
        return in_array($this, [
            self::SALE,
            self::PURCHASE_RETURN,
            self::TRANSFER_OUT,
            self::ADJUSTMENT_OUT,
            self::LOSS_DISPOSAL,
        ], true);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PURCHASE_RECEIPT, self::TRANSFER_IN, self::ADJUSTMENT_IN => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::SALE => 'bg-blue-50 text-blue-700 border-blue-200',
            self::SALE_VOID, self::SALE_RETURN => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::PURCHASE_RETURN, self::TRANSFER_OUT, self::ADJUSTMENT_OUT => 'bg-amber-50 text-amber-700 border-amber-200',
            self::LOSS_DISPOSAL => 'bg-rose-50 text-rose-700 border-rose-200',
            self::STOCK_OPNAME => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }
}
