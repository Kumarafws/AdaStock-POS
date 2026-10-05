<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Http\Requests\LocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Display a listing of stores and warehouses.
     */
    public function index(Request $request): View
    {
        $query = Location::query()->withCount(['users', 'inventories']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $locations = $query->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $types = LocationType::cases();

        return view('locations.index', compact('locations', 'types'));
    }

    /**
     * Store a newly created location (Admin only).
     */
    public function store(LocationRequest $request): RedirectResponse
    {
        Location::create([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'type' => $request->type,
            'address' => $request->filled('address') ? trim($request->address) : null,
            'phone' => $request->filled('phone') ? trim($request->phone) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('locations.index')
            ->with('success', 'Toko / Gudang baru berhasil ditambahkan.');
    }

    /**
     * Update the specified location (Admin only).
     */
    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $location->update([
            'code' => strtoupper(trim($request->code)),
            'name' => trim($request->name),
            'type' => $request->type,
            'address' => $request->filled('address') ? trim($request->address) : null,
            'phone' => $request->filled('phone') ? trim($request->phone) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('locations.index')
            ->with('success', 'Data Toko / Gudang berhasil diperbarui.');
    }

    /**
     * Remove the specified location from storage (Admin only).
     */
    public function destroy(Location $location): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Administrator yang memiliki akses menghapus lokasi.');
        }

        // Prevent deletion if location has active relations or stock movements
        $hasUsers = $location->users()->exists();
        $hasInventories = $location->inventories()->exists();
        $hasMovements = $location->stockMovements()->exists();

        if ($hasUsers || $hasInventories || $hasMovements) {
            return redirect()->route('locations.index')
                ->with('error', "Lokasi '{$location->name}' tidak dapat dihapus permanen karena memiliki data terkait (inventaris/staf/mutasi stok). Anda dapat menonaktifkannya melalui tombol Edit.");
        }

        $locationName = $location->name;
        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', "Lokasi '{$locationName}' berhasil dihapus.");
    }
}
