<x-layouts.app title="Retur Pembelian Supplier" header="Retur Pembelian (Purchase Returns)">
    <div class="space-y-6">

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Retur Pembelian ke Supplier</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Pengembalian barang rusak, cacat produksi, atau salah kirim dari toko/gudang/karantina ke distributor.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.orders.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    Daftar PO
                </a>

                <a href="{{ route('purchasing.returns.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    + Buat Retur Pembelian
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('purchasing.returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Supplier</label>
                    <select name="supplier_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi Asal Barang</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Retur, alasan, catatan..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Returns Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Retur</th>
                            <th class="px-4 py-4">Tanggal Retur</th>
                            <th class="px-6 py-4">Supplier</th>
                            <th class="px-4 py-4">Lokasi Asal</th>
                            <th class="px-6 py-4">Alasan</th>
                            <th class="px-4 py-4 text-right">Nilai Retur (Rp)</th>
                            <th class="px-4 py-4">Petugas</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($returns as $ret)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-600">
                                    <a href="{{ route('purchasing.returns.show', $ret) }}" class="hover:underline">
                                        {{ $ret->return_number }}
                                    </a>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500">
                                    <span class="font-bold text-slate-800">{{ $ret->return_date->format('d/m/Y') }}</span>
                                </td>

                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ $ret->supplier->name }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $ret->location->type->value === 'quarantine' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ $ret->location->code }}
                                    </span>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ $ret->location->name }}</div>
                                </td>

                                <td class="px-6 py-4 text-xs text-slate-700">
                                    {{ $ret->reason }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-bold text-slate-900">
                                    Rp {{ number_format($ret->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700">
                                    {{ $ret->returner->name ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('purchasing.returns.show', $ret) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors shadow-2xs">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada catatan retur pembelian</p>
                                    <p class="text-xs text-slate-500 mt-1">Klik "+ Buat Retur Pembelian" untuk mengembalikan barang ke distributor.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($returns->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $returns->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
