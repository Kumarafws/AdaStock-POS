<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class AuthorizationService
{
    /**
     * Threshold constants for cashier discount tolerance.
     */
    public const MAX_UNAUTHORIZED_DISCOUNT_PERCENT = 0.05; // 5%
    public const MAX_UNAUTHORIZED_DISCOUNT_NOMINAL = 20000; // Rp 20.000

    /**
     * Verify supervisor PIN against active managers and administrators.
     *
     * @throws InvalidArgumentException
     */
    public function verifySupervisorPin(string $pin): User
    {
        $cleanPin = trim($pin);

        if (empty($cleanPin)) {
            throw new InvalidArgumentException('PIN otorisasi supervisor wajib diisi.');
        }

        // Get all active supervisors (Admin & Manager) with configured PIN
        $supervisors = User::whereIn('role', [UserRole::ADMIN, UserRole::MANAGER])
            ->where('status', 'active')
            ->whereNotNull('supervisor_pin')
            ->get();

        foreach ($supervisors as $supervisor) {
            if (Hash::check($cleanPin, $supervisor->supervisor_pin)) {
                return $supervisor;
            }
        }

        throw new InvalidArgumentException('PIN Supervisor tidak valid atau akun supervisor tidak aktif.');
    }

    /**
     * Check if a manual discount exceeds cashier tolerance threshold.
     * Rule: Max discount without supervisor is 5% of subtotal OR Rp 20.000.
     */
    public function isDiscountOverThreshold(float $subtotal, float $discountAmount): bool
    {
        if ($discountAmount <= 0) {
            return false;
        }

        $percentLimit = $subtotal * self::MAX_UNAUTHORIZED_DISCOUNT_PERCENT;
        $maxAllowed = min($percentLimit, self::MAX_UNAUTHORIZED_DISCOUNT_NOMINAL);

        return $discountAmount > $maxAllowed;
    }
}
