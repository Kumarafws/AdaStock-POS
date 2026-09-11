<x-layouts.app title="Shift Kasir">
    <x-slot:header>
        Manajemen Shift & Laci Kas
    </x-slot:header>
    <x-slot:subtitle>
        Sesi buka/tutup register kasir, modal awal kas, dan rekonsiliasi kas laci.
    </x-slot:subtitle>

    <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-xs max-w-2xl mx-auto my-8">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 border border-indigo-100">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900">Modul Shift & Cash Register</h3>
        <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
            Fondasi autentikasi dan penugasan toko kasir telah siap. Logika buka kasir (modal awal kas), pencatatan cash in/out, dan rekonsiliasi tutup shift akan diimplementasikan secara bertahap pada <strong>Fase 5</strong>.
        </p>
        <div class="mt-6 flex justify-center gap-3">
            <x-button href="{{ route('dashboard') }}" variant="secondary">
                Kembali ke Dashboard
            </x-button>
        </div>
    </div>
</x-layouts.app>
