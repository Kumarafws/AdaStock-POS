<x-layouts.app title="Buat Penyesuaian Stok" header="Penyesuaian Stok Baru">
    <div class="max-w-4xl mx-auto space-y-6" 
         x-data="{
             locationId: '{{ old('location_id', $selectedLocationId) }}',
             productId: '{{ old('product_id', $selectedProductId) }}',
             actionType: '{{ old('action_type', 'normal') }}',
             type: '{{ old('type', 'out') }}',
             quantity: {{ old('quantity', 1) }},
             reason: '{{ old('reason', 'damage') }}',
             
             stockData: {
                 loading: false,
                 stock: 0,
                 unit: 'Pcs',
                 breakdown: '',
                 purchasePrice: 0
             },

             async fetchStock() {
                 if (!this.locationId || !this.productId) {
                     this.stockData.stock = 0;
                     this.stockData.breakdown = '';
                     this.stockData.purchasePrice = 0;
                     return;
                 }

                 this.stockData.loading = true;
                 try {
                     const res = await fetch(`{{ route('inventory.check-stock') }}?product_id=${this.productId}&location_id=${this.locationId}`);
                     if (res.ok) {
                         const data = await res.json();
                         this.stockData.stock = data.stock;
                         this.stockData.unit = data.unit;
                         this.stockData.breakdown = data.breakdown;
                         this.stockData.purchasePrice = data.purchase_price;
                     }
                 } catch (e) {
                     console.error('Failed to fetch stock', e);
                 } finally {
                     this.stockData.loading = false;
                 }
             },

             init() {
                 if (this.locationId && this.productId) {
                     this.fetchStock();
                 }
                 this.$watch('locationId', () => this.fetchStock());
                 this.$watch('productId', () => this.fetchStock());
                 this.$watch('actionType', (val) => {
                     if (val === 'to_quarantine') {
                         this.type = 'out';
                         this.reason = 'damage';
                     } else if (val === 'disposal') {
                         this.type = 'out';
                         this.reason = 'damage';
                     }
                 });
             },

             get projectedStock() {
                 const current = parseInt(this.stockData.stock) || 0;
                 const qty = parseInt(this.quantity) || 0;
                 if (this.actionType === 'to_quarantine' || this.actionType === 'disposal' || this.type === 'out') {
                     return current - qty;
                 }
                 return current + qty;
             },

             get totalImpact() {
                 const qty = parseInt(this.quantity) || 0;
                 const price = parseFloat(this.stockData.purchasePrice) || 0;
                 return qty * price;
             }
         }">

        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('adjustments.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Buat Berita Acara Penyesuaian Stok</h2>
                    <p class="text-xs text-slate-500">Form pencatatan fisik stok, mutasi karantina rusak, atau pemusnahan barang.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('adjustments.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Lokasi & Produk -->
            <x-card class="p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    1. Lokasi & Produk Yang Disesuaikan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- Location Selector -->
                    <div>
                        <label for="location_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Lokasi Sumber <span class="text-rose-500">*</span>
                        </label>
                        <select name="location_id" 
                                id="location_id" 
                                x-model="locationId"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">
                                    [{{ $loc->code }}] {{ $loc->name }} ({{ $loc->type->label() }})
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Selector -->
                    <div>
                        <label for="product_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Produk <span class="text-rose-500">*</span>
                        </label>
                        <select name="product_id" 
                                id="product_id" 
                                x-model="productId"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">
                                    [{{ $prod->sku }}] {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Live Stock Snapshot Info Box -->
                <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 transition-all" x-show="productId && locationId">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Saldo Fisik Tersedia di Lokasi Ini:</span>
                                <div class="flex items-baseline gap-2 mt-0.5">
                                    <span class="text-xl font-extrabold text-slate-900 tabular-nums" x-text="stockData.stock">0</span>
                                    <span class="text-sm font-semibold text-slate-600" x-text="stockData.unit">Pcs</span>
                                    <span class="text-xs text-slate-500 font-medium" x-show="stockData.breakdown" x-text="'(' + stockData.breakdown + ')'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">HPP Rata-Rata Acuan:</span>
                            <div class="text-sm font-bold text-slate-800 tabular-nums mt-0.5">
                                Rp <span x-text="Number(stockData.purchasePrice).toLocaleString('id-ID')">0</span> / <span x-text="stockData.unit">Pcs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Section 2: Jenis Tindakan & Arah -->
            <x-card class="p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    2. Jenis Tindakan & Alur Penyesuaian
                </h3>

                <div class="space-y-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Pilih Skenario Tindakan <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        
                        <!-- Option 1: Normal -->
                        <label class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                               :class="actionType === 'normal' ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-600/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="action_type" value="normal" x-model="actionType" class="sr-only">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-900">Penyesuaian Normal</span>
                                <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                      :class="actionType === 'normal' ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="actionType === 'normal'"></span>
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Koreksi fisik selisih opname atau kesalahan pencatatan biasa.</p>
                        </label>

                        <!-- Option 2: To Quarantine -->
                        <label class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                               :class="actionType === 'to_quarantine' ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-600/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="action_type" value="to_quarantine" x-model="actionType" class="sr-only">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-900">Pindah ke Karantina</span>
                                <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                      :class="actionType === 'to_quarantine' ? 'border-purple-600 bg-purple-600' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="actionType === 'to_quarantine'"></span>
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Barang rusak/cacat ditarik dari toko dan dipindahkan ke Gudang Karantina.</p>
                        </label>

                        <!-- Option 3: Disposal -->
                        <label class="relative flex flex-col p-4 rounded-2xl border cursor-pointer transition-all"
                               :class="actionType === 'disposal' ? 'border-rose-600 bg-rose-50/40 ring-2 ring-rose-600/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="action_type" value="disposal" x-model="actionType" class="sr-only">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-slate-900">Pemusnahan (Disposal)</span>
                                <span class="w-4 h-4 rounded-full border flex items-center justify-center"
                                      :class="actionType === 'disposal' ? 'border-rose-600 bg-rose-600' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="actionType === 'disposal'"></span>
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Penghapusan buku (write-off) barang rusak yang resmi dimusnahkan.</p>
                        </label>

                    </div>

                    <!-- Direction (IN / OUT) when Normal -->
                    <div x-show="actionType === 'normal'" class="pt-3 border-t border-slate-100">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Arah Perubahan Stok <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="in" x-model="type" class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                                    + Penambahan Stok (IN)
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="out" x-model="type" class="text-rose-600 focus:ring-rose-500">
                                <span class="text-sm font-bold text-rose-700 bg-rose-50 px-3 py-1 rounded-lg border border-rose-200">
                                    - Pengurangan Stok (OUT)
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Hidden Type Input when to_quarantine or disposal -->
                    <input type="hidden" name="type" :value="type" x-show="actionType !== 'normal'">

                </div>
            </x-card>

            <!-- Section 3: Kuantitas, Alasan & Catatan -->
            <x-card class="p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    3. Kuantitas & Alasan Penyesuaian
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- Quantity Input -->
                    <div>
                        <label for="quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jumlah Penyesuaian (<span x-text="stockData.unit">Pcs</span>) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="quantity" 
                                   id="quantity" 
                                   x-model="quantity"
                                   min="1"
                                   class="w-full rounded-xl border-slate-300 text-base font-bold tabular-nums focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3">
                            <span class="absolute inset-y-0 right-3 flex items-center text-xs font-semibold text-slate-400" x-text="stockData.unit">
                                Pcs
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Kuantitas dihitung dalam satuan terkecil (Base Unit).</p>
                        @error('quantity')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Negative stock warning in client preview -->
                        <template x-if="(actionType === 'to_quarantine' || actionType === 'disposal' || type === 'out') && projectedStock < 0">
                            <div class="mt-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Peringatan: Jumlah pengurangan melebihi saldo fisik saat ini! Transaksi akan ditolak oleh sistem.</span>
                            </div>
                        </template>
                    </div>

                    <!-- Reason Dropdown -->
                    <div>
                        <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Penyesuaian <span class="text-rose-500">*</span>
                        </label>
                        <select name="reason" 
                                id="reason" 
                                x-model="reason"
                                class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                            @foreach($reasons as $r)
                                <option value="{{ $r->value }}">
                                    {{ $r->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('reason')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan Berita Acara / Keterangan Tambahan
                        </label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="3" 
                                  placeholder="Tuliskan nomor berita acara fisik, kondisi kerusakan barang, atau rincian penyebab selisih opname..." 
                                  class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Live Projection & Impact Card -->
                <div class="mt-6 p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100" x-show="productId && locationId">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-900 mb-2">Simulasi Dampak Buku Besar:</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="space-y-1">
                            <span class="text-xs text-slate-500">Perubahan Saldo:</span>
                            <div class="font-bold text-slate-800 tabular-nums">
                                <span x-text="stockData.stock">0</span>
                                <span class="text-slate-400 mx-1">&rarr;</span>
                                <span :class="projectedStock < 0 ? 'text-rose-600' : 'text-indigo-600 font-extrabold'" x-text="projectedStock">0</span>
                                <span class="text-xs text-slate-500 font-semibold" x-text="stockData.unit">Pcs</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs text-slate-500">Estimasi Dampak Nilai Finansial:</span>
                            <div class="font-extrabold text-slate-900 tabular-nums">
                                Rp <span x-text="Number(totalImpact).toLocaleString('id-ID')">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('adjustments.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    Simpan & Catat Mutasi Stok
                </button>
            </div>

        </form>
    </div>
</x-layouts.app>
