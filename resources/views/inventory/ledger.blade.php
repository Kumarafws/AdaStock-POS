<x-layouts.app title="Buku Besar Mutasi Stok" header="Buku Besar Mutasi Stok">
    <div class="space-y-6">

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Buku Besar Mutasi Persediaan (Stock Ledger)</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Jejak audit permanen dan <span class="font-semibold text-slate-700">immutable</span> untuk setiap keluar-masuk barang dan penyesuaian stok.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Saldo Stok
                </a>

                <a href="{{ route('adjustments.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Penyesuaian Stok
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('inventory.ledger') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <!-- Location Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Movement Type Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Jenis Mutasi</label>
                    <select name="movement_type" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Jenis Mutasi</option>
                        @foreach($movementTypes as $mt)
                            <option value="{{ $mt->value }}" {{ request('movement_type') === $mt->value ? 'selected' : '' }}>
                                {{ $mt->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <!-- Search Input & Submit -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                    <a href="{{ route('inventory.ledger') }}" class="p-2 border border-slate-300 rounded-xl hover:bg-slate-50 text-slate-600 text-sm font-medium transition-colors" title="Reset Filter">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </a>
                </div>

                <!-- Search Keyword in next line full width -->
                <div class="lg:col-span-12">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari kata kunci: nama produk, SKU, barcode, nomor dokumen (ADJ/PO/STR), atau catatan..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>
            </form>
        </x-card>

        <!-- Movement Ledger Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-4 py-4">Jenis Mutasi</th>
                            <th class="px-4 py-4">No. Dokumen</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-4 py-4">Lokasi</th>
                            <th class="px-4 py-4 text-right">Mutasi (Qty)</th>
                            <th class="px-6 py-4 text-center">Saldo (Sblm &rarr; Ssdh)</th>
                            <th class="px-4 py-4 text-right">HPP Satuan</th>
                            <th class="px-4 py-4">Petugas</th>
                            <th class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($movements as $m)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Timestamp -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                    <div class="font-bold text-slate-800">{{ $m->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $m->created_at->format('H:i:s') }} WIB</div>
                                </td>

                                <!-- Movement Type Badge -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $m->movement_type->badgeClass() }}">
                                        {{ $m->movement_type->label() }}
                                    </span>
                                </td>

                                <!-- Reference Number -->
                                <td class="px-4 py-4 whitespace-nowrap font-mono text-xs text-indigo-600 font-bold">
                                    {{ $m->reference_number ?? '-' }}
                                </td>

                                <!-- Product Info -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $m->product->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $m->product->sku }}</div>
                                </td>

                                <!-- Location -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $m->location->type->value === 'quarantine' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ $m->location->code }}
                                    </span>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ $m->location->name }}</div>
                                </td>

                                <!-- Signed Quantity (+ / -) -->
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums">
                                    @if($m->quantity > 0)
                                        <span class="inline-flex items-center gap-0.5 text-base font-extrabold text-emerald-600">
                                            +{{ number_format($m->quantity) }}
                                            <span class="text-xs font-semibold text-emerald-500">{{ $m->product->base_unit_name }}</span>
                                        </span>
                                    @elseif($m->quantity < 0)
                                        <span class="inline-flex items-center gap-0.5 text-base font-extrabold text-rose-600">
                                            {{ number_format($m->quantity) }}
                                            <span class="text-xs font-semibold text-rose-500">{{ $m->product->base_unit_name }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-bold">0</span>
                                    @endif
                                </td>

                                <!-- Balance Before -> Balance After -->
                                <td class="px-6 py-4 text-center whitespace-nowrap tabular-nums">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                                        <span class="text-slate-500 font-semibold">{{ number_format($m->balance_before) }}</span>
                                        <span class="text-slate-300 font-bold">&rarr;</span>
                                        <span class="text-slate-900 font-extrabold">{{ number_format($m->balance_after) }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $m->product->base_unit_name }}</span>
                                    </div>
                                </td>

                                <!-- HPP at time of transaction -->
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-xs font-semibold text-slate-700">
                                    Rp {{ number_format($m->cogs_per_unit, 0, ',', '.') }}
                                </td>

                                <!-- User / Actor -->
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-600">
                                    <span class="font-semibold text-slate-800">{{ $m->creator->name ?? '-' }}</span>
                                </td>

                                <!-- Notes -->
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate" title="{{ $m->notes }}">
                                    {{ $m->notes ?? '-' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada catatan mutasi stok</p>
                                    <p class="text-xs text-slate-500 mt-1">Seluruh pergerakan barang fisik akan otomatis tercatat di sini secara permanen.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($movements->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $movements->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
