<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin-Only Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', function () {
            return view('admin.users.index');
        })->name('users.index');
    });

    // Admin & Manager Routes
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/locations', function () {
            $locations = \App\Models\Location::withCount('users')->get();
            return view('locations.index', compact('locations'));
        })->name('locations.index');
    });

    // Cashier Routes (POS & Shift)
    Route::middleware('role:cashier,admin,manager')->group(function () {
        Route::get('/pos', function () {
            return view('pos.index');
        })->name('pos.index');

        Route::get('/shifts', function () {
            return view('shifts.index');
        })->name('shifts.index');
    });
});
