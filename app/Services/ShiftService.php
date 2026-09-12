<?php

namespace App\Services;

use App\Enums\ShiftStatus;
use App\Models\CashierShift;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShiftService
{
    /**
     * Open a new cashier shift register atomically.
     *
     * @throws InvalidArgumentException
     */
    public function openShift(User $cashier, Location $store, float $startingCash, ?string $notes = null): CashierShift
    {
        return DB::transaction(function () use ($cashier, $store, $startingCash, $notes) {
            // Invariant: Cashier cannot have concurrent active open shifts
            $activeShift = $this->getActiveShift($cashier);
            if ($activeShift) {
                throw new InvalidArgumentException(
                    "Kasir {$cashier->name} masih memiliki shift aktif yang belum ditutup (#{$activeShift->shift_number}). Harap tutup shift tersebut terlebih dahulu."
                );
            }

            if ($startingCash < 0) {
                throw new InvalidArgumentException('Modal awal laci kasir tidak boleh negatif.');
            }

            $shiftNumber = $this->generateShiftNumber();

            return CashierShift::create([
                'shift_number' => $shiftNumber,
                'user_id' => $cashier->id,
                'location_id' => $store->id,
                'opened_at' => now(),
                'starting_cash' => $startingCash,
                'expected_ending_cash' => $startingCash,
                'actual_ending_cash' => null,
                'cash_difference' => null,
                'total_sales_cash' => 0,
                'total_sales_non_cash' => 0,
                'total_sales_amount' => 0,
                'total_transactions_count' => 0,
                'status' => ShiftStatus::OPEN,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Close an active cashier shift and reconcile physical cash.
     *
     * @throws InvalidArgumentException
     */
    public function closeShift(CashierShift $shift, float $actualCash, ?string $notes = null, ?User $closer = null): CashierShift
    {
        $closerUser = $closer ?? $shift->cashier;

        return DB::transaction(function () use ($shift, $actualCash, $notes, $closerUser) {
            if ($shift->isClosed()) {
                throw new InvalidArgumentException("Shift {$shift->shift_number} sudah ditutup sebelumnya.");
            }

            if ($actualCash < 0) {
                throw new InvalidArgumentException('Jumlah uang fisik aktual di laci kas tidak boleh negatif.');
            }

            // Expected ending cash = starting cash + total cash sales
            $expectedEndingCash = (float) $shift->starting_cash + (float) $shift->total_sales_cash;
            $cashDifference = $actualCash - $expectedEndingCash;

            $shift->closed_at = now();
            $shift->actual_ending_cash = $actualCash;
            $shift->expected_ending_cash = $expectedEndingCash;
            $shift->cash_difference = $cashDifference;
            $shift->status = ShiftStatus::CLOSED;
            $shift->closed_by = $closerUser?->id;

            if ($notes) {
                $shift->notes = trim(($shift->notes ?? '') . "\n[Catatan Penutupan: {$notes}]");
            }

            $shift->save();

            return $shift;
        });
    }

    /**
     * Get the active open shift for a cashier, if any.
     */
    public function getActiveShift(User $cashier): ?CashierShift
    {
        return CashierShift::where('user_id', $cashier->id)
            ->where('status', ShiftStatus::OPEN)
            ->latest('id')
            ->first();
    }

    /**
     * Generate unique shift document number: SFT/YYYYMMDD/0001
     */
    public function generateShiftNumber(): string
    {
        $prefix = 'SFT/' . now()->format('Ymd') . '/';

        $lastNumber = CashierShift::where('shift_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('shift_number');

        if ($lastNumber) {
            $parts = explode('/', $lastNumber);
            $lastSeq = (int) end($parts);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
