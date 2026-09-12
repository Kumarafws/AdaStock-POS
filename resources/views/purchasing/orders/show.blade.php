<x-layouts.app :title="$order->po_number" header="Detail Purchase Order">
    <div class="max-w-6xl mx-auto space-y-6" x-data="{ cancelModalOpen: false }">

        <!-- Top Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.orders.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $order->po_number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $order->status->badgeClass() }}">
                            {{ $order->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Diterbitkan oleh <span class="font-semibold text-slate-700">{{ $order->creator->name ?? 'Sistem' }}</span> pada {{ $order->created_at->format('d/m/Y H:i') }} WIB
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($order->status->canCancel())
                    <button type="button" 
                            @click="cancelModalOpen = true"
                            class="px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-sm transition-colors">
                        Batalkan PO
                    </button>
                @endif

                @if($order->status->canReceive())
                    <a href="{{ route('purchasing.receipts.create', ['po_id' => $order->id]) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-600/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.125c.621 0 1.125.504 1.125 1.125v3.75m-6.75-4.875H6a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25h1.5" />
                        </svg>
                        Terima Pengiriman Barang
                    </a>
                @endif
            </div>
        </div>

        <!-- Receiving Progress Card -->
        <x-card class="p-5">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Progress Pemenuhan Fisik (Goods Receipt)</h3>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">
                        {{ number_format($order->items->sum('received_quantity_base')) }} <span class="text-sm font-semibold text-slate-500">/ {{ number_format($order->items->sum('ordered_quantity_base')) }} Base Unit Diterima</span>
                    </p>
                </div>
                <span class="text-2xl font-black text-indigo-600 tabular-nums">{{ $order->receipt_progress }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200">
                <div class="h-3 rounded-full {{ $order->receipt_progress == 100 ? 'bg-emerald-500' : 'bg-indigo-600' }} transition-all duration-500" style="width: {{ $order->receipt_progress }}%"></div>
            </div>
        </x-card>

        <!-- Information Cards (Supplier & Location) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            <!-- Supplier Card -->
            <x-card class="p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pemasok / Supplier</p>
                <h4 class="text-base font-bold text-slate-900">{{ $order->supplier->name }}</h4>
                <div class="mt-2 space-y-1 text-xs text-slate-600">
                    <p><span class="text-slate-400">Kode:</span> <span class="font-mono font-semibold">{{ $order->supplier->code }}</span></p>
                    <p><span class="text-slate-400">Kontak:</span> {{ $order->supplier->contact_name ?? '-' }} ({{ $order->supplier->phone ?? '-' }})</p>
                    <p><span class="text-slate-400">Email:</span> {{ $order->supplier->email ?? '-' }}</p>
                    <p><span class="text-slate-400">Alamat:</span> {{ $order->supplier->address ?? '-' }}</p>
                </div>
            </x-card>

            <!-- Destination Location Card -->
            <x-card class="p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tujuan Pengiriman</p>
                <h4 class="text-base font-bold text-slate-900">{{ $order->destinationLocation->name }}</h4>
                <div class="mt-2 space-y-1 text-xs text-slate-600">
                    <p><span class="text-slate-400">Kode Lokasi:</span> <span class="font-mono font-semibold">{{ $order->destinationLocation->code }}</span></p>
                    <p><span class="text-slate-400">Tipe:</span> {{ $order->destinationLocation->type->label() }}</p>
                    <p><span class="text-slate-400">Alamat:</span> {{ $order->destinationLocation->address ?? '-' }}</p>
                    <p><span class="text-slate-400">Est. Tiba:</span> {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('d/m/Y') : 'Tidak ditentukan' }}</p>
                </div>
            </x-card>

        </div>

        <!-- Ordered Items Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Rincian Item Barang</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Item Produk</th>
                            <th class="px-4 py-4">Satuan Pesan</th>
                            <th class="px-4 py-4 text-right">Harga Beli</th>
                            <th class="px-4 py-4 text-right">Subtotal (Rp)</th>
                            <th class="px-4 py-4 text-right">Total Dipesan</th>
                            <th class="px-4 py-4 text-right">Diterima</th>
                            <th class="px-4 py-4 text-right">Sisa Belum Tiba</th>
                            <th class="px-4 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($order->items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $item->product->sku }}</div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $item->ordered_quantity }} {{ $item->unit_name }}</span>
                                    @if($item->conversion_factor > 1)
                                        <div class="text-[11px] text-slate-400 font-medium">@ {{ $item->conversion_factor }} {{ $item->product->base_unit_name }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-xs">
                                    Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                                    @if($item->conversion_factor > 1)
                                        <div class="text-[10px] text-slate-400">(@ Rp {{ number_format($item->base_unit_cost, 0, ',', '.') }}/{{ $item->product->base_unit_name }})</div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-semibold text-slate-800">
                                    {{ number_format($item->ordered_quantity_base) }} {{ $item->product->base_unit_name }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-extrabold text-emerald-600">
                                    {{ number_format($item->received_quantity_base) }} {{ $item->product->base_unit_name }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-extrabold {{ $item->remaining_quantity_base > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                    {{ number_format($item->remaining_quantity_base) }} {{ $item->product->base_unit_name }}
                                </td>

                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    @if($item->is_fully_received)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Lengkap
                                        </span>
                                    @elseif($item->received_quantity_base > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Parsial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Menunggu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary Footer -->
            <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-xs text-slate-500 max-w-md">
                    <span class="font-bold text-slate-700">Catatan:</span> {{ $order->notes ?? 'Tidak ada catatan.' }}
                </div>

                <div class="space-y-1.5 text-right text-sm">
                    <div class="text-slate-600">Subtotal: <span class="font-bold text-slate-900 tabular-nums">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    @if($order->tax_amount > 0)
                        <div class="text-slate-600">Pajak (PPN): <span class="font-semibold text-slate-800 tabular-nums">+ Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span></div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="text-slate-600">Diskon: <span class="font-semibold text-rose-600 tabular-nums">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="text-base font-extrabold text-slate-900 pt-1 border-t border-slate-200">
                        Total Nilai PO: <span class="text-indigo-600 tabular-nums">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Goods Receipts History Card -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Riwayat Berita Acara Penerimaan Barang (Goods Receipts)</h3>
                <span class="text-xs font-semibold text-slate-500">{{ $order->goodsReceipts->count() }} kali pengiriman diterima</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-3">No. GR</th>
                            <th class="px-4 py-3">Waktu Penerimaan</th>
                            <th class="px-4 py-3">No. Surat Jalan</th>
                            <th class="px-4 py-3">No. Faktur</th>
                            <th class="px-4 py-3">Petugas Penerima</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($order->goodsReceipts as $gr)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap font-mono text-xs font-bold text-indigo-600">
                                    {{ $gr->receipt_number }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                                    {{ $gr->received_date->format('d/m/Y H:i') }} WIB
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-slate-800 font-semibold">
                                    {{ $gr->delivery_order_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-slate-800">
                                    {{ $gr->invoice_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700">
                                    {{ $gr->receiver->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('purchasing.receipts.show', $gr) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                                        Lihat Rincian &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs">
                                    Belum ada catatan penerimaan barang untuk Purchase Order ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Cancel Confirmation Modal -->
        <div x-show="cancelModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4" @click.away="cancelModalOpen = false">
                <h3 class="text-lg font-bold text-slate-900">Batalkan Purchase Order?</h3>
                <p class="text-xs text-slate-500">
                    Apakah Anda yakin ingin membatalkan Purchase Order <span class="font-mono font-bold">{{ $order->po_number }}</span>? Dokumen yang dibatalkan tidak dapat menerima pengiriman barang.
                </p>

                <form method="POST" action="{{ route('purchasing.orders.cancel', $order) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Alasan Pembatalan</label>
                        <input type="text" name="cancel_reason" placeholder="Contoh: Supplier kehabisan stok, salah input harga..." class="w-full rounded-xl border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500" required>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700">
                            Ya, Batalkan PO
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
