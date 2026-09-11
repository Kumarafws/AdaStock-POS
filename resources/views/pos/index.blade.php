<x-layouts.app title="Layar Kasir (POS)">
    <x-slot:header>
        Layar Kasir (Point of Sale)
    </x-slot:header>
    <x-slot:subtitle>
        Terminal checkout kasir terintegrasi stok toko real-time.
    </x-slot:subtitle>

    <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-xs max-w-2xl mx-auto my-8">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 border border-emerald-100">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900">Modul POS Kasir</h3>
        <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
            Fondasi autentikasi, role, dan lokasi toko telah aktif. Layar interaktif kasir dengan scan barcode, multi-satuan, dan split payment akan dibangun secara bertahap pada <strong>Fase 6</strong>.
        </p>
        <div class="mt-6 flex justify-center gap-3">
            <x-button href="{{ route('dashboard') }}" variant="secondary">
                Kembali ke Dashboard
            </x-button>
        </div>
    </div>
</x-layouts.app>
