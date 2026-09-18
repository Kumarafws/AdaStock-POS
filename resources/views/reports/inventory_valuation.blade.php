<x-layouts.app title="Valuasi Nilai Aset Stok" header="Valuasi Aset Inventori">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Valuasi Nilai Aset Stok (Inventory Valuation)</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Valuasi total modal berjalan barang dagang berdasarkan Moving Average Cost (HPP) di seluruh toko, gudang, dan karantina.
                </p>
            </div>
            
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 shadow-2xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0014.25 1.5h-4.5A2.25 2.25 0 007.5 3.75v3.456" />
                </svg>
                Cetak Valuasi
            </button>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5 print:hidden">
            <form method="GET" action="{{ route('reports.inventory-valuation') }}" class="flex flex-col sm:flex-row items-end gap-3 max-w-md">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi Inventori</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi (Konsolidasi)</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }} ({{ $loc->type->label() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs shrink-0">
                    Filter
                </button>
                @if(request()->filled('location_id'))
                    <a href="{{ route('reports.inventory-valuation') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-colors shrink-0">
                        Reset
                    </a>
                @endif
            </form>
        </x-card>

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 print:grid-cols-3">
            <x-card class="p-5 border-l-4 border-l-purple-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Buku Aset Stok</span>
                <p class="text-2xl font-black text-purple-600 mt-1">Rp {{ number_format($report['total_asset_value'], 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Modal barang berjalan</p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-blue-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kuantitas Fisik Barang</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($report['total_stock_qty']) }} unit</p>
                <p class="text-[11px] text-slate-400 mt-1">Unit dalam satuan dasar</p>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-amber-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk Stok Kritis (Under Min)</span>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ number_format($report['critical_count']) }} produk</p>
                <p class="text-[11px] text-slate-400 mt-1">Perlu segera dilakukan PO pengadaan</p>
            </x-card>
        </div>

        <!-- Location Breakdown Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($report['locations'] as $loc)
                <x-card class="p-4 bg-slate-50/50 border border-slate-200">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 text-sm">[{{ $loc['code'] }}] {{ $loc['name'] }}</span>
                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold border {{ $loc['type_badge'] }}">
                            {{ $loc['type'] }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between">
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase tracking-wider">Nilai Aset:</p>
                            <p class="text-lg font-black text-slate-900 font-mono">Rp {{ number_format($loc['total_value'], 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] text-slate-400 uppercase tracking-wider">Total Stok:</p>
                            <p class="text-sm font-bold text-slate-700 font-mono">{{ number_format($loc['total_quantity']) }} unit</p>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <!-- Valuation Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Rincian Nilai Aset per Produk & Lokasi</h3>
                    <p class="text-xs text-slate-500">Kalkulasi saldo kuantitas dikali Moving Average Cost</p>
                </div>
                <span class="text-xs font-bold text-slate-600 font-mono">{{ count($report['items']) }} Item Data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-3.5">Produk</th>
                            <th class="px-4 py-3.5">Lokasi</th>
                            <th class="px-4 py-3.5 text-center">Kategori</th>
                            <th class="px-4 py-3.5 text-right">Saldo Stok</th>
                            <th class="px-4 py-3.5 text-right">HPP (Moving Avg)</th>
                            <th class="px-4 py-3.5 text-right">Total Nilai Buku</th>
                            <th class="px-6 py-3.5 text-center">Status Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($report['items'] as $it)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $it['product_name'] }}</div>
                                    <div class="text-xs font-mono text-slate-400">SKU: {{ $it['sku'] }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-700 whitespace-nowrap">
                                    <span class="font-mono font-semibold">[{{ $it['location_code'] }}]</span> {{ $it['location_name'] }}
                                </td>
                                <td class="px-4 py-3.5 text-center text-xs">
                                    <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px]">
                                        {{ $it['category_name'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs whitespace-nowrap">
                                    {{ number_format($it['quantity']) }} {{ $it['unit_name'] }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-xs text-slate-600 whitespace-nowrap">
                                    Rp {{ number_format($it['moving_average_cost'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-purple-700 text-xs whitespace-nowrap">
                                    Rp {{ number_format($it['total_value'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                    @if($it['is_critical'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menipis (Min: {{ $it['min_stock'] }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aman / Cukup
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400 text-xs">
                                    Tidak ada data stok inventori.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</x-layouts.app>
