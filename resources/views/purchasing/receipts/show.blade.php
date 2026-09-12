<x-layouts.app :title="$receipt->receipt_number" header="Detail Penerimaan Barang (GR)">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Top Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.receipts.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $receipt->receipt_number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Penerimaan Selesai
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Diterima oleh <span class="font-semibold text-slate-700">{{ $receipt->receiver->name ?? 'Sistem' }}</span> pada {{ $receipt->received_date->format('d/m/Y H:i') }} WIB
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.orders.show', $receipt->purchaseOrder) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Lihat Dokumen PO ({{ $receipt->purchaseOrder->po_number }})
                </a>
            </div>
        </div>

        <!-- Receipt Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pemasok / Supplier:</span>
                <p class="text-base font-bold text-slate-900 mt-1">{{ $receipt->purchaseOrder->supplier->name }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Kode: {{ $receipt->purchaseOrder->supplier->code }}</p>
            </x-card>

            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi Penerimaan / Gudang:</span>
                <p class="text-base font-bold text-slate-900 mt-1">{{ $receipt->location->name }}</p>
                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200 mt-0.5">
                    {{ $receipt->location->code }}
                </span>
            </x-card>

            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Surat Jalan & Faktur:</span>
                <p class="text-sm font-bold text-slate-900 mt-1 font-mono">SJ: {{ $receipt->delivery_order_number ?? '-' }}</p>
                <p class="text-xs font-mono text-slate-600 mt-0.5">Faktur: {{ $receipt->invoice_number ?? '-' }}</p>
            </x-card>
        </div>

        <!-- Items Received Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Rincian Fisik Barang Yang Diterima</h3>
                <span class="text-xs font-semibold text-slate-500">{{ $receipt->items->count() }} item barang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Item Produk</th>
                            <th class="px-4 py-4 text-right">Kuantitas Fisik Diterima</th>
                            <th class="px-4 py-4 text-right">Harga Beli Riil / Base Unit</th>
                            <th class="px-6 py-4 text-right">Nilai Penerimaan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($receipt->items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $item->product->sku }}</div>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums">
                                    <span class="text-base font-extrabold text-emerald-600">+{{ number_format($item->received_quantity_base) }}</span>
                                    <span class="text-xs font-semibold text-slate-500">{{ $item->product->base_unit_name }}</span>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-slate-800">
                                    Rp {{ number_format($item->actual_unit_cost, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums font-bold text-slate-900">
                                    Rp {{ number_format($item->received_quantity_base * $item->actual_unit_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Linked Stock Movements Audit Trail -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Catatan Mutasi Buku Besar Terkait</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jejak mutasi immutable persediaan yang otomatis dibuat oleh sistem.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-4 py-3">Lokasi</th>
                            <th class="px-4 py-3 text-right">Mutasi</th>
                            <th class="px-6 py-3 text-center">Saldo (Sebelum &rarr; Sesudah)</th>
                            <th class="px-4 py-3 text-right">HPP Saat Mutasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($receipt->movements as $m)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3 font-bold text-slate-900">{{ $m->product->name }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $m->location->name }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-extrabold text-emerald-600">
                                    +{{ number_format($m->quantity) }} {{ $m->product->base_unit_name }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap tabular-nums text-xs">
                                    {{ number_format($m->balance_before) }} &rarr; <span class="font-bold text-slate-900">{{ number_format($m->balance_after) }}</span> {{ $m->product->base_unit_name }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-xs font-semibold text-slate-700">
                                    Rp {{ number_format($m->cogs_per_unit, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-slate-400 text-xs">
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
