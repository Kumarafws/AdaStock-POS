<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index(Request $request): View
    {
        $query = User::with('assignedStore');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderBy('name')->get();
        $locations = Location::active()->orderBy('type')->orderBy('name')->get();
        $roles = UserRole::cases();

        return view('admin.users.index', compact('users', 'locations', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'assigned_store_id' => ['nullable', 'exists:locations,id'],
            'supervisor_pin' => ['nullable', 'string', 'min:4', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip (-), dan garis bawah (_).',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'supervisor_pin.min' => 'PIN Supervisor minimal 4 karakter/digit.',
        ]);

        if (empty($validated['supervisor_pin'])) {
            unset($validated['supervisor_pin']);
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$validated['name']} berhasil didaftarkan ke sistem.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'assigned_store_id' => ['nullable', 'exists:locations,id'],
            'supervisor_pin' => ['nullable', 'string', 'min:4', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip (-), dan garis bawah (_).',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'supervisor_pin.min' => 'PIN Supervisor minimal 4 karakter/digit.',
        ]);

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Only update supervisor_pin if provided
        if (array_key_exists('supervisor_pin', $validated) && empty($validated['supervisor_pin'])) {
            unset($validated['supervisor_pin']);
        }

        // Prevent admin from deactivating or demoting their own current account
        if ($user->id === auth()->id()) {
            if ($validated['status'] === 'inactive') {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan.');
            }
            if ($validated['role'] !== UserRole::ADMIN->value) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Anda tidak dapat mencabut peran Administrator pada akun Anda sendiri.');
            }
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $userName = $user->name;
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', "Pengguna {$userName} berhasil dihapus dari sistem.");
        } catch (QueryException) {
            // Gracefully deactivate account if user has historical audit records (sales, void logs, shifts)
            $user->update(['status' => 'inactive']);

            return redirect()->route('admin.users.index')
                ->with('info', "Pengguna {$user->name} memiliki riwayat transaksi/audit sehingga status akun dinonaktifkan (bukan dihapus permanen).");
        }
    }
}
