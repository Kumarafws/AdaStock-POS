<x-layouts.app title="Penerimaan Barang" header="Penerimaan Barang (Goods Receipt)">
    <div class="space-y-6">

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Berita Acara Penerimaan Barang (Goods Receipt)</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Catatan fisik penerimaan pengiriman barang dari supplier beserta pembaruan HPP rata-rata.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.orders.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Daftar PO
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('purchasing.receipts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <!-- Location Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi Penerimaan</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <!-- Search Input -->
                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pencarian</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="No. GR, No. PO, No. Surat Jalan, Faktur..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <!-- Button -->
                <div class="lg:col-span-1 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Receipts Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Penerimaan (GR)</th>
                            <th class="px-4 py-4">Waktu Diterima</th>
                            <th class="px-4 py-4">Referensi PO</th>
                            <th class="px-6 py-4">Supplier</th>
                            <th class="px-4 py-4">Lokasi Bongkar</th>
                            <th class="px-4 py-4">Surat Jalan</th>
                            <th class="px-4 py-4">No. Faktur</th>
                            <th class="px-4 py-4">Penerima</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($receipts as $receipt)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('purchasing.receipts.show', $receipt) }}" class="font-mono text-xs font-bold text-indigo-600 hover:underline">
                                        {{ $receipt->receipt_number }}
                                    </a>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500">
                                    <div class="font-bold text-slate-800">{{ $receipt->received_date->format('d/m/Y') }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $receipt->received_date->format('H:i') }} WIB</div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <a href="{{ route('purchasing.orders.show', $receipt->purchaseOrder) }}" class="font-mono text-xs font-semibold text-slate-700 hover:text-indigo-600">
                                        {{ $receipt->purchaseOrder->po_number }}
                                    </a>
                                </td>

                                <td class="px-6 py-4 font-bold text-slate-900">
                                    {{ $receipt->purchaseOrder->supplier->name }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $receipt->location->type->value === 'warehouse' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        {{ $receipt->location->code }}
                                    </span>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ $receipt->location->name }}</div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap font-mono text-xs text-slate-800 font-semibold">
                                    {{ $receipt->delivery_order_number ?? '-' }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap font-mono text-xs text-slate-800">
                                    {{ $receipt->invoice_number ?? '-' }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700">
                                    {{ $receipt->receiver->name ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('purchasing.receipts.show', $receipt) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors shadow-2xs">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.125c.621 0 1.125.504 1.125 1.125v3.75m-6.75-4.875H6a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25h1.5" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada dokumen penerimaan barang</p>
                                    <p class="text-xs text-slate-500 mt-1">Penerimaan barang dicatat melalui halaman detail Purchase Order.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($receipts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $receipts->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
