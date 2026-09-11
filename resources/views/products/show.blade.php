<x-layouts.app title="Detail Produk: {{ $product->name }}">
    <x-slot:header>
        Detail Produk
    </x-slot:header>
    <x-slot:subtitle>
        Informasi spesifikasi barang, struktur harga eceran, dan satuan kemasan bertingkat.
    </x-slot:subtitle>

    <div class="space-y-6 max-w-5xl">

        <!-- Top Navigation -->
        <div class="flex items-center justify-between">
            <x-button href="{{ route('products.index') }}" variant="secondary" size="sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Katalog
            </x-button>

            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('products.toggle', $product) }}">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" :variant="$product->is_active ? 'secondary' : 'emerald'" size="sm">
                            {{ $product->is_active ? 'Nonaktifkan Produk' : 'Aktifkan Produk' }}
                        </x-button>
                    </form>

                    <x-button href="{{ route('products.edit', $product) }}" variant="primary" size="sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        Edit Produk
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Main Product Card -->
        <x-card>
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <!-- Thumbnail -->
                <div class="w-36 h-36 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden text-slate-400">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-12 h-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    @endif
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">
                            {{ $product->sku }}
                        </span>
                        @if($product->barcode)
                            <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                Barcode: {{ $product->barcode }}
                            </span>
                        @endif
                        <x-badge :variant="$product->is_active ? 'emerald' : 'slate'" size="sm">
                            {{ $product->is_active ? 'Aktif Dijual' : 'Nonaktif' }}
                        </x-badge>
                    </div>

                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $product->name }}</h2>

                    <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500">
                        <div>
                            Kategori: <strong class="text-slate-800">{{ $product->category->name }}</strong>
                        </div>
                        <div>
                            Brand: <strong class="text-slate-800">{{ $product->brand->name ?? 'Tanpa Merek' }}</strong>
                        </div>
                        <div>
                            Satuan Dasar: <strong class="text-indigo-600">{{ $product->base_unit_name }}</strong>
                        </div>
                    </div>

                    @if($product->description)
                        <p class="mt-4 text-xs text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                            {{ $product->description }}
                        </p>
                    @endif
                </div>
            </div>
        </x-card>

        <!-- Pricing & Thresholds Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Price Card -->
            <x-card title="Struktur Harga Satuan Dasar ({{ $product->base_unit_name }})">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500 text-xs">Harga Beli Acuan (HPP Awal):</span>
                        <span class="font-mono font-medium text-slate-700 num-tabular">
                            Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500 text-xs">Harga Jual Dasar (Eceran):</span>
                        <span class="font-mono font-bold text-slate-900 text-base num-tabular">
                            Rp {{ number_format($product->default_selling_price, 0, ',', '.') }}
                        </span>
                    </div>

                    @php
                        $margin = $product->default_selling_price - $product->purchase_price;
                        $marginPct = $product->default_selling_price > 0 ? ($margin / $product->default_selling_price) * 100 : 0;
                    @endphp
                    <div class="flex items-center justify-between py-2">
                        <span class="text-slate-500 text-xs">Potensi Margin Keuntungan:</span>
                        <span class="font-mono font-bold {{ $margin >= 0 ? 'text-emerald-600' : 'text-rose-600' }} num-tabular text-sm">
                            Rp {{ number_format($margin, 0, ',', '.') }} ({{ number_format($marginPct, 1) }}%)
                        </span>
                    </div>
                </div>
            </x-card>

            <!-- Inventory Threshold Card -->
            <x-card title="Batas & Parameter Persediaan">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500 text-xs">Batas Stok Rendah (Min Stock):</span>
                        <span class="font-mono font-bold text-amber-600">
                            {{ $product->min_stock }} {{ $product->base_unit_name }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500 text-xs">Titik Pesan Ulang (Reorder Point):</span>
                        <span class="font-mono font-bold text-indigo-600">
                            {{ $product->reorder_point }} {{ $product->base_unit_name }}
                        </span>
                    </div>

                    <div class="py-2 text-xs text-slate-500">
                        💡 Sistem akan otomatis menandai status <strong>LOW_STOCK</strong> dan mengirimkan notifikasi saat stok toko/gudang berada di bawah atau sama dengan titik reorder point.
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Multi-Unit Hierarchy Table -->
        <x-card title="Struktur Kemasan Bertingkat (Multi-Satuan UOM)" subtitle="Konversi satuan saat transaksi POS kasir maupun penerimaan barang gudang.">
            <div class="overflow-x-auto -mx-6 -my-6">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Tingkat Satuan</th>
                            <th class="py-3 px-6">Rasio Konversi</th>
                            <th class="py-3 px-6">Barcode Kemasan</th>
                            <th class="py-3 px-6 text-right">Harga Jual Kemasan</th>
                            <th class="py-3 px-6 text-right">Harga Setara per {{ $product->base_unit_name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Base Unit Row -->
                        <tr class="bg-indigo-50/30 font-medium">
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center gap-1.5 font-bold text-indigo-700">
                                    ★ {{ $product->base_unit_name }} (Satuan Dasar)
                                </span>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-xs text-slate-600">1 {{ $product->base_unit_name }}</td>
                            <td class="py-3.5 px-6 font-mono text-xs text-slate-600">{{ $product->barcode ?? '—' }}</td>
                            <td class="py-3.5 px-6 text-right font-mono font-bold text-slate-900 num-tabular">
                                Rp {{ number_format($product->default_selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-6 text-right font-mono text-slate-600 num-tabular text-xs">
                                Rp {{ number_format($product->default_selling_price, 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- Secondary Units Rows -->
                        @forelse($product->units as $u)
                            @php
                                $equivPerBase = $u->conversion_factor > 0 ? $u->selling_price / $u->conversion_factor : 0;
                                $saving = $product->default_selling_price > 0 ? (($product->default_selling_price - $equivPerBase) / $product->default_selling_price) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-bold text-slate-800">
                                    {{ $u->unit_name }}
                                </td>
                                <td class="py-3.5 px-6 font-mono text-xs">
                                    <x-badge variant="indigo" size="sm">
                                        1 {{ $u->unit_name }} = {{ $u->conversion_factor }} {{ $product->base_unit_name }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-6 font-mono text-xs text-slate-600">{{ $u->barcode ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-right font-mono font-bold text-slate-900 num-tabular">
                                    Rp {{ number_format($u->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-6 text-right font-mono text-slate-600 num-tabular text-xs">
                                    Rp {{ number_format($equivPerBase, 0, ',', '.') }}
                                    @if($saving > 0)
                                        <span class="text-emerald-600 text-[10px] font-semibold block">Hemat {{ number_format($saving, 1) }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-6 text-slate-400 text-xs italic">
                                    Belum ada kemasan sekunder (Dus/Pak). Produk hanya dijual dalam satuan dasar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</x-layouts.app>
