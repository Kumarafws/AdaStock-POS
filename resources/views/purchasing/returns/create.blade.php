<x-layouts.app title="Buat Retur Pembelian" header="Retur Pembelian Baru">
    <div class="max-w-5xl mx-auto space-y-6"
         x-data="{
             products: {{ Js::from($products) }},
             items: [
                 {
                     product_id: '',
                     quantity: 1,
                     unit_cost: 0,
                     unit_name: 'Pcs',
                     notes: ''
                 }
             ],

             onProductChange(index) {
                 const pId = this.items[index].product_id;
                 const prod = this.products.find(p => p.id == pId);
                 if (prod) {
                     this.items[index].unit_name = prod.base_unit_name;
                     this.items[index].unit_cost = parseFloat(prod.purchase_price) || 0;
                 } else {
                     this.items[index].unit_name = 'Pcs';
                     this.items[index].unit_cost = 0;
                 }
             },

             addItem() {
                 this.items.push({
                     product_id: '',
                     quantity: 1,
                     unit_cost: 0,
                     unit_name: 'Pcs',
                     notes: ''
                 });
             },

             removeItem(index) {
                 if (this.items.length > 1) {
                     this.items.splice(index, 1);
                 }
             },

             get grandTotal() {
                 return this.items.reduce((sum, it) => {
                     const q = parseInt(it.quantity) || 0;
                     const c = parseFloat(it.unit_cost) || 0;
                     return sum + (q * c);
                 }, 0);
             }
         }">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.returns.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Retur Pembelian ke Supplier</h2>
                    <p class="text-xs text-slate-500">Keluarkan barang fisik dari lokasi toko, gudang, atau karantina untuk dikembalikan ke distributor.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('purchasing.returns.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Header Dokumen Retur -->
            <x-card class="p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    1. Informasi Pemasok & Lokasi Asal Barang
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    
                    <!-- Supplier -->
                    <div class="lg:col-span-2">
                        <label for="supplier_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Supplier Tujuan Retur <span class="text-rose-500">*</span>
                        </label>
                        <select name="supplier_id" id="supplier_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    [{{ $sup->code }}] {{ $sup->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Source Location -->
                    <div class="lg:col-span-2">
                        <label for="location_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Lokasi Asal Barang <span class="text-rose-500">*</span>
                        </label>
                        <select name="location_id" id="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                            <option value="">-- Pilih Lokasi Asal --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                    [{{ $loc->code }}] {{ $loc->name }} ({{ $loc->type->label() }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Bisa dipilih Gudang Karantina (QRN-01) untuk retur barang rusak.</p>
                        @error('location_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Return Date -->
                    <div class="lg:col-span-2">
                        <label for="return_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Retur <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="return_date" id="return_date" value="{{ old('return_date', now()->toDateString()) }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                        @error('return_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="lg:col-span-2">
                        <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Retur <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="reason" id="reason" value="{{ old('reason', 'Barang Rusak / Cacat Pabrik') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                        @error('reason')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </x-card>

            <!-- Section 2: Items Repeater -->
            <x-card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        2. Rincian Barang Yang Diretur
                    </h3>

                    <button type="button" 
                            @click="addItem()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        + Tambah Baris
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col md:flex-row items-start md:items-center gap-3 transition-all">
                            
                            <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-extrabold shrink-0" x-text="index + 1"></div>

                            <!-- Product Selector -->
                            <div class="flex-1 min-w-[200px] w-full md:w-auto">
                                <label class="block md:hidden text-[10px] font-bold uppercase text-slate-500 mb-1">Produk</label>
                                <select :name="`items[${index}][product_id]`" 
                                        x-model="item.product_id" 
                                        @change="onProductChange(index)"
                                        class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="`[${p.sku}] ${p.name}`"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Qty (Base Unit) -->
                            <div class="w-full md:w-32 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold uppercase text-slate-500 mb-1">Jumlah</label>
                                <div class="relative">
                                    <input type="number" 
                                           :name="`items[${index}][quantity]`" 
                                           x-model="item.quantity" 
                                           min="1" 
                                           class="w-full rounded-xl border-slate-300 text-sm font-bold tabular-nums text-right focus:border-indigo-500 focus:ring-indigo-500 py-2 pr-10" required>
                                    <span class="absolute inset-y-0 right-2 flex items-center text-xs text-slate-400 font-semibold" x-text="item.unit_name"></span>
                                </div>
                            </div>

                            <!-- Unit Cost -->
                            <div class="w-full md:w-36 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold uppercase text-slate-500 mb-1">Harga Beli</label>
                                <input type="number" 
                                       :name="`items[${index}][unit_cost]`" 
                                       x-model="item.unit_cost" 
                                       min="0" 
                                       step="50"
                                       class="w-full rounded-xl border-slate-300 text-sm font-bold tabular-nums text-right focus:border-indigo-500 focus:ring-indigo-500 py-2" required>
                            </div>

                            <!-- Subtotal -->
                            <div class="w-full md:w-36 text-right shrink-0">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Subtotal</span>
                                <span class="font-extrabold text-sm text-slate-900 tabular-nums" x-text="'Rp ' + Number(item.quantity * item.unit_cost).toLocaleString('id-ID')"></span>
                            </div>

                            <!-- Remove Button -->
                            <div class="shrink-0 pt-2 md:pt-0">
                                <button type="button" 
                                        @click="removeItem(index)" 
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" 
                                        title="Hapus Baris">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </template>
                </div>
            </x-card>

            <!-- Section 3: Notes & Total -->
            <x-card class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan Tambahan
                        </label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Nomor bukti tanda terima supir, klaim ganti rugi, dll..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 flex flex-col justify-center items-end text-right">
                        <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Total Nilai Retur:</span>
                        <div class="text-2xl font-black text-rose-600 tabular-nums mt-1" x-text="'Rp ' + Number(grandTotal).toLocaleString('id-ID')"></div>
                        <p class="text-xs text-slate-500 mt-1">Stok fisik di lokasi asal akan otomatis dipotong.</p>
                    </div>
                </div>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('purchasing.returns.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    Proses Retur & Potong Stok
                </button>
            </div>

        </form>

    </div>
</x-layouts.app>
