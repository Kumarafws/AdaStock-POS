<x-layouts.app title="Mulai Stock Opname Baru" header="Inisiasi Stock Opname">
    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Top Navigation -->
        <div class="flex items-center gap-3">
            <a href="{{ route('opnames.index') }}" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Mulai Sesi Stock Opname Baru</h2>
                <p class="text-sm text-slate-500">Pilih lokasi fisik dan cakupan produk yang akan dihitung di lapangan.</p>
            </div>
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

        <!-- Form Card -->
        <x-card class="p-6">
            <form method="POST" action="{{ route('opnames.store') }}" class="space-y-6">
                @csrf

                <!-- Location Field -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Lokasi Fisik Stok <span class="text-rose-500">*</span>
                    </label>
                    <select name="location_id" required class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 py-2.5">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }} &bull; {{ $loc->type->label() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Pilih toko retail, gudang penyimpanan, atau zona karantina barang rusak yang akan di-opname.
                    </p>
                </div>

                <!-- Category Scope Field -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Cakupan Kategori Produk (Opsional)
                    </label>
                    <select name="category_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 py-2.5">
                        <option value="">Semua Kategori Produk (Katalog Lengkap)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Pilih kategori tertentu jika ingin melakukan opname parsial (misal: hanya produk Minuman atau Makanan).
                    </p>
                </div>

                <!-- Notes / Instructions -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan & Instruksi Tim Lapangan
                    </label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Opname rutin akhir bulan, area rak A1 s/d B4..." class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 p-3">{{ old('notes') }}</textarea>
                </div>

                <!-- Snapshot Notice Banner -->
                <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-200/80 text-purple-900 text-xs flex gap-3">
                    <svg class="w-5 h-5 text-purple-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <div>
                        <strong class="font-semibold block mb-0.5">Mekanisme Pembekuan Saldo Sistem (Snapshot Freeze):</strong>
                        Saat sesi dibuka, sistem secara otomatis mengambil potret saldo sistem saat ini (*snapshot*) untuk produk terpilih. Hasil penghitungan fisik lapangan nantinya akan diperbandingkan terhadap angka saldo sistem ini.
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                    <a href="{{ route('opnames.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-sm shadow-sm shadow-purple-600/20 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Buka Sesi Opname</span>
                    </button>
                </div>
            </form>
        </x-card>

    </div>
</x-layouts.app>
