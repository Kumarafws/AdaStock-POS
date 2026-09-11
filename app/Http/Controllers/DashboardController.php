<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $stats = [
            'stores_count' => Location::where('type', LocationType::STORE)->count(),
            'warehouses_count' => Location::where('type', LocationType::WAREHOUSE)->count(),
            'quarantine_count' => Location::where('type', LocationType::QUARANTINE)->count(),
            'active_users_count' => User::where('status', 'active')->count(),
        ];

        $assignedStore = $user->assignedStore;
        $stores = Location::where('type', LocationType::STORE)->where('is_active', true)->get();

        return view('dashboard.index', compact('user', 'stats', 'assignedStore', 'stores'));
    }
}
