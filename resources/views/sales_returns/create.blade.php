<x-layouts.app title="Proses Retur Penjualan Pelanggan" header="Proses Retur Penjualan">
    <div x-data="salesReturnForm(@js($initialSale ? [
        'id' => $initialSale->id,
        'sale_number' => $initialSale->sale_number,
        'customer_name' => $initialSale->customer_name ?: 'Pelanggan Umum',
        'transaction_date' => $initialSale->transaction_date->format('d/m/Y H:i'),
        'total_amount' => $initialSale->total_amount,
        'formatted_total' => $initialSale->formatted_total,
        'status' => $initialSale->status->value,
        'status_label' => $initialSale->status->label(),
        'status_badge' => $initialSale->status->badgeClass(),
        'location_name' => $initialSale->location?->name ?? 'Toko',
        'items' => $initialSale->items->map(function($it) {
            $alreadyReturned = (int) $it->returnItems->sum('quantity');
            $remaining = max(0, $it->quantity - $alreadyReturned);
            $netUnitPrice = $it->quantity > 0 ? round($it->subtotal / $it->quantity, 2) : $it->unit_price;
            return [
                'sale_item_id' => $it->id,
                'product_id' => $it->product_id,
                'product_name' => $it->product->name,
                'sku' => $it->product->sku,
                'unit_name' => $it->unit_name,
                'quantity_purchased' => $it->quantity,
                'quantity_returned' => $alreadyReturned,
                'quantity_remaining' => $remaining,
                'unit_price' => $it->unit_price,
                'net_unit_price' => $netUnitPrice,
                'subtotal' => $it->subtotal,
                'is_returnable' => $remaining > 0,
            ];
        })
    ] : null))" class="space-y-6">

        <!-- Top Header Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('returns.index') }}" class="p-2 rounded-xl border border-slate-200 bg-white text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Buat Retur Penjualan Baru</h2>
                    <p class="text-sm text-slate-500">Pilih faktur transaksi, tentukan kuantitas barang diretur, dan klasifikasikan fisik barang.</p>
                </div>
            </div>

            @if($activeShift)
                <div class="flex items-center gap-3 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <div>
                        <span>Shift Aktif: <strong class="font-mono">{{ $activeShift->shift_number }}</strong></span>
                        <span class="mx-1.5 text-emerald-300">|</span>
                        <span>Kas Tunai: <strong>Rp {{ number_format($activeShift->total_sales_cash, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>Tidak ada shift kasir terbuka. Pengembalian uang tunai membutuhkan shift aktif.</span>
                </div>
            @endif
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Lookup Form -->
        <x-card class="p-5">
            <div class="max-w-xl">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Cari Nomor Faktur Penjualan (Invoice)
                </label>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" 
                               x-model="searchQuery" 
                               @keydown.enter.prevent="lookupSale()"
                               placeholder="Contoh: INV/20260915/0001 atau ID Transaksi..." 
                               class="w-full rounded-xl border-slate-300 text-sm font-mono focus:border-amber-500 focus:ring-amber-500 py-2.5 pl-3 pr-10">
                        <button type="button" 
                                x-show="searchQuery" 
                                @click="searchQuery = ''"
                                class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                            &times;
                        </button>
                    </div>
                    <button type="button" 
                            @click="lookupSale()"
                            :disabled="isLoading || !searchQuery.trim()"
                            class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50 flex items-center gap-2 shadow-xs">
                        <svg x-show="isLoading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Cari Faktur</span>
                    </button>
                </div>
                <p x-show="errorMessage" x-text="errorMessage" class="text-xs text-rose-600 font-medium mt-2"></p>
            </div>
        </x-card>

        <!-- Sale Details & Return Configuration (Visible when sale is loaded) -->
        <div x-show="sale" class="space-y-6" style="display: none;">

            <!-- Invoice Header Card -->
            <x-card class="p-5 bg-gradient-to-r from-slate-50 to-amber-50/30 border-amber-200/60">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-amber-500/30">
                            #
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold font-mono text-slate-900" x-text="sale?.sale_number"></h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border"
                                      :class="sale?.status_badge"
                                      x-text="sale?.status_label"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Tanggal: <span class="font-medium text-slate-700" x-text="sale?.transaction_date"></span>
                                &bull; Pelanggan: <span class="font-medium text-slate-700" x-text="sale?.customer_name"></span>
                                &bull; Lokasi: <span class="font-medium text-slate-700" x-text="sale?.location_name"></span>
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Total Belanja Asli</p>
                        <p class="text-xl font-black text-slate-900" x-text="sale?.formatted_total"></p>
                    </div>
                </div>
            </x-card>

            <!-- Return Processing Form -->
            <form @submit.prevent="submitReturn()">
                <div class="space-y-6">

                    <!-- Items Selection Table -->
                    <x-card class="overflow-hidden">
                        <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Daftar Barang yang Diretur</h3>
                                <p class="text-xs text-slate-500">Tentukan jumlah barang yang dikembalikan dan pilih apakah barang layak jual atau rusak.</p>
                            </div>
                            <button type="button" 
                                    @click="returnAllItems()" 
                                    class="text-xs font-semibold text-amber-600 hover:text-amber-800 hover:underline">
                                Pilih Semua Sisa Barang
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3.5">Produk</th>
                                        <th class="px-4 py-3.5 text-center">Satuan</th>
                                        <th class="px-4 py-3.5 text-center">Dibeli</th>
                                        <th class="px-4 py-3.5 text-center">Sudah Retur</th>
                                        <th class="px-4 py-3.5 text-center">Sisa Bisa Retur</th>
                                        <th class="px-6 py-3.5 text-center w-36">Jumlah Retur</th>
                                        <th class="px-6 py-3.5">Kondisi Fisik Barang</th>
                                        <th class="px-6 py-3.5 text-right">Nilai Refund</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70">
                                    <template x-for="(item, index) in returnItems" :key="item.sale_item_id">
                                        <tr class="hover:bg-slate-50/50 transition-colors" :class="item.quantity > 0 ? 'bg-amber-50/20' : ''">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-slate-900" x-text="item.product_name"></div>
                                                <div class="text-xs font-mono text-slate-400" x-text="'SKU: ' + item.sku"></div>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700" x-text="item.unit_name"></span>
                                            </td>
                                            <td class="px-4 py-4 text-center font-semibold text-slate-700" x-text="item.quantity_purchased"></td>
                                            <td class="px-4 py-4 text-center font-medium text-slate-500" x-text="item.quantity_returned"></td>
                                            <td class="px-4 py-4 text-center font-bold text-indigo-600" x-text="item.quantity_remaining"></td>
                                            
                                            <!-- Quantity Input -->
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-1.5" x-show="item.is_returnable">
                                                    <button type="button" 
                                                            @click="decrementQty(index)"
                                                            class="w-7 h-7 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600 font-bold flex items-center justify-center transition-colors shadow-2xs">
                                                        -
                                                    </button>
                                                    <input type="number" 
                                                           x-model.number="item.quantity"
                                                           @input="validateQty(index)"
                                                           min="0" 
                                                           :max="item.quantity_remaining" 
                                                           class="w-16 rounded-lg border-slate-300 text-center font-bold text-slate-900 py-1 text-sm focus:border-amber-500 focus:ring-amber-500">
                                                    <button type="button" 
                                                            @click="incrementQty(index)"
                                                            class="w-7 h-7 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600 font-bold flex items-center justify-center transition-colors shadow-2xs">
                                                        +
                                                    </button>
                                                </div>
                                                <span x-show="!item.is_returnable" class="text-xs font-semibold text-slate-400">
                                                    Sudah Habis
                                                </span>
                                            </td>

                                            <!-- Physical Condition Selector -->
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col gap-1" x-show="item.quantity > 0">
                                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                                        <input type="radio" 
                                                               :name="'condition_' + index" 
                                                               value="good" 
                                                               x-model="item.condition"
                                                               class="text-emerald-600 focus:ring-emerald-500">
                                                        <span class="font-semibold text-emerald-700">Layak Jual</span>
                                                        <span class="text-[10px] text-slate-400">(Masuk Stok Toko)</span>
                                                    </label>
                                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs">
                                                        <input type="radio" 
                                                               :name="'condition_' + index" 
                                                               value="damaged" 
                                                               x-model="item.condition"
                                                               class="text-rose-600 focus:ring-rose-500">
                                                        <span class="font-semibold text-rose-700">Rusak / Cacat</span>
                                                        <span class="text-[10px] text-slate-400">(Masuk Karantina QRN-01)</span>
                                                    </label>
                                                </div>
                                                <span x-show="item.quantity === 0" class="text-xs text-slate-400">-</span>
                                            </td>

                                            <!-- Subtotal Refund -->
                                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                                <span x-text="formatRupiah(item.quantity * item.net_unit_price)"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </x-card>

                    <!-- Refund & Reason Configuration Form -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Left / Center: Details & Reason -->
                        <x-card class="p-5 lg:col-span-2 space-y-4">
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Detail Pengembalian</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Metode Pengembalian Dana <span class="text-rose-500">*</span>
                                    </label>
                                    <select x-model="refundMethod" class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 py-2.5">
                                        @foreach($refundMethods as $method)
                                            <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[11px] text-slate-500 mt-1" x-show="refundMethod === 'cash'">
                                        Pengembalian tunai akan otomatis memotong saldo fisik kasir shift saat ini.
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                        Alasan Retur <span class="text-rose-500">*</span>
                                    </label>
                                    <select x-model="reason" class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 py-2.5">
                                        <option value="">-- Pilih Alasan --</option>
                                        <option value="Kemasan Rusak / Bocor">Kemasan Rusak / Bocor</option>
                                        <option value="Barang Cacat Produksi">Barang Cacat Produksi</option>
                                        <option value="Salah Beli / Tidak Cocok">Salah Beli / Tidak Cocok</option>
                                        <option value="Mendekati / Lewat Tanggal Kedaluwarsa">Mendekati / Lewat Kedaluwarsa</option>
                                        <option value="Lainnya">Lainnya (Tulis di Catatan)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                    Catatan Tambahan
                                </label>
                                <textarea x-model="notes" rows="3" placeholder="Tambahkan keterangan pendukung retur jika diperlukan..." class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 p-3"></textarea>
                            </div>
                        </x-card>

                        <!-- Right: Total Summary & Action -->
                        <x-card class="p-5 flex flex-col justify-between space-y-6">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3">
                                    Ringkasan Pengembalian
                                </h3>

                                <div class="mt-4 space-y-3 text-sm">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>Total Item Diretur:</span>
                                        <span class="font-bold text-slate-900" x-text="totalQuantitySelected + ' unit'"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>Barang Layak Jual:</span>
                                        <span class="font-semibold text-emerald-600" x-text="goodItemsCount + ' unit'"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>Barang Rusak (Karantina):</span>
                                        <span class="font-semibold text-rose-600" x-text="damagedItemsCount + ' unit'"></span>
                                    </div>

                                    <div class="pt-4 border-t border-slate-200">
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Dana Dikembalikan</p>
                                        <p class="text-2xl font-black text-rose-600 mt-1" x-text="formatRupiah(totalRefundAmount)"></p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" 
                                    :disabled="isSubmitting || totalQuantitySelected === 0 || !reason"
                                    class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-sm shadow-md shadow-amber-600/25 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Konfirmasi & Proses Retur</span>
                            </button>
                        </x-card>

                    </div>

                </div>
            </form>

        </div>

    </div>

    @push('scripts')
    <script>
        function salesReturnForm(initialSaleData) {
            return {
                searchQuery: initialSaleData?.sale_number || '',
                sale: initialSaleData || null,
                returnItems: [],
                refundMethod: 'cash',
                reason: '',
                notes: '',
                isLoading: false,
                isSubmitting: false,
                errorMessage: '',

                init() {
                    if (this.sale && this.sale.items) {
                        this.setupReturnItems(this.sale.items);
                    }
                },

                setupReturnItems(items) {
                    this.returnItems = items.map(it => ({
                        sale_item_id: it.sale_item_id,
                        product_name: it.product_name,
                        sku: it.sku,
                        unit_name: it.unit_name,
                        quantity_purchased: it.quantity_purchased,
                        quantity_returned: it.quantity_returned,
                        quantity_remaining: it.quantity_remaining,
                        unit_price: it.unit_price,
                        net_unit_price: it.net_unit_price,
                        quantity: 0,
                        condition: 'good',
                        is_returnable: it.is_returnable,
                    }));
                },

                async lookupSale() {
                    if (!this.searchQuery.trim()) return;
                    this.isLoading = true;
                    this.errorMessage = '';
                    this.sale = null;
                    this.returnItems = [];

                    try {
                        const response = await fetch(`{{ route('returns.lookup-sale') }}?query=${encodeURIComponent(this.searchQuery.trim())}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            this.errorMessage = data.message || 'Faktur tidak ditemukan atau tidak dapat diproses.';
                            return;
                        }

                        this.sale = data.sale;
                        this.setupReturnItems(data.sale.items);
                    } catch (e) {
                        this.errorMessage = 'Terjadi kesalahan saat memuat faktur penjualan.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                incrementQty(index) {
                    if (this.returnItems[index].quantity < this.returnItems[index].quantity_remaining) {
                        this.returnItems[index].quantity++;
                    }
                },

                decrementQty(index) {
                    if (this.returnItems[index].quantity > 0) {
                        this.returnItems[index].quantity--;
                    }
                },

                validateQty(index) {
                    const item = this.returnItems[index];
                    if (item.quantity < 0) item.quantity = 0;
                    if (item.quantity > item.quantity_remaining) {
                        item.quantity = item.quantity_remaining;
                    }
                },

                returnAllItems() {
                    this.returnItems.forEach(item => {
                        if (item.is_returnable) {
                            item.quantity = item.quantity_remaining;
                        }
                    });
                },

                get totalQuantitySelected() {
                    return this.returnItems.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
                },

                get totalRefundAmount() {
                    return this.returnItems.reduce((sum, item) => sum + ((Number(item.quantity) || 0) * item.net_unit_price), 0);
                },

                get goodItemsCount() {
                    return this.returnItems
                        .filter(it => it.condition === 'good')
                        .reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
                },

                get damagedItemsCount() {
                    return this.returnItems
                        .filter(it => it.condition === 'damaged')
                        .reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
                },

                formatRupiah(val) {
                    return 'Rp ' + Math.round(val || 0).toLocaleString('id-ID');
                },

                async submitReturn() {
                    if (this.totalQuantitySelected === 0) {
                        alert('Pilih setidaknya satu barang untuk diretur.');
                        return;
                    }

                    if (!this.reason) {
                        alert('Silakan pilih alasan retur.');
                        return;
                    }

                    if (!confirm(`Konfirmasi proses retur untuk ${this.totalQuantitySelected} unit dengan total refund ${this.formatRupiah(this.totalRefundAmount)}?`)) {
                        return;
                    }

                    this.isSubmitting = true;

                    const payload = {
                        sale_id: this.sale.id,
                        refund_method: this.refundMethod,
                        reason: this.reason,
                        notes: this.notes,
                        items: this.returnItems
                            .filter(it => it.quantity > 0)
                            .map(it => ({
                                sale_item_id: it.sale_item_id,
                                quantity: it.quantity,
                                condition: it.condition
                            }))
                    };

                    try {
                        const response = await fetch(`{{ route('returns.store') }}`, {
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
                            alert(data.message || 'Gagal memproses retur.');
                            this.isSubmitting = false;
                            return;
                        }

                        window.location.href = data.redirect_url;
                    } catch (e) {
                        alert('Terjadi kesalahan sistem saat memproses retur.');
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
