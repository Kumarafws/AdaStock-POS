<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $greeting = match ($user->role->value) {
            'admin' => "Selamat datang kembali, Administrator {$user->name}!",
            'manager' => "Selamat bertugas, Manager {$user->name}!",
            'cashier' => "Selamat bertugas, {$user->name}! Silakan buka shift kasir sebelum memulai transaksi.",
            default => "Selamat datang, {$user->name}!",
        };

        return redirect()->intended(route('dashboard'))
            ->with('success', $greeting);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil logout dari sistem.');
    }
}
