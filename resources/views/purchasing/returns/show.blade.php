<x-layouts.app :title="$return->return_number" header="Detail Retur Pembelian">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.returns.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $return->return_number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Retur Diproses
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Diproses oleh <span class="font-semibold text-slate-700">{{ $return->returner->name ?? 'Sistem' }}</span> pada {{ $return->created_at->format('d/m/Y H:i') }} WIB
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pemasok / Supplier:</span>
                <p class="text-base font-bold text-slate-900 mt-1">{{ $return->supplier->name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Kode: {{ $return->supplier->code }}</p>
            </x-card>

            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi Asal Stok:</span>
                <p class="text-base font-bold text-slate-900 mt-1">{{ $return->location->name }}</p>
                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $return->location->type->value === 'quarantine' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }} mt-0.5">
                    {{ $return->location->code }}
                </span>
            </x-card>

            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Alasan Retur:</span>
                <p class="text-sm font-bold text-slate-900 mt-1">{{ $return->reason }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Tgl Retur: {{ $return->return_date->format('d/m/Y') }}</p>
            </x-card>
        </div>

        <!-- Returned Items Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Rincian Barang Yang Dikembalikan</h3>
                <span class="text-xs font-semibold text-slate-500">{{ $return->items->count() }} item barang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Item Produk</th>
                            <th class="px-4 py-4 text-right">Kuantitas Diretur</th>
                            <th class="px-4 py-4 text-right">Harga Satuan Acuan</th>
                            <th class="px-6 py-4 text-right">Total Nilai (Rp)</th>
                            <th class="px-6 py-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($return->items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $item->product->sku }}</div>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums">
                                    <span class="text-base font-extrabold text-rose-600">-{{ number_format($item->quantity) }}</span>
                                    <span class="text-xs font-semibold text-slate-500">{{ $item->product->base_unit_name }}</span>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-slate-800">
                                    Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ $item->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-between items-center">
                <div class="text-xs text-slate-500">
                    <span class="font-bold text-slate-700">Catatan:</span> {{ $return->notes ?? 'Tidak ada catatan.' }}
                </div>
                <div class="text-base font-extrabold text-slate-900">
                    Total Nilai Retur: <span class="text-rose-600 tabular-nums">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </x-card>

        <!-- Linked Stock Movements -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Catatan Pengurangan Stok Buku Besar</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-4 py-3">Lokasi</th>
                            <th class="px-4 py-3 text-right">Mutasi</th>
                            <th class="px-6 py-3 text-center">Saldo (Sebelum &rarr; Sesudah)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($return->movements as $m)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3 font-bold text-slate-900">{{ $m->product->name }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $m->location->name }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-extrabold text-rose-600">
                                    {{ number_format($m->quantity) }} {{ $m->product->base_unit_name }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap tabular-nums text-xs">
                                    {{ number_format($m->balance_before) }} &rarr; <span class="font-bold text-slate-900">{{ number_format($m->balance_after) }}</span> {{ $m->product->base_unit_name }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-slate-400 text-xs">
                                    Tidak ada catatan mutasi persediaan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</x-layouts.app>
