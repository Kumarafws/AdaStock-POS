<x-layouts.app title="Tutup Shift Kasir" header="Tutup Shift Register">
    <div class="max-w-xl mx-auto space-y-6" 
         x-data="{
             expectedCash: {{ (float)$shift->starting_cash + (float)$shift->total_sales_cash }},
             actualCash: {{ old('actual_ending_cash', (float)$shift->starting_cash + (float)$shift->total_sales_cash) }},

             get difference() {
                 const act = parseFloat(this.actualCash) || 0;
                 return act - this.expectedCash;
             }
         }">

        <!-- Top Back & Header -->
        <div class="flex items-center gap-3">
            <a href="{{ route('shifts.show', $shift) }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Tutup Register & Hitung Uang Laci</h2>
                <p class="text-xs text-slate-500">Hitung seluruh uang fisik di laci kasir untuk rekonsiliasi akhir (*Blind Count*).</p>
            </div>
        </div>

        <form method="POST" action="{{ route('shifts.close', $shift) }}" class="space-y-6">
            @csrf

            <!-- Shift Info Card -->
            <x-card class="p-5 bg-slate-50 border-slate-200 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Nomor Shift:</span>
                    <span class="font-mono font-bold text-slate-900">{{ $shift->shift_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kasir:</span>
                    <span class="font-bold text-slate-900">{{ $shift->cashier->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu Buka:</span>
                    <span class="font-medium text-slate-700">{{ $shift->opened_at->format('d/m/Y H:i') }} WIB ({{ $shift->duration }})</span>
                </div>
            </x-card>

            <!-- Reconciliation Card -->
            <x-card class="p-6 space-y-5">
                <div class="space-y-2 text-xs border-b border-slate-100 pb-3">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Modal Kas Awal:</span>
                        <span class="font-bold tabular-nums">Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">(+) Penjualan Tunai:</span>
                        <span class="font-bold tabular-nums text-emerald-600">+ Rp {{ number_format($shift->total_sales_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-1 border-t border-slate-100">
                        <span>Ekspektasi Kas di Laci:</span>
                        <span class="tabular-nums" x-text="'Rp ' + Number(expectedCash).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Actual Ending Cash Input -->
                <div>
                    <label for="actual_ending_cash" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jumlah Uang Tunai Fisik di Laci <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-sm font-bold text-slate-400 pointer-events-none">
                            Rp
                        </span>
                        <input type="number" 
                               name="actual_ending_cash" 
                               id="actual_ending_cash" 
                               x-model="actualCash"
                               min="0" 
                               step="100"
                               class="w-full rounded-xl border-slate-300 pl-11 pr-4 py-3 text-lg font-black text-slate-900 tabular-nums focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    @error('actual_ending_cash')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Real-time Variance Simulation Card -->
                <div class="p-4 rounded-xl transition-all"
                     :class="difference == 0 ? 'bg-slate-100 border border-slate-200' : (difference > 0 ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-rose-50 border border-rose-200 text-rose-900')">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold">Selisih Kas (Variance):</span>
                        <span class="font-extrabold text-base tabular-nums"
                              x-text="difference == 0 ? 'Rp 0 (Pas)' : (difference > 0 ? '+ Rp ' + Number(difference).toLocaleString('id-ID') + ' (Lebih)' : '- Rp ' + Number(Math.abs(difference)).toLocaleString('id-ID') + ' (Kurang)')">
                        </span>
                    </div>
                    <p class="text-[11px] mt-1 opacity-80" x-show="difference != 0">
                        Harap berikan keterangan pada kolom catatan jika terdapat selisih uang kas.
                    </p>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Keterangan Rekonsiliasi / Catatan Penutupan
                    </label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Penyebab selisih kas (jika ada), kondisi setoran uang, atau serah terima shift..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">{{ old('notes') }}</textarea>
                </div>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('shifts.show', $shift) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 text-white font-semibold text-sm hover:bg-rose-700 shadow-sm shadow-rose-600/20 transition-colors">
                    Konfirmasi & Tutup Register Shift
                </button>
            </div>

        </form>

    </div>
</x-layouts.app>
