<x-layouts.app title="Rekonsiliasi Kas Laci & Shift" header="Rekonsiliasi Shift Kasir">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Rekonsiliasi Kas Laci & Shift Kasir</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Audit kepatuhan uang fisik laci kasir (*cash variance*), perbandingan modal awal, penjualan tunai, dan hitungan buta kasir (*Z-Report*).
                </p>
            </div>
            
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 shadow-2xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0014.25 1.5h-4.5A2.25 2.25 0 007.5 3.75v3.456" />
                </svg>
                Cetak Rekap
            </button>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5 print:hidden">
            <form method="GET" action="{{ route('reports.shifts') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi Toko</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                </div>
            </form>
        </x-card>

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:grid-cols-4">
            <x-card class="p-5 border-l-4 border-l-blue-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sesi Shift</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($report['total_shifts_count']) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">
                    {{ $report['closed_shifts_count'] }} ditutup, {{ $report['open_shifts_count'] }} masih aktif
                </p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-purple-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Modal Awal Disetor</span>
                <p class="text-2xl font-black text-purple-600 mt-1">Rp {{ number_format($report['total_starting_cash'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Akumulasi uang awal laci kasir</p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-emerald-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penjualan Tunai Laci</span>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($report['total_sales_cash'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Arus kas masuk laci kasir</p>
            </x-card>

            <x-card class="p-5 border-l-4" class="{{ $report['net_cash_difference'] < 0 ? 'border-l-rose-500' : 'border-l-slate-400' }}">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Net Selisih Kas (Over/Short)</span>
                <p class="text-2xl font-black mt-1 {{ $report['net_cash_difference'] < 0 ? 'text-rose-600' : ($report['net_cash_difference'] > 0 ? 'text-blue-600' : 'text-slate-900') }}">
                    {{ $report['net_cash_difference'] > 0 ? '+Rp ' : ($report['net_cash_difference'] < 0 ? '-Rp ' : 'Rp ') }}{{ number_format(abs($report['net_cash_difference']), 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    Defisit: Rp {{ number_format(abs($report['total_short_cash']), 0, ',', '.') }} &bull; Surplus: Rp {{ number_format($report['total_over_cash'], 0, ',', '.') }}
                </p>
            </x-card>
        </div>

        <!-- Shift History Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Riwayat Shift & Rekonsiliasi Kas Laci</h3>
                    <p class="text-xs text-slate-500">Hasil audit penutupan shift kasir pada rentang tanggal terpilih</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">No. Shift</th>
                            <th class="px-4 py-3.5">Kasir</th>
                            <th class="px-4 py-3.5">Lokasi</th>
                            <th class="px-4 py-3.5">Buka - Tutup</th>
                            <th class="px-4 py-3.5 text-right">Modal Awal</th>
                            <th class="px-4 py-3.5 text-right">Penjualan Kas</th>
                            <th class="px-4 py-3.5 text-right">Ekspektasi Kas</th>
                            <th class="px-4 py-3.5 text-right">Kas Fisik Aktual</th>
                            <th class="px-4 py-3.5 text-right">Selisih Kas</th>
                            <th class="px-6 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($report['shifts'] as $shift)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3.5 font-mono font-bold text-indigo-600">
                                    <a href="{{ route('shifts.show', $shift) }}" class="hover:underline">
                                        {{ $shift->shift_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 text-xs font-semibold text-slate-800">
                                    {{ $shift->cashier->name }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-600">
                                    {{ $shift->location->name }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-700 whitespace-nowrap">
                                    <div>{{ $shift->opened_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : 'Masih Buka' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs">
                                    Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs text-emerald-700 font-medium">
                                    Rp {{ number_format($shift->total_sales_cash, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs font-bold text-slate-800">
                                    Rp {{ number_format($shift->expected_ending_cash, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs font-bold text-slate-900">
                                    {{ $shift->actual_ending_cash !== null ? 'Rp ' . number_format($shift->actual_ending_cash, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs whitespace-nowrap font-bold">
                                    @if($shift->cash_difference !== null)
                                        @if($shift->cash_difference == 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Pas (Rp 0)
                                            </span>
                                        @elseif($shift->cash_difference < 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-rose-50 text-rose-700 border border-rose-200">
                                                -Rp {{ number_format(abs($shift->cash_difference), 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-blue-50 text-blue-700 border border-blue-200">
                                                +Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">Belum tutup</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    <a href="{{ route('shifts.show', $shift) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-2xs">
                                        Slip Z-Report
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-400 text-xs">
                                    Tidak ada data sesi shift kasir pada periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</x-layouts.app>
