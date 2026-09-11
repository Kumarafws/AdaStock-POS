<x-layouts.app title="Kategori Produk">
    <x-slot:header>
        Kategori Produk
    </x-slot:header>
    <x-slot:subtitle>
        Pengelompokan jenis barang untuk kemudahan navigasi katalog dan pelaporan.
    </x-slot:subtitle>

    <div x-data="{
        showModal: false,
        isEdit: false,
        formUrl: '{{ route('categories.store') }}',
        id: null,
        code: '',
        name: '',
        description: '',
        is_active: true,

        openCreate() {
            this.isEdit = false;
            this.formUrl = '{{ route('categories.store') }}';
            this.id = null;
            this.code = '';
            this.name = '';
            this.description = '';
            this.is_active = true;
            this.showModal = true;
        },

        openEdit(cat) {
            this.isEdit = true;
            this.formUrl = '/categories/' + cat.id;
            this.id = cat.id;
            this.code = cat.code;
            this.name = cat.name;
            this.description = cat.description || '';
            this.is_active = cat.is_active;
            this.showModal = true;
        }
    }" class="space-y-6">

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="{{ route('categories.index') }}" class="flex items-center gap-2 max-w-sm w-full">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari kode atau nama kategori..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs">
                <x-button type="submit" variant="secondary" size="sm">Cari</x-button>
            </form>

            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <x-button type="button" @click="openCreate()" variant="primary" size="md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kategori
                </x-button>
            @endif
        </div>

        <!-- Table Card -->
        <x-card>
            <div class="overflow-x-auto -mx-6 -my-6">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Kode</th>
                            <th class="py-3 px-6">Nama Kategori</th>
                            <th class="py-3 px-6">Deskripsi</th>
                            <th class="py-3 px-6 text-center">Jumlah Produk</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                <th class="py-3 px-6 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-xs text-indigo-600">{{ $cat->code }}</td>
                                <td class="py-3.5 px-6 font-semibold text-slate-900">{{ $cat->name }}</td>
                                <td class="py-3.5 px-6 text-slate-500 text-xs truncate max-w-xs">{{ $cat->description ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $cat->products_count }} item
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <x-badge :variant="$cat->is_active ? 'emerald' : 'slate'" size="sm">
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                    <td class="py-3.5 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" 
                                                    @click="openEdit({{ $cat->toJson() }})"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                                                    title="Edit Kategori">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors"
                                                        title="Hapus Kategori">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada kategori yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="mt-4 pt-3 border-t border-slate-100">
                    {{ $categories->links() }}
                </div>
            @endif
        </x-card>

        <!-- Modal Form (Create / Edit) -->
        <div x-show="showModal" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900" x-text="isEdit ? 'Edit Kategori' : 'Tambah Kategori Baru'"></h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="formUrl" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Kode Kategori</label>
                        <input type="text" 
                               name="code" 
                               x-model="code"
                               required 
                               placeholder="Contoh: CAT-FNB"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Kategori</label>
                        <input type="text" 
                               name="name" 
                               x-model="name"
                               required 
                               placeholder="Contoh: Makanan & Minuman"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Deskripsi (Opsional)</label>
                        <textarea name="description" 
                                  x-model="description"
                                  rows="2"
                                  placeholder="Keterangan kategori..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_active" name="is_active" value="1" x-model="is_active" class="w-4 h-4 text-indigo-600 rounded">
                        <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Kategori Aktif</label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                        <x-button type="button" @click="showModal = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Simpan Kategori</x-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
