<x-layouts.app title="Katalog Produk">
    <x-slot:header>
        Katalog Produk & Multi-Satuan
    </x-slot:header>
    <x-slot:subtitle>
        Daftar seluruh barang retail, harga per satuan, kemasan bertingkat (Dus/Pak/Pcs), dan parameter stok minimum.
    </x-slot:subtitle>

    <div class="space-y-6">

        <!-- Search & Filter Card -->
        <x-card>
            <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Cari Produk</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nama, SKU, atau Barcode..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kategori</label>
                    <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Merek / Brand</label>
                    <select name="brand_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        <option value="">Semua Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <x-button type="submit" variant="primary" size="sm" class="flex-1">
                        Filter
                    </x-button>
                    <x-button href="{{ route('products.index') }}" variant="secondary" size="sm">
                        Reset
                    </x-button>
                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                        <x-button href="{{ route('products.create') }}" variant="emerald" size="sm" class="shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            + Produk
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Product Table Card -->
        <x-card>
            <div class="overflow-x-auto -mx-6 -my-6">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Produk & Barcode</th>
                            <th class="py-3 px-6">Kategori & Brand</th>
                            <th class="py-3 px-6">Satuan & Konversi</th>
                            <th class="py-3 px-6 text-right">Harga Beli (HPP Dasar)</th>
                            <th class="py-3 px-6 text-right">Harga Jual (Base Unit)</th>
                            <th class="py-3 px-6 text-center">Batas Min</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $p)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- Product & Barcode -->
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden text-slate-400">
                                            @if($p->image_path)
                                                <img src="{{ asset('storage/' . $p->image_path) }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-6 h-6 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('products.show', $p) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors">
                                                {{ $p->name }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="font-mono text-[11px] font-semibold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">
                                                    {{ $p->sku }}
                                                </span>
                                                @if($p->barcode)
                                                    <span class="font-mono text-[11px] text-slate-500">
                                                        {{ $p->barcode }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category & Brand -->
                                <td class="py-3.5 px-6">
                                    <div class="font-semibold text-slate-800 text-xs">{{ $p->category->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $p->brand->name ?? 'No Brand' }}</div>
                                </td>

                                <!-- Units & Conversion -->
                                <td class="py-3.5 px-6">
                                    <div class="space-y-1">
                                        <x-badge variant="slate" size="sm">
                                            Dasar: 1 {{ $p->base_unit_name }}
                                        </x-badge>
                                        @foreach($p->units as $u)
                                            <div class="flex items-center gap-1">
                                                <x-badge variant="indigo" size="sm">
                                                    1 {{ $u->unit_name }} = {{ $u->conversion_factor }} {{ $p->base_unit_name }}
                                                </x-badge>
                                                <span class="text-[11px] text-slate-500 num-tabular">
                                                    (Rp {{ number_format($u->selling_price, 0, ',', '.') }})
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <!-- Purchase Price (HPP) -->
                                <td class="py-3.5 px-6 text-right font-mono font-medium text-slate-600 num-tabular text-xs">
                                    Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                                </td>

                                <!-- Default Selling Price -->
                                <td class="py-3.5 px-6 text-right font-mono font-bold text-slate-900 num-tabular text-sm">
                                    Rp {{ number_format($p->default_selling_price, 0, ',', '.') }}
                                </td>

                                <!-- Thresholds -->
                                <td class="py-3.5 px-6 text-center text-xs">
                                    <span class="text-amber-600 font-semibold" title="Batas Stok Rendah">{{ $p->min_stock }}</span>
                                    <span class="text-slate-300">/</span>
                                    <span class="text-slate-600 font-medium" title="Reorder Point">{{ $p->reorder_point }}</span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-6 text-center">
                                    <x-badge :variant="$p->is_active ? 'emerald' : 'slate'" size="sm">
                                        {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('products.show', $p) }}" 
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                                           title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>

                                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                            <a href="{{ route('products.edit', $p) }}" 
                                               class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                                               title="Edit Produk">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </a>

                                            <form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors"
                                                        title="Hapus Produk">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                    Tidak ada produk yang sesuai dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="mt-4 pt-3 border-t border-slate-100">
                    {{ $products->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
