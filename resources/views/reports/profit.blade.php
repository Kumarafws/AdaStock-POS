<x-layouts.app title="Laporan Laba Kotor & Margin" header="Laba Kotor & Analisis Margin">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Laba Kotor & Margin Produk</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Analisis profitabilitas barang, perbandingan omset vs HPP (COGS), dan kontribusi laba per kategori & merek.
                </p>
            </div>
            
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 shadow-2xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0014.25 1.5h-4.5A2.25 2.25 0 007.5 3.75v3.456" />
                </svg>
                Cetak Laporan
            </button>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5 print:hidden">
            <form method="GET" action="{{ route('reports.profit') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kategori</label>
                    <select name="category_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Merek</label>
                    <select name="brand_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Merek</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
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
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Omset Produk</span>
                <p class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Nilai penjualan kotor</p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-rose-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total HPP (COGS)</span>
                <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($report['total_cogs'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Modal beli barang berjalan</p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-emerald-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Laba Kotor</span>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($report['total_profit'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Omset - HPP</p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-purple-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Margin Laba</span>
                <p class="text-2xl font-black text-purple-600 mt-1">{{ $report['overall_margin_percent'] }}%</p>
                <p class="text-[11px] text-slate-400 mt-1">Persentase margin kotor keseluruhan</p>
            </x-card>
        </div>

        <!-- Top Sellers & Most Profitable Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top 10 Best Sellers -->
            <x-card class="p-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3 flex items-center justify-between">
                    <span>Top 10 Produk Terlaris (Kuantitas)</span>
                    <span class="text-xs font-semibold text-indigo-600 font-mono">Volume Penjualan</span>
                </h3>

                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="py-2.5 px-3">Produk</th>
                                <th class="py-2.5 px-3 text-center">Terjual</th>
                                <th class="py-2.5 px-3 text-right">Omset</th>
                                <th class="py-2.5 px-3 text-right">Laba Kotor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($report['top_sellers'] as $prod)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-900">{{ $prod['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $prod['sku'] }} &bull; {{ $prod['category_name'] }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-800">
                                        {{ number_format($prod['quantity_sold']) }} unit
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono">
                                        Rp {{ number_format($prod['revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">
                                        Rp {{ number_format($prod['profit'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Top 10 Most Profitable -->
            <x-card class="p-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3 flex items-center justify-between">
                    <span>Top 10 Kontributor Laba Tertinggi</span>
                    <span class="text-xs font-semibold text-emerald-600 font-mono">Nominal Profit</span>
                </h3>

                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="py-2.5 px-3">Produk</th>
                                <th class="py-2.5 px-3 text-right">Laba Bersih</th>
                                <th class="py-2.5 px-3 text-center">Margin</th>
                                <th class="py-2.5 px-3 text-right">HPP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($report['top_profitable'] as $prod)
                                <tr>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-900">{{ $prod['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $prod['sku'] }} &bull; {{ $prod['category_name'] }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-600">
                                        Rp {{ number_format($prod['profit'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] {{ $prod['margin_percent'] >= 25 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ $prod['margin_percent'] }}%
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-500">
                                        Rp {{ number_format($prod['cogs'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Category & Brand Contribution Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Category Margin Breakdown -->
            <x-card class="p-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3">
                    Margin Laba per Kategori Produk
                </h3>

                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="py-2.5 px-3">Kategori</th>
                                <th class="py-2.5 px-3 text-right">Omset</th>
                                <th class="py-2.5 px-3 text-right">Laba Kotor</th>
                                <th class="py-2.5 px-3 text-center">Margin %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($report['category_breakdown'] as $cat)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $cat['name'] }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono">Rp {{ number_format($cat['revenue'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">Rp {{ number_format($cat['profit'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                            {{ $cat['margin_percent'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Brand Margin Breakdown -->
            <x-card class="p-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3">
                    Margin Laba per Merek (Brand)
                </h3>

                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="py-2.5 px-3">Merek / Brand</th>
                                <th class="py-2.5 px-3 text-right">Omset</th>
                                <th class="py-2.5 px-3 text-right">Laba Kotor</th>
                                <th class="py-2.5 px-3 text-center">Margin %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($report['brand_breakdown'] as $brand)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $brand['name'] }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono">Rp {{ number_format($brand['revenue'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">Rp {{ number_format($brand['profit'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                            {{ $brand['margin_percent'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

    </div>
</x-layouts.app>
