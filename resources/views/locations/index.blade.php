<x-layouts.app title="Toko & Gudang">
    <x-slot:header>
        Manajemen Toko & Gudang
    </x-slot:header>
    <x-slot:subtitle>
        Daftar seluruh store retail, gudang logistik, dan area karantina barang rusak.
    </x-slot:subtitle>

    <div x-data="{
        showModal: false,
        isEdit: false,
        formUrl: '{{ route('locations.store') }}',
        id: null,
        code: '',
        name: '',
        type: 'store',
        phone: '',
        address: '',
        is_active: true,

        openCreate() {
            this.isEdit = false;
            this.formUrl = '{{ route('locations.store') }}';
            this.id = null;
            this.code = '';
            this.name = '';
            this.type = 'store';
            this.phone = '';
            this.address = '';
            this.is_active = true;
            this.showModal = true;
        },

        openEdit(loc) {
            this.isEdit = true;
            this.formUrl = '/locations/' + loc.id;
            this.id = loc.id;
            this.code = loc.code;
            this.name = loc.name;
            this.type = loc.type;
            this.phone = loc.phone || '';
            this.address = loc.address || '';
            this.is_active = Boolean(loc.is_active);
            this.showModal = true;
        }
    }" class="space-y-6">

        <!-- Top Action & Filter Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" action="{{ route('locations.index') }}" class="flex flex-wrap items-center gap-2.5 max-w-2xl w-full">
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[240px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari kode, nama toko/gudang, atau alamat..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs">
                </div>

                <!-- Type Filter -->
                <div class="w-48">
                    <select name="type" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs font-medium text-slate-700">
                        <option value="">Semua Tipe Lokasi</option>
                        @foreach($types as $t)
                            <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>
                                {{ $t->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-button type="submit" variant="secondary" size="md">Filter</x-button>

                @if(request()->hasAny(['search', 'type']))
                    <a href="{{ route('locations.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline font-medium px-1">
                        Reset
                    </a>
                @endif
            </form>

            @if(auth()->user()->isAdmin())
                <div class="flex items-center gap-2">
                    <x-button type="button" @click="openCreate()" variant="primary" size="md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Toko / Gudang
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Locations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($locations as $location)
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:border-slate-300 hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        <!-- Header badge row -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $location->code }}
                                </span>
                                @if(!$location->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>
                            <x-badge :variant="$location->type->value === 'store' ? 'emerald' : ($location->type->value === 'warehouse' ? 'blue' : 'rose')" size="sm">
                                {{ $location->type->label() }}
                            </x-badge>
                        </div>

                        <!-- Name & Address -->
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">
                            {{ $location->name }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                            {{ $location->address ?? 'Alamat operasional belum diatur' }}
                        </p>

                        <!-- Metric Details -->
                        <div class="mt-5 pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-slate-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Telepon:
                                </span>
                                <span class="font-medium text-slate-800">{{ $location->phone ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-slate-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Staf Ditugaskan:
                                </span>
                                <span class="font-semibold text-slate-800">{{ $location->users_count }} Pengguna</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-slate-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    Item Terdata:
                                </span>
                                <span class="font-semibold text-slate-800">{{ $location->inventories_count }} SKU Produk</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Row: Status & Actions -->
                    <div class="mt-6 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <x-status-badge :active="$location->is_active" activeText="Aktif Operasional" />
                        </div>

                        @if(auth()->user()->isAdmin())
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="openEdit({{ json_encode([
                                            'id' => $location->id,
                                            'code' => $location->code,
                                            'name' => $location->name,
                                            'type' => $location->type->value,
                                            'phone' => $location->phone,
                                            'address' => $location->address,
                                            'is_active' => $location->is_active,
                                        ]) }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                        title="Edit Lokasi">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>

                                <form method="POST" action="{{ route('locations.destroy', $location) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi {{ addslashes($location->name) }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Hapus Lokasi">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 font-mono">ID: #{{ $location->id }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state 
                    title="Tidak ada toko atau gudang yang ditemukan" 
                    subtitle="Coba sesuaikan kata kunci pencarian atau filter tipe lokasi yang dipilih."
                    :resetUrl="request()->hasAny(['search', 'type']) ? route('locations.index') : null" />
            @endforelse
        </div>

        @if(auth()->user()->isAdmin())
            <!-- Modal Form (Tambah / Edit Toko & Gudang) -->
            <x-modal showVar="showModal" titleVar="isEdit ? 'Edit Data Toko / Gudang' : 'Tambah Toko / Gudang Baru'" maxWidth="max-w-lg">
                <p class="text-xs text-slate-500 -mt-2 mb-4">Kelola entitas operasional retail dan inventaris fisik.</p>

                    <form :action="formUrl" method="POST" class="space-y-4">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Kode & Tipe Lokasi -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Kode Lokasi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="code" 
                                       x-model="code"
                                       required 
                                       placeholder="STR-02 / WHS-02"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 uppercase font-mono font-bold text-slate-800">
                                <span class="text-[10px] text-slate-400 mt-1 block">Kode unik identifikasi cabang/gudang.</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Tipe Lokasi <span class="text-rose-500">*</span>
                                </label>
                                <select name="type" 
                                        x-model="type"
                                        required
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-medium text-slate-800">
                                    @foreach($types as $t)
                                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                    @endforeach
                                </select>
                                <span class="text-[10px] text-slate-400 mt-1 block">Peruntukan entitas stok fisik.</span>
                            </div>
                        </div>

                        <!-- Nama Toko / Gudang -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nama Toko / Gudang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="name"
                                   required 
                                   placeholder="Contoh: Toko Cabang Surabaya / Gudang Logistik Barat"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-semibold text-slate-800">
                        </div>

                        <!-- Kontak Telepon -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nomor Telepon / Kontak
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   x-model="phone"
                                   placeholder="Contoh: 021-5551234 atau 08123456789"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 text-slate-800">
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Alamat Operasional Lengkap
                            </label>
                            <textarea name="address" 
                                      x-model="address"
                                      rows="2.5"
                                      placeholder="Nama jalan, nomor gedung, kelurahan, kota, provinsi..."
                                      class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 text-slate-800 leading-relaxed"></textarea>
                        </div>

                        <!-- Status Aktif Operasional -->
                        <div class="flex items-center gap-3 pt-2 pb-1">
                            <input type="hidden" name="is_active" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       x-model="is_active" 
                                       class="sr-only peer">
                                <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                            <div>
                                <span class="text-xs font-bold text-slate-800">Status Aktif Operasional</span>
                                <p class="text-[11px] text-slate-500">Jika dinonaktifkan, lokasi tidak akan muncul pada pilihan transaksi baru.</p>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <x-button type="button" @click="showModal = false" variant="secondary" size="md">
                                Batal
                            </x-button>
                            <x-button type="submit" variant="primary" size="md">
                                <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Lokasi'"></span>
                            </x-button>
                        </div>
                    </form>
            </x-modal>
        @endif

    </div>
</x-layouts.app>
