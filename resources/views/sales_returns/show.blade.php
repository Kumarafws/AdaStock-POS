<x-layouts.app :title="$salesReturn->return_number" header="Detail Retur Penjualan">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('returns.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $salesReturn->return_number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Retur Pelanggan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Diproses oleh <span class="font-semibold text-slate-700">{{ $salesReturn->cashier->name }}</span> pada {{ $salesReturn->return_date->format('d/m/Y H:i') }} WIB
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0014.25 1.5h-4.5A2.25 2.25 0 007.5 3.75v3.456" />
                    </svg>
                    Cetak Bukti Retur
                </button>

                <a href="{{ route('returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-600 text-white font-semibold text-sm hover:bg-amber-700 shadow-sm shadow-amber-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Retur Baru
                </a>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 print:grid-cols-3">
            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ref. Faktur Penjualan:</span>
                <p class="text-base font-bold font-mono text-slate-900 mt-1">{{ $salesReturn->sale->sale_number }}</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pelanggan: <span class="font-medium text-slate-700">{{ $salesReturn->sale->customer_name ?: 'Pelanggan Umum' }}</span>
                </p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $salesReturn->sale->status->badgeClass() }}">
                        Status Faktur: {{ $salesReturn->sale->status->label() }}
                    </span>
                </div>
            </x-card>

            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Metode & Total Refund:</span>
                <p class="text-xl font-black text-rose-600 mt-1">{{ $salesReturn->formatted_total_refund }}</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $salesReturn->refund_method->badgeClass() }}">
                        {{ $salesReturn->refund_method->label() }}
                    </span>
                </div>
                @if($salesReturn->shift)
                    <p class="text-[11px] text-slate-500 mt-2">
                        Shift: <span class="font-mono font-medium text-slate-700">{{ $salesReturn->shift->shift_number }}</span>
                    </p>
                @endif
            </x-card>

            <x-card class="p-5">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Alasan & Catatan:</span>
                <p class="text-sm font-bold text-slate-900 mt-1">{{ $salesReturn->reason }}</p>
                @if($salesReturn->notes)
                    <p class="text-xs text-slate-500 mt-1 italic">"{{ $salesReturn->notes }}"</p>
                @else
                    <p class="text-xs text-slate-400 mt-1">Tidak ada catatan tambahan</p>
                @endif
            </x-card>
        </div>

        <!-- Returned Items Table -->
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Rincian Barang Yang Diretur</h3>
                <span class="text-xs font-semibold text-slate-500">{{ $salesReturn->items->count() }} item barang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Item Produk</th>
                            <th class="px-4 py-4 text-center">Satuan</th>
                            <th class="px-4 py-4 text-center">Kuantitas</th>
                            <th class="px-4 py-4 text-right">Harga Satuan</th>
                            <th class="px-6 py-4 text-center">Kondisi Barang</th>
                            <th class="px-6 py-4">Lokasi Tujuan Fisik</th>
                            <th class="px-6 py-4 text-right">Subtotal Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @foreach($salesReturn->items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                                    <div class="text-xs font-mono text-slate-400">SKU: {{ $item->product->sku }}</div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $item->unit_name }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center font-bold text-slate-800">
                                    {{ $item->quantity }}
                                    @if($item->conversion_factor > 1)
                                        <span class="text-[10px] text-slate-400 block">({{ $item->quantity_base }} {{ $item->product->base_unit_name }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right font-medium text-slate-700">
                                    {{ $item->formatted_unit_price }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $item->condition->badgeClass() }}">
                                        {{ $item->condition->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->condition->isDamaged())
                                        <div class="flex items-center gap-1.5 text-rose-700 font-semibold text-xs">
                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                            <span>{{ $item->destinationLocation->name }} ({{ $item->destinationLocation->code }})</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-semibold text-xs">
                                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ $item->destinationLocation->name }} ({{ $item->destinationLocation->code }})</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900">
                                    {{ $item->formatted_refund_amount }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/80 border-t-2 border-slate-200">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-right font-bold text-slate-700 uppercase tracking-wider text-xs">
                                Total Pengembalian Dana (Refund):
                            </td>
                            <td class="px-6 py-4 text-right font-black text-rose-600 text-lg">
                                {{ $salesReturn->formatted_total_refund }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>

        <!-- Stock Movements Audit Log Card (Admin & Manager) -->
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <x-card class="overflow-hidden print:hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        Audit Mutasi Stok (Ledger)
                    </h3>
                    <span class="text-xs font-semibold text-slate-500">Otomatis dicatat oleh sistem</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-4 py-3">Lokasi Masuk</th>
                                <th class="px-4 py-3 text-right">Saldo Awal</th>
                                <th class="px-4 py-3 text-right">Mutasi Masuk (+)</th>
                                <th class="px-4 py-3 text-right">Saldo Akhir</th>
                                <th class="px-6 py-3">Tipe Mutasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($salesReturn->movements as $mov)
                                <tr>
                                    <td class="px-6 py-3 font-semibold text-slate-800">{{ $mov->product->name }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $mov->location->code }} - {{ $mov->location->name }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-500">{{ $mov->balance_before }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600">+{{ $mov->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ $mov->balance_after }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $mov->movement_type->badgeClass() }}">
                                            {{ $mov->movement_type->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

        <!-- Printable Slip Section (Formatted specifically for printing) -->
        <div class="hidden print:block font-mono text-xs max-w-sm mx-auto p-4 border border-dashed border-slate-300">
            <div class="text-center pb-3 border-b border-dashed border-slate-400">
                <h2 class="text-sm font-bold uppercase tracking-wider">AdaStock Retail POS</h2>
                <p class="text-[10px] text-slate-500">BUKTI RETUR PENJUALAN</p>
                <p class="font-bold mt-1 text-xs">{{ $salesReturn->return_number }}</p>
                <p class="text-[10px]">{{ $salesReturn->return_date->format('d/m/Y H:i') }}</p>
            </div>

            <div class="py-2 text-[10px] border-b border-dashed border-slate-400 space-y-0.5">
                <div class="flex justify-between">
                    <span>No. Faktur:</span>
                    <span class="font-bold">{{ $salesReturn->sale->sale_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Pelanggan:</span>
                    <span>{{ $salesReturn->sale->customer_name ?: 'Pelanggan Umum' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Petugas:</span>
                    <span>{{ $salesReturn->cashier->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Metode Refund:</span>
                    <span class="font-bold">{{ $salesReturn->refund_method->label() }}</span>
                </div>
            </div>

            <div class="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-1.5">
                @foreach($salesReturn->items as $item)
                    <div>
                        <div class="font-bold">{{ $item->product->name }}</div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ $item->quantity }} x {{ $item->formatted_unit_price }} [{{ $item->condition->label() }}]</span>
                            <span>{{ $item->formatted_refund_amount }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="py-2 border-b border-dashed border-slate-400 text-[11px] space-y-1">
                <div class="flex justify-between font-bold">
                    <span>TOTAL REFUND:</span>
                    <span>{{ $salesReturn->formatted_total_refund }}</span>
                </div>
                <div class="text-[10px] text-slate-500">
                    Alasan: {{ $salesReturn->reason }}
                </div>
            </div>

            <div class="text-center pt-3 text-[10px] text-slate-500">
                <p>Terima kasih atas kunjungan Anda.</p>
                <p>AdaStock Point of Sale</p>
            </div>
        </div>

    </div>
</x-layouts.app>
