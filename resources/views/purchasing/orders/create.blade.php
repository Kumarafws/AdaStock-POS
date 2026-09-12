<x-layouts.app title="Buat Purchase Order" header="Buat Purchase Order Baru">
    <div class="max-w-5xl mx-auto space-y-6"
         x-data="{
             products: {{ Js::from($products) }},
             items: [
                 {
                     product_id: '',
                     product_unit_id: '',
                     unit_name: 'Pcs',
                     conversion_factor: 1,
                     ordered_quantity: 1,
                     unit_cost: 0,
                     available_units: []
                 }
             ],
             taxAmount: 0,
             discountAmount: 0,

             onProductChange(index) {
                 const pId = this.items[index].product_id;
                 const prod = this.products.find(p => p.id == pId);
                 if (prod) {
                     this.items[index].available_units = prod.units || [];
                     this.items[index].product_unit_id = '';
                     this.items[index].unit_name = prod.base_unit_name;
                     this.items[index].conversion_factor = 1;
                     this.items[index].unit_cost = parseFloat(prod.purchase_price) || 0;
                 } else {
                     this.items[index].available_units = [];
                     this.items[index].unit_name = 'Pcs';
                     this.items[index].conversion_factor = 1;
                     this.items[index].unit_cost = 0;
                 }
             },

             onUnitChange(index) {
                 const uId = this.items[index].product_unit_id;
                 const pId = this.items[index].product_id;
                 const prod = this.products.find(p => p.id == pId);
                 if (!prod) return;

                 if (uId) {
                     const unit = (prod.units || []).find(u => u.id == uId);
                     if (unit) {
                         this.items[index].unit_name = unit.unit_name;
                         this.items[index].conversion_factor = parseInt(unit.conversion_factor) || 1;
                         this.items[index].unit_cost = parseFloat(prod.purchase_price) * this.items[index].conversion_factor;
                     }
                 } else {
                     this.items[index].unit_name = prod.base_unit_name;
                     this.items[index].conversion_factor = 1;
                     this.items[index].unit_cost = parseFloat(prod.purchase_price) || 0;
                 }
             },

             addItem() {
                 this.items.push({
                     product_id: '',
                     product_unit_id: '',
                     unit_name: 'Pcs',
                     conversion_factor: 1,
                     ordered_quantity: 1,
                     unit_cost: 0,
                     available_units: []
                 });
             },

             removeItem(index) {
                 if (this.items.length > 1) {
                     this.items.splice(index, 1);
                 }
             },

             get subtotal() {
                 return this.items.reduce((sum, it) => {
                     const q = parseInt(it.ordered_quantity) || 0;
                     const c = parseFloat(it.unit_cost) || 0;
                     return sum + (q * c);
                 }, 0);
             },

             get grandTotal() {
                 const tax = parseFloat(this.taxAmount) || 0;
                 const disc = parseFloat(this.discountAmount) || 0;
                 return Math.max(0, this.subtotal + tax - disc);
             }
         }">

        <!-- Header Bar -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchasing.orders.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Formulir Pemesanan Barang (PO)</h2>
                    <p class="text-xs text-slate-500">Pilih supplier dan tambahkan item barang dengan pilihan multi-satuan (Dus / Pcs).</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('purchasing.orders.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Header Dokumen PO -->
            <x-card class="p-6">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    1. Informasi Pemasok & Tujuan Kirim
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    
                    <!-- Supplier -->
                    <div class="lg:col-span-2">
                        <label for="supplier_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Supplier / Pemasok <span class="text-rose-500">*</span>
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

                    <!-- Destination Location -->
                    <div class="lg:col-span-2">
                        <label for="destination_location_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Lokasi Gudang/Toko Tujuan <span class="text-rose-500">*</span>
                        </label>
                        <select name="destination_location_id" id="destination_location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('destination_location_id') == $loc->id ? 'selected' : '' }}>
                                    [{{ $loc->code }}] {{ $loc->name }} ({{ $loc->type->label() }})
                                </option>
                            @endforeach
                        </select>
                        @error('destination_location_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Order Date -->
                    <div class="lg:col-span-2">
                        <label for="order_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Pemesanan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="order_date" id="order_date" value="{{ old('order_date', now()->toDateString()) }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5" required>
                        @error('order_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expected Delivery Date -->
                    <div class="lg:col-span-2">
                        <label for="expected_delivery_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Estimasi Tanggal Tiba (Expected)
                        </label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date" value="{{ old('expected_delivery_date') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5">
                        @error('expected_delivery_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </x-card>

            <!-- Section 2: Rincian Item PO (Alpine.js Repeater) -->
            <x-card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        2. Rincian Item Produk Yang Dipesan
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
                            
                            <!-- Index Badge -->
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

                            <!-- Unit Selector -->
                            <div class="w-full md:w-40 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold uppercase text-slate-500 mb-1">Satuan</label>
                                <select :name="`items[${index}][product_unit_id]`" 
                                        x-model="item.product_unit_id" 
                                        @change="onUnitChange(index)"
                                        class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                                    <option value="" x-text="item.unit_name + ' (1x)'"></option>
                                    <template x-for="u in item.available_units" :key="u.id">
                                        <option :value="u.id" x-text="`${u.unit_name} (${u.conversion_factor}x)`"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Ordered Quantity -->
                            <div class="w-full md:w-28 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold uppercase text-slate-500 mb-1">Kuantitas</label>
                                <input type="number" 
                                       :name="`items[${index}][ordered_quantity]`" 
                                       x-model="item.ordered_quantity" 
                                       min="1" 
                                       class="w-full rounded-xl border-slate-300 text-sm font-bold tabular-nums text-right focus:border-indigo-500 focus:ring-indigo-500 py-2" required>
                            </div>

                            <!-- Unit Cost -->
                            <div class="w-full md:w-36 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold uppercase text-slate-500 mb-1">Harga Beli</label>
                                <input type="number" 
                                       :name="`items[${index}][unit_cost]`" 
                                       x-model="item.unit_cost" 
                                       min="0" 
                                       step="100"
                                       class="w-full rounded-xl border-slate-300 text-sm font-bold tabular-nums text-right focus:border-indigo-500 focus:ring-indigo-500 py-2" required>
                            </div>

                            <!-- Subtotal Display -->
                            <div class="w-full md:w-36 text-right shrink-0">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Subtotal</span>
                                <span class="font-extrabold text-sm text-slate-900 tabular-nums" x-text="'Rp ' + Number(item.ordered_quantity * item.unit_cost).toLocaleString('id-ID')"></span>
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

            <!-- Section 3: Pajak, Diskon & Total Nilai PO -->
            <x-card class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan Tambahan untuk Supplier / Gudang
                        </label>
                        <textarea name="notes" id="notes" rows="4" placeholder="Syarat pembayaran, instruksi bongkar muat, atau catatan spesifik..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Subtotal Item:</span>
                            <span class="font-bold text-slate-900 tabular-nums" x-text="'Rp ' + Number(subtotal).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Pajak / PPN (Rp):</span>
                            <input type="number" name="tax_amount" x-model="taxAmount" min="0" step="100" class="w-36 rounded-lg border-slate-300 text-sm font-bold tabular-nums text-right py-1">
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Diskon Supplier (Rp):</span>
                            <input type="number" name="discount_amount" x-model="discountAmount" min="0" step="100" class="w-36 rounded-lg border-slate-300 text-sm font-bold tabular-nums text-right py-1">
                        </div>

                        <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
                            <span class="text-base font-extrabold text-slate-900">Total Nilai PO:</span>
                            <span class="text-xl font-extrabold text-indigo-600 tabular-nums" x-text="'Rp ' + Number(grandTotal).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('purchasing.orders.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    Terbitkan Purchase Order
                </button>
            </div>

        </form>
    </div>
</x-layouts.app>
