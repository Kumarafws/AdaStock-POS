<x-layouts.app :title="$shift->shift_number" header="Laporan Register Kasir">
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Top Header & Action Bar -->
        <div class="flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <a href="{{ route('shifts.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-bold text-slate-900 font-mono tracking-tight">{{ $shift->shift_number }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $shift->status->badgeClass() }}">
                            {{ $shift->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $shift->isClosed() ? 'Laporan Penutupan Register (Z-Report)' : 'Laporan Berjalan Kasir (X-Report)' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="window.print()" 
                        class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors shadow-2xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5z" />
                    </svg>
                    Cetak
                </button>

                @if($shift->isOpen())
                    <a href="{{ route('shifts.close.form', $shift) }}" 
                       class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors shadow-xs">
                        Tutup Shift & Kas Laci
                    </a>
                @endif
            </div>
        </div>

        <!-- Printable Register Slip Card -->
        <x-card class="p-8 font-sans bg-white border border-slate-200 print:shadow-none print:border-none print:p-0">
            
            <!-- Slip Header -->
            <div class="text-center border-b border-slate-200 pb-5 mb-5 space-y-1">
                <h1 class="text-lg font-black tracking-tight text-slate-900 uppercase">AdaStock POS</h1>
                <p class="text-xs text-slate-600 font-bold">{{ $shift->location->name }}</p>
                <p class="text-[11px] text-slate-400">{{ $shift->location->address }}</p>
                <div class="pt-2">
                    <span class="inline-block px-3 py-1 rounded text-xs font-extrabold font-mono tracking-wider bg-slate-100 text-slate-800">
                        {{ $shift->isClosed() ? 'Z-REPORT (PENUTUPAN SHIFT)' : 'X-REPORT (SHIFT BERJALAN)' }}
                    </span>
                </div>
            </div>

            <!-- Meta Data Grid -->
            <div class="space-y-2 text-xs border-b border-slate-200 pb-4 mb-5 text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-400">Nomor Register:</span>
                    <span class="font-mono font-bold">{{ $shift->shift_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Nama Kasir:</span>
                    <span class="font-bold text-slate-900">{{ $shift->cashier->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Waktu Buka Shift:</span>
                    <span class="font-medium">{{ $shift->opened_at->format('d/m/Y H:i:s') }} WIB</span>
                </div>
                @if($shift->closed_at)
                    <div class="flex justify-between">
                        <span class="text-slate-400">Waktu Tutup Shift:</span>
                        <span class="font-medium">{{ $shift->closed_at->format('d/m/Y H:i:s') }} WIB</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-400">Total Durasi:</span>
                    <span class="font-semibold">{{ $shift->duration }}</span>
                </div>
            </div>

            <!-- Sales Summary Section -->
            <div class="space-y-3 border-b border-slate-200 pb-4 mb-5">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900">Ringkasan Penjualan</h4>
                
                <div class="space-y-2 text-xs text-slate-700">
                    <div class="flex justify-between">
                        <span>Total Transaksi / Struk:</span>
                        <span class="font-bold tabular-nums">{{ $shift->total_transactions_count }} Struk</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Penjualan Tunai (Cash):</span>
                        <span class="font-bold tabular-nums">Rp {{ number_format($shift->total_sales_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Penjualan Non-Tunai (QRIS/Debit):</span>
                        <span class="font-bold tabular-nums">Rp {{ number_format($shift->total_sales_non_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-1 border-t border-slate-100">
                        <span>Total Omzet Penjualan:</span>
                        <span class="tabular-nums">Rp {{ number_format($shift->total_sales_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Cash Drawer Reconciliation Section -->
            <div class="space-y-3 border-b border-slate-200 pb-4 mb-5">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900">Rekonsiliasi Kas Laci</h4>
                
                <div class="space-y-2 text-xs text-slate-700">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Modal Kas Awal:</span>
                        <span class="font-bold tabular-nums">Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">(+) Penjualan Tunai Masuk:</span>
                        <span class="font-bold tabular-nums text-emerald-600">+ Rp {{ number_format($shift->total_sales_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 pt-1 border-t border-slate-100">
                        <span>(=) Ekspektasi Kas Teoritis di Laci:</span>
                        <span class="tabular-nums">Rp {{ number_format($shift->expected_ending_cash, 0, ',', '.') }}</span>
                    </div>

                    @if($shift->isClosed())
                        <div class="flex justify-between font-bold text-slate-900">
                            <span>(Fisik) Uang Kas Aktual di Laci:</span>
                            <span class="tabular-nums text-indigo-600 font-extrabold">Rp {{ number_format($shift->actual_ending_cash, 0, ',', '.') }}</span>
                        </div>

                        <!-- Variance Callout -->
                        <div class="p-3 rounded-xl {{ $shift->cash_difference == 0 ? 'bg-slate-50 border border-slate-200' : ($shift->cash_difference > 0 ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-rose-50 border border-rose-200 text-rose-900') }} flex justify-between items-center text-xs mt-2">
                            <span class="font-bold">Selisih Kas (Variance):</span>
                            <span class="font-extrabold tabular-nums text-sm">
                                @if($shift->cash_difference == 0)
                                    Rp 0 (Pas / Tepat)
                                @elseif($shift->cash_difference > 0)
                                    + Rp {{ number_format($shift->cash_difference, 0, ',', '.') }} (Lebih)
                                @else
                                    - Rp {{ number_format(abs($shift->cash_difference), 0, ',', '.') }} (Kurang)
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 font-medium">
                            Register masih terbuka. Perhitungan uang fisik laci akan dilakukan saat penutupan shift.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes & Signatures -->
            @if($shift->notes)
                <div class="text-xs text-slate-500 mb-5 border-b border-slate-100 pb-3">
                    <span class="font-bold text-slate-700">Catatan:</span>
                    <p class="whitespace-pre-line mt-0.5">{{ $shift->notes }}</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-8 text-center text-xs pt-4 text-slate-600">
                <div>
                    <p class="text-slate-400">Kasir Bertugas,</p>
                    <div class="h-14"></div>
                    <p class="font-bold text-slate-900">({{ $shift->cashier->name }})</p>
                </div>

                <div>
                    <p class="text-slate-400">Supervisor / Manager,</p>
                    <div class="h-14"></div>
                    <p class="font-bold text-slate-900">({{ $shift->closer->name ?? '........................' }})</p>
                </div>
            </div>

        </x-card>

    </div>
</x-layouts.app>
