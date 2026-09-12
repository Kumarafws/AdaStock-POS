<?php

namespace App\Http\Controllers;

use App\Enums\ShiftStatus;
use App\Http\Requests\CloseShiftRequest;
use App\Http\Requests\OpenShiftRequest;
use App\Models\CashierShift;
use App\Models\Location;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftService $shiftService
    ) {}

    /**
     * Display a listing of Cashier Shifts.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $isCashier = $user->role->value === 'cashier';

        $query = CashierShift::with(['cashier', 'location', 'closer']);

        if ($isCashier) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->query('user_id'));
            }
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->query('location_id'));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->query('date_to'));
        }

        $shifts = $query->latest('id')->paginate(15)->withQueryString();

        $activeShift = $this->shiftService->getActiveShift($user);

        // Stats summary
        $statsQuery = $isCashier ? CashierShift::where('user_id', $user->id) : CashierShift::query();
        $totalActive = (clone $statsQuery)->where('status', ShiftStatus::OPEN)->count();
        $totalClosed = (clone $statsQuery)->where('status', ShiftStatus::CLOSED)->count();
        $totalSales = (float) (clone $statsQuery)->sum('total_sales_amount');
        $totalDifference = (float) (clone $statsQuery)->where('status', ShiftStatus::CLOSED)->sum('cash_difference');

        $cashiers = User::where('role', 'cashier')->orderBy('name')->get();
        $locations = Location::stores()->active()->orderBy('name')->get();

        return view('shifts.index', [
            'shifts' => $shifts,
            'activeShift' => $activeShift,
            'isCashier' => $isCashier,
            'cashiers' => $cashiers,
            'locations' => $locations,
            'summary' => [
                'total_active' => $totalActive,
                'total_closed' => $totalClosed,
                'total_sales' => $totalSales,
                'total_difference' => $totalDifference,
            ],
        ]);
    }

    /**
     * Show form to open a new cashier shift register.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $activeShift = $this->shiftService->getActiveShift($user);

        if ($activeShift) {
            return redirect()
                ->route('shifts.show', $activeShift)
                ->with('info', "Anda masih memiliki shift aktif ({$activeShift->shift_number}).");
        }

        $store = $user->assignedStore ?? Location::stores()->first();

        return view('shifts.create', [
            'store' => $store,
        ]);
    }

    /**
     * Open a new cashier shift register.
     */
    public function store(OpenShiftRequest $request): RedirectResponse
    {
        $user = $request->user();
        $store = $user->assignedStore ?? Location::stores()->first();

        if (!$store) {
            return redirect()->back()->with('error', 'Toko ritel belum dikonfigurasi dalam sistem.');
        }

        try {
            $shift = $this->shiftService->openShift(
                cashier: $user,
                store: $store,
                startingCash: (float) $request->input('starting_cash'),
                notes: $request->input('notes')
            );

            return redirect()
                ->route('pos.index')
                ->with('success', "Register Shift {$shift->shift_number} berhasil dibuka dengan modal awal Rp " . number_format($shift->starting_cash, 0, ',', '.') . ". Selamat bertugas!");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display cashier shift details & X/Z-Report summary.
     */
    public function show(Request $request, CashierShift $shift): View
    {
        $user = $request->user();

        // Cashier can only view their own shifts
        if ($user->role->value === 'cashier' && $shift->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki otorisasi untuk melihat shift kasir lain.');
        }

        $shift->load(['cashier', 'location', 'closer']);

        return view('shifts.show', [
            'shift' => $shift,
        ]);
    }

    /**
     * Show form to close register and perform cash reconciliation.
     */
    public function closeForm(Request $request, CashierShift $shift): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role->value === 'cashier' && $shift->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menutup shift ini.');
        }

        if ($shift->isClosed()) {
            return redirect()
                ->route('shifts.show', $shift)
                ->with('info', "Shift {$shift->shift_number} sudah ditutup sebelumnya.");
        }

        return view('shifts.close', [
            'shift' => $shift,
        ]);
    }

    /**
     * Close the register and reconcile physical cash.
     */
    public function close(CloseShiftRequest $request, CashierShift $shift): RedirectResponse
    {
        $user = $request->user();

        if ($user->role->value === 'cashier' && $shift->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menutup shift ini.');
        }

        try {
            $closedShift = $this->shiftService->closeShift(
                shift: $shift,
                actualCash: (float) $request->input('actual_ending_cash'),
                notes: $request->input('notes'),
                closer: $user
            );

            return redirect()
                ->route('shifts.show', $closedShift)
                ->with('success', "Shift {$closedShift->shift_number} berhasil ditutup. Laporan ringkasan kas (Z-Report) telah dicetak.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
