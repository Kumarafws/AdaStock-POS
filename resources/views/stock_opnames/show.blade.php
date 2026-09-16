<x-layouts.app :title="$opname->opname_number" header="Lembar Kerja Stock Opname">
    <div x-data="opnameSheet(@js([
        'opname_id' => $opname->id,
        'status' => $opname->status->value,
        'is_editable' => $opname->isEditable(),
        'items' => $opname->items->map(function($it) {
            return [
                'id' => $it->id,
                'product_id' => $it->product_id,
                'name' => $it->product->name,
                'sku' => $it->product->sku,
                'barcode' => $it->product->barcode,
                'category_name' => $it->product->category?->name,
                'unit_name' => $it->product->base_unit_name,
                'system_qty' => $it->system_qty,
                'physical_qty' => $it->physical_qty,
                'difference_qty' => $it->difference_qty,
                'unit_cost' => $it->unit_cost,
                'difference_amount' => $it->difference_amount,
                'notes' => $it->notes ?? '',
            ];
        })
    ]))" class="space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('opnames.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $opname->opname_number }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $opname->status->badgeClass() }}">
                            {{ $opname->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Lokasi: <span class="font-semibold text-slate-800">{{ $opname->location->name }} ({{ $opname->location->code }})</span>
                        &bull; Tanggal: {{ $opname->opname_date->format('d/m/Y') }}
                        &bull; Petugas: <span class="font-medium text-slate-700">{{ $opname->creator->name }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 shadow-2xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0014.25 1.5h-4.5A2.25 2.25 0 007.5 3.75v3.456" />
                    </svg>
                    Cetak Berita Acara
                </button>

                @if($opname->isEditable())
                    <button type="button" 
                            @click="saveCounts()"
                            :disabled="isSaving"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs shadow-2xs transition-colors">
                        <svg x-show="isSaving" class="animate-spin -ml-0.5 h-3.5 w-3.5 text-purple-700" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Draft Hitungan</span>
                    </button>

                    <button type="button" 
                            @click="isApproveModalOpen = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Setujui & Sesuaikan Stok</span>
                    </button>

                    <button type="button" 
                            @click="isCancelModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 font-bold text-xs transition-colors">
                        Batalkan
                    </button>
                @endif
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:grid-cols-4">
            <x-card class="p-4 border-l-4 border-l-purple-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Progress Penghitungan</span>
                <p class="text-xl font-black text-slate-900 mt-1">
                    <span x-text="countedCount"></span> / <span x-text="items.length"></span> item
                </p>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + (items.length ? (countedCount / items.length * 100) : 0) + '%'"></div>
                </div>
            </x-card>

            <x-card class="p-4 border-l-4 border-l-blue-500">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Fisik vs Sistem</span>
                <p class="text-xl font-black text-slate-900 mt-1">
                    <span x-text="totalPhysicalQty.toLocaleString('id-ID')"></span>
                    <span class="text-xs font-normal text-slate-400">/ <span x-text="totalSystemQty.toLocaleString('id-ID')"></span> unit</span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Hasil akumulasi hitungan fisik</p>
            </x-card>

            <x-card class="p-4 border-l-4" x-bind:class="totalVarianceQty < 0 ? 'border-l-rose-500' : (totalVarianceQty > 0 ? 'border-l-emerald-500' : 'border-l-slate-400')">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Net Selisih Kuantitas</span>
                <p class="text-xl font-black mt-1" :class="totalVarianceQty < 0 ? 'text-rose-600' : (totalVarianceQty > 0 ? 'text-emerald-600' : 'text-slate-900')">
                    <span x-text="(totalVarianceQty > 0 ? '+' : '') + totalVarianceQty.toLocaleString('id-ID') + ' unit'"></span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    <span x-text="deficitItemsCount"></span> kurang, <span x-text="surplusItemsCount"></span> lebih, <span x-text="matchItemsCount"></span> pas
                </p>
            </x-card>

            <x-card class="p-4 border-l-4" x-bind:class="totalVarianceAmount < 0 ? 'border-l-rose-500' : (totalVarianceAmount > 0 ? 'border-l-emerald-500' : 'border-l-slate-400')">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Deviasi Finansial (HPP)</span>
                <p class="text-xl font-black mt-1" :class="totalVarianceAmount < 0 ? 'text-rose-600' : (totalVarianceAmount > 0 ? 'text-emerald-600' : 'text-slate-900')">
                    <span x-text="formatRupiah(totalVarianceAmount)"></span>
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Nilai buku laba/rugi selisih</p>
            </x-card>
        </div>

        <!-- Filter & Search Sheet Bar -->
        <x-card class="p-4 print:hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama produk, SKU, atau scan barcode..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 pl-9 pr-4 py-2">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select x-model="filterStatus" class="rounded-xl border-slate-300 text-xs focus:border-purple-500 focus:ring-purple-500 py-2">
                        <option value="all">Semua Produk</option>
                        <option value="discrepancy">Hanya yang Selisih</option>
                        <option value="deficit">Selisih Kurang (-)</option>
                        <option value="surplus">Selisih Lebih (+)</option>
                        <option value="match">Hitungan Pas (Sesuai)</option>
                        <option value="uncounted">Belum Dihitung</option>
                    </select>

                    <template x-if="isEditable">
                        <button type="button" 
                                @click="fillAllWithSystem()" 
                                class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-colors">
                            Isi Semua Pas Sistem
                        </button>
                    </template>
                </div>
            </div>
        </x-card>

        <!-- Count Sheet Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">#</th>
                            <th class="px-6 py-3.5">Produk</th>
                            <th class="px-4 py-3.5 text-center">Satuan</th>
                            <th class="px-4 py-3.5 text-right">Stok Sistem</th>
                            <th class="px-6 py-3.5 text-center w-40">Hitungan Fisik</th>
                            <th class="px-4 py-3.5 text-right">Selisih Qty</th>
                            <th class="px-4 py-3.5 text-right">HPP (Moving Avg)</th>
                            <th class="px-4 py-3.5 text-right">Nilai Selisih</th>
                            <th class="px-6 py-3.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        <template x-for="(item, idx) in filteredItems" :key="item.id">
                            <tr class="hover:bg-slate-50/60 transition-colors" :class="getRowClass(item)">
                                <td class="px-4 py-3 text-center text-xs text-slate-400" x-text="idx + 1"></td>
                                
                                <td class="px-6 py-3">
                                    <div class="font-bold text-slate-900" x-text="item.name"></div>
                                    <div class="flex items-center gap-2 text-xs font-mono text-slate-400 mt-0.5">
                                        <span x-text="'SKU: ' + item.sku"></span>
                                        <template x-if="item.barcode">
                                            <span>&bull; <span x-text="item.barcode"></span></span>
                                        </template>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700" x-text="item.unit_name"></span>
                                </td>

                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-800" x-text="item.system_qty.toLocaleString('id-ID')"></td>

                                <!-- Physical Count Input -->
                                <td class="px-6 py-3 text-center">
                                    <template x-if="isEditable">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" 
                                                    @click="decrementQty(item)"
                                                    class="w-7 h-7 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600 font-bold flex items-center justify-center transition-colors shadow-2xs">
                                                -
                                            </button>
                                            <input type="number" 
                                                   x-model.number="item.physical_qty" 
                                                   @input="recalculateItem(item)"
                                                   min="0"
                                                   placeholder="-" 
                                                   class="w-18 rounded-lg border-slate-300 text-center font-bold text-slate-900 py-1 text-sm focus:border-purple-500 focus:ring-purple-500">
                                            <button type="button" 
                                                    @click="incrementQty(item)"
                                                    class="w-7 h-7 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600 font-bold flex items-center justify-center transition-colors shadow-2xs">
                                                +
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!isEditable">
                                        <span class="font-mono font-bold text-slate-900" x-text="item.physical_qty !== null ? item.physical_qty.toLocaleString('id-ID') : '-'"></span>
                                    </template>
                                </td>

                                <!-- Variance Qty Badge -->
                                <td class="px-4 py-3 text-right font-mono font-bold whitespace-nowrap">
                                    <template x-if="item.physical_qty === null">
                                        <span class="text-xs text-slate-400 font-normal italic">Belum hitung</span>
                                    </template>
                                    <template x-if="item.physical_qty !== null">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold border" :class="getDiffBadgeClass(item.difference_qty)">
                                            <span x-text="(item.difference_qty > 0 ? '+' : '') + item.difference_qty.toLocaleString('id-ID')"></span>
                                        </span>
                                    </template>
                                </td>

                                <td class="px-4 py-3 text-right font-mono text-xs text-slate-600" x-text="formatRupiah(item.unit_cost)"></td>

                                <!-- Difference Amount -->
                                <td class="px-4 py-3 text-right font-mono font-bold text-xs whitespace-nowrap" :class="item.difference_amount < 0 ? 'text-rose-600' : (item.difference_amount > 0 ? 'text-emerald-600' : 'text-slate-500')">
                                    <span x-text="item.physical_qty !== null ? formatRupiah(item.difference_amount) : '-'"></span>
                                </td>

                                <!-- Notes -->
                                <td class="px-6 py-3">
                                    <template x-if="isEditable">
                                        <input type="text" 
                                               x-model="item.notes" 
                                               placeholder="Catatan..." 
                                               class="w-full rounded-lg border-slate-200 text-xs focus:border-purple-500 focus:ring-purple-500 py-1 px-2">
                                    </template>
                                    <template x-if="!isEditable">
                                        <span class="text-xs text-slate-600" x-text="item.notes || '-'"></span>
                                    </template>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredItems.length === 0">
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-slate-400 text-xs">
                                    Tidak ada produk yang sesuai dengan pencarian / filter.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Audit Movements Table (Visible when completed) -->
        @if($opname->isCompleted())
            <x-card class="overflow-hidden print:hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        Buku Besar Mutasi Stok Penyesuaian (Ledger Audit)
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">Disetujui oleh: {{ $opname->approver?->name ?? 'Admin' }} pada {{ $opname->completed_at?->format('d/m/Y H:i') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3 text-right">Saldo Sistem Lama</th>
                                <th class="px-4 py-3 text-right">Mutasi Penyesuaian</th>
                                <th class="px-4 py-3 text-right">Saldo Fisik Baru</th>
                                <th class="px-6 py-3">Tipe Mutasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($opname->movements as $mov)
                                <tr>
                                    <td class="px-6 py-3 font-semibold text-slate-900">{{ $mov->product->name }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $mov->location->code }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-500">{{ $mov->balance_before }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold {{ $mov->quantity < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ $mov->balance_after }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $mov->movement_type->badgeClass() }}">
                                            {{ $mov->movement_type->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-slate-400 italic">
                                        Tidak ada mutasi yang dihasilkan (semua stok fisik cocok 100% dengan saldo sistem).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

        <!-- Printable Document Section (Visible when printing) -->
        <div class="hidden print:block font-sans text-xs space-y-4">
            <div class="text-center pb-3 border-b-2 border-slate-900">
                <h1 class="text-lg font-black uppercase tracking-wider">BERITA ACARA HASIL STOCK OPNAME</h1>
                <p class="text-xs text-slate-600">AdaStock Multi-Store & Inventory Management System</p>
                <div class="flex justify-between items-center mt-3 text-[11px] font-mono">
                    <span>No. Dokumen: <strong>{{ $opname->opname_number }}</strong></span>
                    <span>Lokasi: <strong>[{{ $opname->location->code }}] {{ $opname->location->name }}</strong></span>
                    <span>Tanggal: <strong>{{ $opname->opname_date->format('d/m/Y') }}</strong></span>
                </div>
            </div>

            <table class="w-full text-left text-xs border border-slate-400 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-400 font-bold">
                        <th class="p-2 border-r border-slate-400">No</th>
                        <th class="p-2 border-r border-slate-400">SKU / Produk</th>
                        <th class="p-2 border-r border-slate-400 text-center">Satuan</th>
                        <th class="p-2 border-r border-slate-400 text-right">Sistem</th>
                        <th class="p-2 border-r border-slate-400 text-right">Fisik</th>
                        <th class="p-2 border-r border-slate-400 text-right">Selisih</th>
                        <th class="p-2 border-r border-slate-400 text-right">HPP</th>
                        <th class="p-2 border-r border-slate-400 text-right">Nilai Selisih</th>
                        <th class="p-2">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opname->items as $idx => $it)
                        <tr class="border-b border-slate-300">
                            <td class="p-1.5 border-r border-slate-300 text-center">{{ $idx + 1 }}</td>
                            <td class="p-1.5 border-r border-slate-300">{{ $it->product->name }} ({{ $it->product->sku }})</td>
                            <td class="p-1.5 border-r border-slate-300 text-center">{{ $it->product->base_unit_name }}</td>
                            <td class="p-1.5 border-r border-slate-300 text-right font-mono">{{ $it->system_qty }}</td>
                            <td class="p-1.5 border-r border-slate-300 text-right font-mono font-bold">{{ $it->physical_qty ?? '-' }}</td>
                            <td class="p-1.5 border-r border-slate-300 text-right font-mono font-bold">{{ $it->difference_qty > 0 ? '+' : '' }}{{ $it->difference_qty }}</td>
                            <td class="p-1.5 border-r border-slate-300 text-right font-mono">{{ number_format($it->unit_cost, 0, ',', '.') }}</td>
                            <td class="p-1.5 border-r border-slate-300 text-right font-mono font-bold">{{ number_format($it->difference_amount, 0, ',', '.') }}</td>
                            <td class="p-1.5 text-[10px]">{{ $it->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold border-t-2 border-slate-400">
                        <td colspan="3" class="p-2 text-right">TOTAL:</td>
                        <td class="p-2 text-right font-mono">{{ number_format($opname->total_system_qty) }}</td>
                        <td class="p-2 text-right font-mono">{{ number_format($opname->total_physical_qty) }}</td>
                        <td class="p-2 text-right font-mono">{{ $opname->total_variance_qty > 0 ? '+' : '' }}{{ number_format($opname->total_variance_qty) }}</td>
                        <td></td>
                        <td class="p-2 text-right font-mono">{{ $opname->formatted_variance_amount }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Signatures -->
            <div class="grid grid-cols-3 gap-8 pt-8 text-center text-xs">
                <div>
                    <p>Petugas Penghitung Fisik,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">{{ $opname->creator->name }}</p>
                </div>
                <div>
                    <p>Supervisor / Saksi,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">(............................)</p>
                </div>
                <div>
                    <p>Penyetuju / Kepala Toko,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">{{ $opname->approver?->name ?? '(............................)' }}</p>
                </div>
            </div>
        </div>

        <!-- Approve Modal Confirmation -->
        @if($opname->isEditable())
            <div x-show="isApproveModalOpen" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.away="isApproveModalOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Konfirmasi Penyesuaian Saldo Stok</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Tindakan ini akan menyetujui hasil hitungan fisik dan <strong>menyesuaikan saldo buku besar inventori secara permanen</strong> persis sesuai data fisik lapangan.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Net Selisih Fisik:</span>
                            <span class="font-bold" :class="totalVarianceQty < 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="(totalVarianceQty > 0 ? '+' : '') + totalVarianceQty.toLocaleString('id-ID') + ' unit'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Deviasi Finansial (HPP):</span>
                            <span class="font-bold" :class="totalVarianceAmount < 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="formatRupiah(totalVarianceAmount)"></span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('opnames.complete', $opname) }}" class="flex items-center justify-end gap-2 pt-2">
                        @csrf
                        <button type="button" @click="isApproveModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-600/20">
                            Ya, Setujui & Sesuaikan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cancel Modal Confirmation -->
            <div x-show="isCancelModalOpen" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.away="isCancelModalOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Batalkan Sesi Stock Opname</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Sesi yang dibatalkan tidak akan memengaruhi saldo buku besar stok.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('opnames.cancel', $opname) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan</label>
                            <input type="text" name="reason" required placeholder="Contoh: Kesalahan pemilihan lokasi..." class="w-full rounded-xl border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500 py-2">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="isCancelModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                Kembali
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm shadow-rose-600/20">
                                Batalkan Sesi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>

    @push('scripts')
    <script>
        function opnameSheet(config) {
            return {
                opnameId: config.opname_id,
                isEditable: config.is_editable,
                items: config.items || [],
                searchQuery: '',
                filterStatus: 'all',
                isSaving: false,
                isApproveModalOpen: false,
                isCancelModalOpen: false,

                get filteredItems() {
                    let list = this.items;

                    // Filter search query
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase().trim();
                        list = list.filter(it => 
                            it.name.toLowerCase().includes(q) || 
                            it.sku.toLowerCase().includes(q) || 
                            (it.barcode && it.barcode.toLowerCase().includes(q))
                        );
                    }

                    // Filter status
                    if (this.filterStatus === 'discrepancy') {
                        list = list.filter(it => it.physical_qty !== null && it.difference_qty !== 0);
                    } else if (this.filterStatus === 'deficit') {
                        list = list.filter(it => it.physical_qty !== null && it.difference_qty < 0);
                    } else if (this.filterStatus === 'surplus') {
                        list = list.filter(it => it.physical_qty !== null && it.difference_qty > 0);
                    } else if (this.filterStatus === 'match') {
                        list = list.filter(it => it.physical_qty !== null && it.difference_qty === 0);
                    } else if (this.filterStatus === 'uncounted') {
                        list = list.filter(it => it.physical_qty === null);
                    }

                    return list;
                },

                get countedCount() {
                    return this.items.filter(it => it.physical_qty !== null).length;
                },

                get totalSystemQty() {
                    return this.items.reduce((sum, it) => sum + (it.system_qty || 0), 0);
                },

                get totalPhysicalQty() {
                    return this.items
                        .filter(it => it.physical_qty !== null)
                        .reduce((sum, it) => sum + (Number(it.physical_qty) || 0), 0);
                },

                get totalVarianceQty() {
                    return this.items
                        .filter(it => it.physical_qty !== null)
                        .reduce((sum, it) => sum + (it.difference_qty || 0), 0);
                },

                get totalVarianceAmount() {
                    return this.items
                        .filter(it => it.physical_qty !== null)
                        .reduce((sum, it) => sum + (it.difference_amount || 0), 0);
                },

                get deficitItemsCount() {
                    return this.items.filter(it => it.physical_qty !== null && it.difference_qty < 0).length;
                },

                get surplusItemsCount() {
                    return this.items.filter(it => it.physical_qty !== null && it.difference_qty > 0).length;
                },

                get matchItemsCount() {
                    return this.items.filter(it => it.physical_qty !== null && it.difference_qty === 0).length;
                },

                incrementQty(item) {
                    if (item.physical_qty === null) {
                        item.physical_qty = item.system_qty + 1;
                    } else {
                        item.physical_qty++;
                    }
                    this.recalculateItem(item);
                },

                decrementQty(item) {
                    if (item.physical_qty === null) {
                        item.physical_qty = Math.max(0, item.system_qty - 1);
                    } else if (item.physical_qty > 0) {
                        item.physical_qty--;
                    }
                    this.recalculateItem(item);
                },

                recalculateItem(item) {
                    if (item.physical_qty === '' || item.physical_qty === null) {
                        item.physical_qty = null;
                        item.difference_qty = null;
                        item.difference_amount = 0;
                        return;
                    }
                    item.difference_qty = item.physical_qty - item.system_qty;
                    item.difference_amount = Math.round(item.difference_qty * item.unit_cost);
                },

                fillAllWithSystem() {
                    if (!confirm('Isi semua produk yang belum dihitung dengan angka saldo sistem?')) return;
                    this.items.forEach(it => {
                        if (it.physical_qty === null) {
                            it.physical_qty = it.system_qty;
                            it.difference_qty = 0;
                            it.difference_amount = 0;
                        }
                    });
                },

                getRowClass(item) {
                    if (item.physical_qty === null) return '';
                    if (item.difference_qty < 0) return 'bg-rose-50/25';
                    if (item.difference_qty > 0) return 'bg-emerald-50/25';
                    return '';
                },

                getDiffBadgeClass(diff) {
                    if (diff < 0) return 'bg-rose-50 text-rose-700 border-rose-200';
                    if (diff > 0) return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    return 'bg-slate-100 text-slate-700 border-slate-200';
                },

                formatRupiah(val) {
                    const rounded = Math.round(val || 0);
                    const prefix = rounded > 0 ? '+Rp ' : (rounded < 0 ? '-Rp ' : 'Rp ');
                    return prefix + Math.abs(rounded).toLocaleString('id-ID');
                },

                async saveCounts() {
                    this.isSaving = true;

                    const payload = {
                        counts: this.items
                            .filter(it => it.physical_qty !== null)
                            .map(it => ({
                                item_id: it.id,
                                physical_qty: it.physical_qty,
                                notes: it.notes
                            }))
                    };

                    try {
                        const response = await fetch(`{{ route('opnames.update-counts', $opname) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            alert(data.message || 'Gagal menyimpan hitungan fisik.');
                            return;
                        }

                        alert('Hitungan fisik berhasil disimpan.');
                    } catch (e) {
                        alert('Terjadi kesalahan sistem saat menyimpan hitungan fisik.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
