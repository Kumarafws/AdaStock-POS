<x-layouts.app title="Laporan Penjualan" header="Laporan Penjualan">
    <div class="space-y-6">

        <!-- Header & Date Preset Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Penjualan (Sales Report)</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Analisis omset kotor, diskon, nilai retur, laba kotor, dan distribusi metode pembayaran.
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 shadow-2xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0014.25 1.5h-4.5A2.25 2.25 0 007.5 3.75v3.456" />
                    </svg>
                    Cetak
                </button>

                <a href="{{ route('reports.sales.export-csv', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Ekspor CSV
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5 print:hidden">
            <form method="GET" action="{{ route('reports.sales') }}" class="space-y-4">
                <!-- Preset Quick Buttons -->
                <div class="flex flex-wrap items-center gap-1.5 pb-3 border-b border-slate-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mr-2">Preset:</span>
                    <a href="{{ route('reports.sales', array_merge(request()->except(['start_date', 'end_date', 'preset']), ['preset' => 'today'])) }}" 
                       class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedPreset === 'today' ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('reports.sales', array_merge(request()->except(['start_date', 'end_date', 'preset']), ['preset' => 'yesterday'])) }}" 
                       class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedPreset === 'yesterday' ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        Kemarin
                    </a>
                    <a href="{{ route('reports.sales', array_merge(request()->except(['start_date', 'end_date', 'preset']), ['preset' => 'last_7_days'])) }}" 
                       class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedPreset === 'last_7_days' ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        7 Hari Terakhir
                    </a>
                    <a href="{{ route('reports.sales', array_merge(request()->except(['start_date', 'end_date', 'preset']), ['preset' => 'last_30_days'])) }}" 
                       class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedPreset === 'last_30_days' ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        30 Hari Terakhir
                    </a>
                    <a href="{{ route('reports.sales', array_merge(request()->except(['start_date', 'end_date', 'preset']), ['preset' => 'this_month'])) }}" 
                       class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedPreset === 'this_month' ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        Bulan Ini
                    </a>
                </div>

                <!-- Custom Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                    </div>

                    <div class="lg:col-span-3">
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

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kasir / Petugas</label>
                        <select name="cashier_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            <option value="">Semua Kasir</option>
                            @foreach($cashiers as $c)
                                <option value="{{ $c->id }}" {{ request('cashier_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 print:grid-cols-3">
            <x-card class="p-4 border-l-4 border-l-blue-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Omset Kotor</span>
                <p class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($report['gross_sales'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Sebelum diskon</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-amber-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Diskon</span>
                <p class="text-xl font-black text-amber-600 mt-1">Rp {{ number_format($report['total_discounts'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Potongan harga jual</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-rose-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Retur</span>
                <p class="text-xl font-black text-rose-600 mt-1">Rp {{ number_format($report['total_refunds'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Refund pelanggan</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-emerald-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penjualan Bersih</span>
                <p class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($report['net_sales'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Kotor - Diskon - Retur</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-purple-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laba Kotor (Profit)</span>
                <p class="text-xl font-black text-purple-600 mt-1">Rp {{ number_format($report['gross_profit'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Bersih - HPP (COGS)</p>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-indigo-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Transaksi</span>
                <p class="text-xl font-black text-indigo-600 mt-1">Rp {{ number_format($report['average_order_value'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($report['total_transactions']) }} struk transaksi</p>
            </x-card>
        </div>

        <!-- Payment Methods & Void Summary Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Payment Methods Breakdown -->
            <x-card class="p-5 lg:col-span-2">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3">
                    Distribusi Metode Pembayaran
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                    @foreach($report['payment_methods'] as $pm)
                        <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700">{{ $pm['label'] }}</span>
                                <span class="text-[11px] font-semibold text-slate-400">{{ $pm['count'] }} trx</span>
                            </div>
                            <div class="text-base font-black text-slate-900">
                                Rp {{ number_format($pm['total_amount'], 0, ',', '.') }}
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $pm['percentage'] }}%"></div>
                            </div>
                            <div class="text-[10px] text-right font-medium text-slate-500">
                                {{ $pm['percentage'] }}% dari omset
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Void Notice / Audit Summary Card -->
            <x-card class="p-5 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3">
                        Transaksi Dibatalkan (Void)
                    </h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Jumlah Transaksi Void:</span>
                            <span class="font-bold text-rose-600 font-mono">{{ $report['voided_count'] }} transaksi</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nominal yang Dibatalkan:</span>
                            <span class="font-black text-rose-600">Rp {{ number_format($report['voided_amount'], 0, ',', '.') }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            Seluruh transaksi void telah dikeluarkan dari kalkulasi penjualan bersih dan laba kotor.
                        </p>
                    </div>
                </div>

                <a href="{{ route('pos.void-logs') }}" class="mt-4 inline-flex items-center justify-center gap-2 w-full py-2.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Lihat Audit Log Void
                </a>
            </x-card>
        </div>

        <!-- Detailed Transactions Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Rincian Transaksi Penjualan</h3>
                    <p class="text-xs text-slate-500">Daftar transaksi dalam rentang periode yang dipilih</p>
                </div>
                <span class="text-xs font-bold text-slate-600 font-mono">{{ $report['sales']->count() }} Dokumen</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">No. Faktur</th>
                            <th class="px-4 py-3.5">Waktu</th>
                            <th class="px-4 py-3.5">Lokasi</th>
                            <th class="px-4 py-3.5">Kasir</th>
                            <th class="px-4 py-3.5">Pelanggan</th>
                            <th class="px-4 py-3.5 text-right">Subtotal</th>
                            <th class="px-4 py-3.5 text-right">Diskon</th>
                            <th class="px-4 py-3.5 text-right">Total Akhir</th>
                            <th class="px-4 py-3.5">Pembayaran</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($report['sales'] as $sale)
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $sale->isVoided() ? 'opacity-60 bg-rose-50/20' : '' }}">
                                <td class="px-6 py-3.5 font-mono font-bold text-indigo-600">
                                    <a href="{{ route('pos.receipt', $sale) }}" class="hover:underline">
                                        {{ $sale->sale_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-700">
                                    {{ $sale->transaction_date->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                    {{ $sale->location->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                    {{ $sale->cashier->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-slate-800">
                                    {{ $sale->customer_name ?: 'Pelanggan Umum' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs">
                                    Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs {{ $sale->discount_amount > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">
                                    {{ $sale->discount_amount > 0 ? 'Rp ' . number_format($sale->discount_amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs whitespace-nowrap">
                                    {{ $sale->formatted_total }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                    @foreach($sale->payments as $pay)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $pay->payment_method->badgeClass() }}">
                                            {{ $pay->payment_method->label() }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $sale->status->badgeClass() }}">
                                        {{ $sale->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-400 text-xs">
                                    Tidak ada data transaksi penjualan pada rentang periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</x-layouts.app>
