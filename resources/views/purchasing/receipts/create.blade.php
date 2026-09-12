<x-layouts.app title="Formulir Penerimaan Barang" header="Penerimaan Barang (Goods Receipt)">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Top Header & Back -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.orders.show', $order) }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Catat Penerimaan Barang Fisik (Goods Receipt)</h2>
                    <p class="text-xs text-slate-500">
                        Penerimaan bertahap (*Partial*) atau penuh untuk Purchase Order <span class="font-mono font-bold text-slate-700">{{ $order->po_number }}</span>
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('purchasing.receipts.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="purchase_order_id" value="{{ $order->id }}">

            <!-- Reference PO Card -->
            <x-card class="p-5 bg-indigo-50/40 border-indigo-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor PO:</span>
                        <p class="font-mono text-base font-extrabold text-indigo-700 mt-0.5">{{ $order->po_number }}</p>
                        <span class="text-xs text-slate-500">Tgl: {{ $order->order_date->format('d/m/Y') }}</span>
                    </div>

                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Supplier:</span>
                        <p class="text-base font-bold text-slate-900 mt-0.5">{{ $order->supplier->name }}</p>
                        <span class="text-xs text-slate-500">Kode: {{ $order->supplier->code }}</span>
                    </div>

                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi Bongkar:</span>
                        <p class="text-base font-bold text-slate-900 mt-0.5">{{ $order->destinationLocation->name }}</p>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800 mt-0.5">
                            {{ $order->destinationLocation->code }}
                        </span>
                    </div>
                </div>
            </x-card>

            <!-- Delivery & Invoice Information -->
            <x-card class="p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    1. Dokumen Pengantar & Waktu Penerimaan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    <div>
                        <label for="received_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal & Waktu Penerimaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" 
                               name="received_date" 
                               id="received_date" 
                               value="{{ old('received_date', now()->format('Y-m-d\TH:i')) }}" 
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                        @error('received_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delivery_order_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            No. Surat Jalan Supplier (DO)
                        </label>
                        <input type="text" 
                               name="delivery_order_number" 
                               id="delivery_order_number" 
                               value="{{ old('delivery_order_number') }}" 
                               placeholder="Contoh: SJ/2026/09/8812" 
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                        @error('delivery_order_number')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="invoice_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            No. Faktur / Tagihan (Invoice)
                        </label>
                        <input type="text" 
                               name="invoice_number" 
                               id="invoice_number" 
                               value="{{ old('invoice_number') }}" 
                               placeholder="Contoh: INV-IND-99120" 
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                        @error('invoice_number')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan Pemeriksaan Fisik (QC / Receiving Notes)
                        </label>
                        <textarea name="notes" id="notes" rows="2" placeholder="Kondisi kemasan saat dibongkar, nomor segel kontainer, atau keterangan driver..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">{{ old('notes') }}</textarea>
                    </div>

                </div>
            </x-card>

            <!-- Items Receiving Table -->
            <x-card class="overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">
                        2. Verifikasi Kuantitas Fisik Yang Diterima
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Masukkan jumlah Base Unit yang lolos pemeriksaan fisik. Kuantitas yang belum tiba dapat diterima pada pengiriman berikutnya.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Item Produk</th>
                                <th class="px-4 py-4 text-right">Dipesan Awal</th>
                                <th class="px-4 py-4 text-right">Sudah Tiba</th>
                                <th class="px-4 py-4 text-right">Sisa Belum Tiba</th>
                                <th class="px-6 py-4 text-right w-44">Kuantitas Tiba Hari Ini</th>
                                <th class="px-6 py-4 text-right w-44">Harga Faktur / Unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($order->items as $idx => $item)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <input type="hidden" name="items[{{ $idx }}][purchase_order_item_id]" value="{{ $item->id }}">

                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $item->product->sku }}</div>
                                    </td>

                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-slate-700">
                                        {{ number_format($item->ordered_quantity_base) }} {{ $item->product->base_unit_name }}
                                        <div class="text-[10px] text-slate-400">({{ $item->ordered_quantity }} {{ $item->unit_name }})</div>
                                    </td>

                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-slate-600">
                                        {{ number_format($item->received_quantity_base) }} {{ $item->product->base_unit_name }}
                                    </td>

                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-extrabold {{ $item->remaining_quantity_base > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                        {{ number_format($item->remaining_quantity_base) }} {{ $item->product->base_unit_name }}
                                    </td>

                                    <!-- Received Qty Today Input -->
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="relative max-w-[150px] ml-auto">
                                            <input type="number" 
                                                   name="items[{{ $idx }}][received_quantity_base]" 
                                                   value="{{ old("items.{$idx}.received_quantity_base", $item->remaining_quantity_base) }}" 
                                                   min="0" 
                                                   max="{{ $item->remaining_quantity_base }}" 
                                                   class="w-full rounded-xl border-slate-300 text-sm font-extrabold tabular-nums text-right focus:border-indigo-500 focus:ring-indigo-500 py-2 pr-10 {{ $item->remaining_quantity_base == 0 ? 'bg-slate-100 text-slate-400' : 'bg-white' }}"
                                                   {{ $item->remaining_quantity_base == 0 ? 'readonly' : '' }}>
                                            <span class="absolute inset-y-0 right-3 flex items-center text-xs font-semibold text-slate-400 pointer-events-none">
                                                {{ $item->product->base_unit_name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Actual Unit Cost Input -->
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="relative max-w-[150px] ml-auto">
                                            <input type="number" 
                                                   name="items[{{ $idx }}][actual_unit_cost]" 
                                                   value="{{ old("items.{$idx}.actual_unit_cost", $item->base_unit_cost) }}" 
                                                   min="0" 
                                                   step="50"
                                                   class="w-full rounded-xl border-slate-300 text-sm font-bold tabular-nums text-right focus:border-indigo-500 focus:ring-indigo-500 py-2">
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Ledger & Costing Info Notice -->
                <div class="p-4 bg-emerald-50/60 border-t border-emerald-100 text-xs text-emerald-900 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">Otomatisasi Mutasi Buku Besar & Moving Average Costing</p>
                        <p class="text-emerald-700 mt-0.5">
                            Saat dikonfirmasi, saldo fisik di <span class="font-bold">{{ $order->destinationLocation->name }}</span> akan bertambah dengan tipe mutasi <span class="font-mono font-bold">PURCHASE_RECEIPT</span>, dan HPP Rata-rata Bergerak produk akan langsung diperbarui.
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('purchasing.orders.show', $order) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    Konfirmasi Penerimaan Fisik & Update HPP
                </button>
            </div>

        </form>

    </div>
</x-layouts.app>
