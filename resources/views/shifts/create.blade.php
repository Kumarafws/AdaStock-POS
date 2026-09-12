<x-layouts.app title="Buka Shift Kasir" header="Buka Shift Kasir">
    <div class="max-w-xl mx-auto space-y-6" x-data="{ startingCash: {{ old('starting_cash', 200000) }} }">

        <!-- Top Back & Header -->
        <div class="flex items-center gap-3">
            <a href="{{ route('shifts.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Buka Register Kasir Baru</h2>
                <p class="text-xs text-slate-500">Masukkan modal kas awal di laci kasir (*starting float*) sebelum memulai penjualan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('shifts.store') }}" class="space-y-6">
            @csrf

            <!-- Cashier & Store Card -->
            <x-card class="p-6 space-y-4 bg-gradient-to-br from-indigo-50/50 to-slate-50 border-indigo-100">
                <div class="flex items-center justify-between border-b border-indigo-100 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kasir Bertugas:</span>
                    <span class="text-sm font-extrabold text-slate-900">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Lokasi Toko:</span>
                    <span class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $store->name }} ({{ $store->code }})
                    </span>
                </div>
            </x-card>

            <!-- Starting Cash Input -->
            <x-card class="p-6 space-y-4">
                <div>
                    <label for="starting_cash" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Modal Kas Awal di Laci (Uang Kembalian) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-sm font-bold text-slate-400 pointer-events-none">
                            Rp
                        </span>
                        <input type="number" 
                               name="starting_cash" 
                               id="starting_cash" 
                               x-model="startingCash"
                               min="0" 
                               step="1000"
                               class="w-full rounded-xl border-slate-300 pl-11 pr-4 py-3 text-lg font-black text-slate-900 tabular-nums focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    @error('starting_cash')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quick Fill Buttons -->
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pilihan Cepat Nominal:</span>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" 
                                @click="startingCash = 100000" 
                                class="py-2 px-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 text-xs font-bold tabular-nums transition-colors">
                            100 Rb
                        </button>
                        <button type="button" 
                                @click="startingCash = 200000" 
                                class="py-2 px-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 text-xs font-bold tabular-nums transition-colors">
                            200 Rb
                        </button>
                        <button type="button" 
                                @click="startingCash = 300000" 
                                class="py-2 px-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 text-xs font-bold tabular-nums transition-colors">
                            300 Rb
                        </button>
                        <button type="button" 
                                @click="startingCash = 500000" 
                                class="py-2 px-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 text-xs font-bold tabular-nums transition-colors">
                            500 Rb
                        </button>
                    </div>
                </div>

                <!-- Notes -->
                <div class="pt-2">
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Catatan pecahan uang kembalian atau kondisi mesin kasir..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">{{ old('notes') }}</textarea>
                </div>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('shifts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                    Buka Shift & Masuk POS
                </button>
            </div>

        </form>

    </div>
</x-layouts.app>
