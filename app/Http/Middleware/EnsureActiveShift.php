<?php

namespace App\Http\Middleware;

use App\Services\ShiftService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveShift
{
    public function __construct(
        protected ShiftService $shiftService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role->value === 'cashier') {
            $activeShift = $this->shiftService->getActiveShift($user);

            if (!$activeShift) {
                return redirect()
                    ->route('shifts.create')
                    ->with('warning', 'Silakan buka register shift kasir terlebih dahulu sebelum mengakses layar POS.');
            }
        }

        return $next($request);
    }
}
