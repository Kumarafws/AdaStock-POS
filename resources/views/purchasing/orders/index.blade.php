<x-layouts.app title="Purchase Orders" header="Pengadaan Barang (PO)">
    <div class="space-y-6">

        <!-- Top Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pesanan Pembelian (Purchase Orders)</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Kelola pengadaan barang dari supplier distributor ke gudang pusat atau toko cabang.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.receipts.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.125c.621 0 1.125.504 1.125 1.125v3.75m-6.75-4.875H6a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25h1.5" />
                    </svg>
                    Riwayat Penerimaan (GR)
                </a>

                <a href="{{ route('purchasing.orders.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    + Buat Purchase Order
                </a>
            </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pesanan (PO)</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">{{ number_format($summary['total_orders']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Seluruh dokumen pesanan tercatat</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">PO Aktif / Berjalan</p>
                        <p class="text-2xl font-extrabold text-blue-600 mt-1 tabular-nums">{{ number_format($summary['active_orders']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Menunggu kiriman supplier</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pengadaan (Rp)</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">Rp {{ number_format($summary['total_spend'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Akumulasi nilai pesanan tidak batal</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">PO Selesai Diterima</p>
                        <p class="text-2xl font-extrabold text-emerald-600 mt-1 tabular-nums">{{ number_format($summary['received_orders']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Barang telah 100% diterima</p>
            </x-card>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('purchasing.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <!-- Supplier Filter -->
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

                <!-- Location Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi Tujuan</label>
                    <select name="destination_location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('destination_location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status PO</label>
                    <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pencarian</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="No. PO, nama supplier, catatan..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <!-- Buttons -->
                <div class="lg:col-span-1 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Orders Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. PO</th>
                            <th class="px-4 py-4">Tanggal Pesan</th>
                            <th class="px-6 py-4">Supplier</th>
                            <th class="px-4 py-4">Lokasi Tujuan</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Progress Penerimaan</th>
                            <th class="px-6 py-4 text-right">Total Nilai (Rp)</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($orders as $po)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- PO Number -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('purchasing.orders.show', $po) }}" class="font-mono text-sm font-bold text-indigo-600 hover:underline">
                                        {{ $po->po_number }}
                                    </a>
                                </td>

                                <!-- Order Date -->
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500">
                                    <div class="font-bold text-slate-800">{{ $po->order_date->format('d/m/Y') }}</div>
                                    @if($po->expected_delivery_date)
                                        <div class="text-[11px] text-slate-400 mt-0.5">Est: {{ $po->expected_delivery_date->format('d/m/Y') }}</div>
                                    @endif
                                </td>

                                <!-- Supplier -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $po->supplier->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $po->supplier->code }}</div>
                                </td>

                                <!-- Destination Location -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $po->destinationLocation->type->value === 'warehouse' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        {{ $po->destinationLocation->code }}
                                    </span>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ $po->destinationLocation->name }}</div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $po->status->badgeClass() }}">
                                        {{ $po->status->label() }}
                                    </span>
                                </td>

                                <!-- Receiving Progress -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-slate-200 rounded-full h-2 max-w-[120px] overflow-hidden">
                                            <div class="h-2 rounded-full {{ $po->receipt_progress == 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}" style="width: {{ $po->receipt_progress }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700 tabular-nums">{{ $po->receipt_progress }}%</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $po->items->sum('received_quantity_base') }} / {{ $po->items->sum('ordered_quantity_base') }} Base Unit
                                    </div>
                                </td>

                                <!-- Total Amount -->
                                <td class="px-6 py-4 text-right whitespace-nowrap tabular-nums">
                                    <div class="font-extrabold text-slate-900">
                                        Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $po->items->count() }} jenis barang</div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('purchasing.orders.show', $po) }}" 
                                           class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors shadow-2xs">
                                            Detail
                                        </a>

                                        @if($po->status->canReceive())
                                            <a href="{{ route('purchasing.receipts.create', ['po_id' => $po->id]) }}" 
                                               class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-xs font-semibold text-white transition-colors shadow-xs">
                                                Terima Barang
                                            </a>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada dokumen Purchase Order</p>
                                    <p class="text-xs text-slate-500 mt-1">Klik tombol "+ Buat Purchase Order" untuk memulai pesanan ke supplier.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
