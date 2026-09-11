<x-layouts.app title="Tambah Produk Baru">
    <x-slot:header>
        Tambah Produk Baru
    </x-slot:header>
    <x-slot:subtitle>
        Input produk master baru lengkap dengan satuan dasar dan kemasan bertingkat (multi-satuan).
    </x-slot:subtitle>

    <div x-data="{
        baseUnit: 'Pcs',
        baseSellingPrice: 0,
        units: [],

        addUnit() {
            this.units.push({
                unit_name: '',
                conversion_factor: 12,
                barcode: '',
                selling_price: 0
            });
        },

        removeUnit(index) {
            this.units.splice(index, 1);
        },

        generateSku() {
            const prefix = 'PRD-';
            const random = Math.floor(100000 + Math.random() * 900000);
            document.getElementById('sku').value = prefix + random;
        }
    }" class="space-y-6 max-w-5xl">

        @if($errors->any())
            <x-alert type="error">
                <div class="font-bold mb-1">Terdapat kesalahan pengisian form:</div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Dasar & Identitas -->
            <x-card title="1. Identitas Produk & Kategori" subtitle="Informasi utama untuk mengenali produk di sistem.">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="sku" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Kode SKU *</label>
                            <button type="button" @click="generateSku()" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">
                                ⚡ Generate Acak
                            </button>
                        </div>
                        <input type="text" 
                               id="sku" 
                               name="sku" 
                               value="{{ old('sku') }}" 
                               required 
                               placeholder="Contoh: PRD-IND-001"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono uppercase">
                    </div>

                    <div>
                        <label for="barcode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Barcode Satuan Dasar (Opsional)</label>
                        <input type="text" 
                               id="barcode" 
                               name="barcode" 
                               value="{{ old('barcode') }}" 
                               placeholder="Scan atau ketik barcode eceran..."
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono">
                    </div>

                    <div class="md:col-span-2">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap Produk *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               placeholder="Contoh: Indomie Goreng Spesial 85g"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                    </div>

                    <div>
                        <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Kategori Produk *</label>
                        <select id="category_id" name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="brand_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Merek / Brand (Opsional)</label>
                        <select id="brand_id" name="brand_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                            <option value="">-- Tanpa Brand / No Brand --</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="image" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Foto Produk (Opsional)</label>
                        <input type="file" 
                               id="image" 
                               name="image" 
                               accept="image/png,image/jpeg,image/webp"
                               class="w-full px-3 py-2 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    </div>
                </div>
            </x-card>

            <!-- Section 2: Satuan Dasar & Harga -->
            <x-card title="2. Satuan Dasar (Base Unit) & Batas Stok" subtitle="Satuan terkecil yang dijadikan dasar mutasi inventori.">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="base_unit_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Satuan Dasar *</label>
                        <input type="text" 
                               id="base_unit_name" 
                               name="base_unit_name" 
                               x-model="baseUnit"
                               required 
                               placeholder="Pcs, Botol, Bungkus..."
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-semibold text-indigo-700">
                    </div>

                    <div>
                        <label for="purchase_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Harga Beli Acuan (HPP Awal) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs font-semibold">Rp</span>
                            <input type="number" 
                                   id="purchase_price" 
                                   name="purchase_price" 
                                   value="{{ old('purchase_price', 0) }}" 
                                   step="any"
                                   min="0"
                                   required 
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono">
                        </div>
                    </div>

                    <div>
                        <label for="default_selling_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Harga Jual Dasar (Eceran) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs font-semibold">Rp</span>
                            <input type="number" 
                                   id="default_selling_price" 
                                   name="default_selling_price" 
                                   x-model="baseSellingPrice"
                                   step="any"
                                   min="0"
                                   required 
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono font-bold text-slate-900">
                        </div>
                    </div>

                    <div>
                        <label for="min_stock" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Minimum Stok *</label>
                        <input type="number" 
                               id="min_stock" 
                               name="min_stock" 
                               value="{{ old('min_stock', 10) }}" 
                               min="0"
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono">
                    </div>

                    <div>
                        <label for="reorder_point" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Titik Pesan Ulang (Reorder Point) *</label>
                        <input type="number" 
                               id="reorder_point" 
                               name="reorder_point" 
                               value="{{ old('reorder_point', 25) }}" 
                               min="0"
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono">
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                        <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Produk Aktif Dijual</label>
                    </div>
                </div>
            </x-card>

            <!-- Section 3: Kemasan Bertingkat (Multi-Satuan UOM) -->
            <x-card title="3. Satuan Kemasan Sekunder (Multi-Satuan / UOM)" subtitle="Konfigurasi penjualan satuan bertingkat (misal: 1 Dus = 40 Pcs, 1 Pak = 10 Pcs).">
                <x-slot:headerAction>
                    <x-button type="button" @click="addUnit()" variant="secondary" size="sm">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        + Tambah Kemasan (Dus/Pak)
                    </x-button>
                </x-slot:headerAction>

                <div x-show="units.length === 0" class="py-8 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                    <p class="text-xs text-slate-500 font-medium">
                        Belum ada kemasan sekunder. Produk ini hanya akan dijual dalam satuan dasar (<span class="font-bold text-indigo-600" x-text="baseUnit"></span>).
                    </p>
                    <button type="button" @click="addUnit()" class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Klik di sini jika produk ini juga dijual per Dus, Pak, atau Karton
                    </button>
                </div>

                <div x-show="units.length > 0" class="space-y-3">
                    <template x-for="(unit, index) in units" :key="index">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-end gap-3">
                            <div class="flex-1 w-full">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Kemasan</label>
                                <input type="text" 
                                       :name="`units[${index}][unit_name]`" 
                                       x-model="unit.unit_name" 
                                       required 
                                       placeholder="Contoh: Dus, Pak, Karton"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white">
                            </div>

                            <div class="w-full md:w-36">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Isi (<span x-text="baseUnit"></span>)
                                </label>
                                <input type="number" 
                                       :name="`units[${index}][conversion_factor]`" 
                                       x-model.number="unit.conversion_factor" 
                                       min="2" 
                                       required 
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white font-mono">
                            </div>

                            <div class="flex-1 w-full">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Barcode Kemasan (Opsional)</label>
                                <input type="text" 
                                       :name="`units[${index}][barcode]`" 
                                       x-model="unit.barcode" 
                                       placeholder="Barcode pada dus..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white font-mono">
                            </div>

                            <div class="w-full md:w-44">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Harga Jual Kemasan</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 text-xs">Rp</span>
                                    <input type="number" 
                                           :name="`units[${index}][selling_price]`" 
                                           x-model.number="unit.selling_price" 
                                           min="0" 
                                           step="any"
                                           required 
                                           class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white font-mono font-bold text-slate-800">
                                </div>
                            </div>

                            <button type="button" 
                                    @click="removeUnit(index)" 
                                    class="p-2.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors shrink-0"
                                    title="Hapus baris kemasan">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </x-card>

            <!-- Submit Action -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <x-button href="{{ route('products.index') }}" variant="secondary" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary" size="md">
                    Simpan Produk & Satuan
                </x-button>
            </div>
        </form>

    </div>
</x-layouts.app>
