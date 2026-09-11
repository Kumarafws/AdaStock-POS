<x-layouts.app title="Monitoring Saldo Inventori" header="Saldo Inventori & Stok Fisik">
    <div class="space-y-6">

        <!-- Top Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Saldo Persediaan Real-Time</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Monitoring posisi stok barang per lokasi toko, gudang distribusi, dan karantina.
                </p>
            </div>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.ledger') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        Buku Besar Mutasi
                    </a>

                    <a href="{{ route('adjustments.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Penyesuaian Stok
                    </a>
                </div>
            @endif
        </div>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Item Produk</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">{{ number_format($summary['total_items']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">SKU terdata di lokasi aktif</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kuantitas Fisik</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">{{ number_format($summary['total_quantity']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Satuan terkecil (Base Unit)</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Nilai Stok (HPP)</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">Rp {{ number_format($summary['total_valuation'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Berdasarkan HPP rata-rata berjalan</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Peringatan Stok</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xl font-bold text-amber-600 tabular-nums">{{ $summary['low_stock_count'] }}</span>
                            <span class="text-xs text-slate-400">Menipis</span>
                            <span class="text-slate-300">/</span>
                            <span class="text-xl font-bold text-rose-600 tabular-nums">{{ $summary['out_of_stock_count'] }}</span>
                            <span class="text-xs text-slate-400">Habis</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Perlu pengadaan / restock segera</p>
            </x-card>
        </div>

        <!-- Filter & Search Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <!-- Location Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi</label>
                    @if($isCashier)
                        <div class="px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-700 text-sm font-semibold flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $locations->first()?->name ?? 'Toko Anda' }}
                        </div>
                    @else
                        <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                    [{{ $loc->code }}] {{ $loc->name }} ({{ $loc->type->label() }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Category Filter -->
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

                <!-- Stock Status Filter -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Stok</label>
                    <select name="stock_status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Status</option>
                        <option value="safe" {{ request('stock_status') === 'safe' ? 'selected' : '' }}>Stok Aman</option>
                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stok Menipis (<= Min)</option>
                        <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pencarian</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama, SKU, barcode..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                    <a href="{{ route('inventory.index') }}" class="p-2 border border-slate-300 rounded-xl hover:bg-slate-50 text-slate-600 text-sm font-medium transition-colors" title="Reset Filter">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </a>
                </div>
            </form>
        </x-card>

        <!-- Inventory Balance Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-4 py-4">Lokasi</th>
                            <th class="px-4 py-4 text-right">Saldo Fisik</th>
                            <th class="px-4 py-4 text-right">Min Stok</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">HPP & Nilai Persediaan</th>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                <th class="px-6 py-4 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($inventories as $inv)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Product Info -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-600 shrink-0">
                                            {{ strtoupper(substr($inv->product->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('products.show', $inv->product) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors">
                                                {{ $inv->product->name }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                                                <span class="font-mono">{{ $inv->product->sku }}</span>
                                                @if($inv->product->barcode)
                                                    <span class="text-slate-300">•</span>
                                                    <span>{{ $inv->product->barcode }}</span>
                                                @endif
                                                @if($inv->product->category)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="text-slate-500">{{ $inv->product->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-semibold text-slate-800">{{ $inv->location->name }}</span>
                                    </div>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $inv->location->type->value === 'quarantine' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($inv->location->type->value === 'warehouse' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }} mt-1">
                                        {{ $inv->location->code }}
                                    </span>
                                </td>

                                <!-- Physical Stock & Secondary Breakdown -->
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <div class="text-base font-extrabold tabular-nums {{ $inv->quantity <= 0 ? 'text-rose-600' : ($inv->quantity <= $inv->product->min_stock ? 'text-amber-600' : 'text-slate-900') }}">
                                        {{ number_format($inv->quantity) }} <span class="text-xs font-semibold text-slate-500">{{ $inv->product->base_unit_name }}</span>
                                    </div>
                                    <div class="text-[11px] font-medium text-slate-500 mt-0.5">
                                        {{ $inv->product->formatQuantityBreakdown($inv->quantity) }}
                                    </div>
                                </td>

                                <!-- Minimum Stock -->
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-slate-600">
                                    {{ number_format($inv->product->min_stock) }} {{ $inv->product->base_unit_name }}
                                </td>

                                <!-- Status Badge -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    @if($inv->quantity <= 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Habis
                                        </span>
                                    @elseif($inv->quantity <= $inv->product->min_stock)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menipis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aman
                                        </span>
                                    @endif
                                </td>

                                <!-- Valuation (HPP & Total) -->
                                <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums">
                                    <div class="font-extrabold text-slate-900">
                                        Rp {{ number_format($inv->inventory_value, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        @ Rp {{ number_format($inv->product->purchase_price, 0, ',', '.') }}
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('adjustments.create', ['product_id' => $inv->product_id, 'location_id' => $inv->location_id]) }}" 
                                               class="p-1.5 rounded-lg border border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300 text-slate-500 transition-colors" 
                                               title="Sesuaikan Stok">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('inventory.ledger', ['search' => $inv->product->sku, 'location_id' => $inv->location_id]) }}" 
                                               class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-500 transition-colors" 
                                               title="Lihat Riwayat Mutasi">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                @endif

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada saldo inventori</p>
                                    <p class="text-xs text-slate-500 mt-1">Gunakan filter lain atau lakukan penerimaan/penyesuaian stok baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($inventories->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $inventories->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
