<x-layouts.app title="Pemasok / Supplier">
    <x-slot:header>
        Pemasok / Supplier
    </x-slot:header>
    <x-slot:subtitle>
        Daftar vendor dan distributor resmi pengadaan barang dagang.
    </x-slot:subtitle>

    <div x-data="{
        showModal: false,
        isEdit: false,
        formUrl: '{{ route('suppliers.store') }}',
        id: null,
        code: '',
        name: '',
        contact_name: '',
        phone: '',
        email: '',
        address: '',
        tax_id: '',
        is_active: true,

        openCreate() {
            this.isEdit = false;
            this.formUrl = '{{ route('suppliers.store') }}';
            this.id = null;
            this.code = '';
            this.name = '';
            this.contact_name = '';
            this.phone = '';
            this.email = '';
            this.address = '';
            this.tax_id = '';
            this.is_active = true;
            this.showModal = true;
        },

        openEdit(s) {
            this.isEdit = true;
            this.formUrl = '/suppliers/' + s.id;
            this.id = s.id;
            this.code = s.code;
            this.name = s.name;
            this.contact_name = s.contact_name || '';
            this.phone = s.phone || '';
            this.email = s.email || '';
            this.address = s.address || '';
            this.tax_id = s.tax_id || '';
            this.is_active = s.is_active;
            this.showModal = true;
        }
    }" class="space-y-6">

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2 max-w-sm w-full">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari kode, nama, kontak, atau no telp..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs">
                <x-button type="submit" variant="secondary" size="sm">Cari</x-button>
            </form>

            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <x-button type="button" @click="openCreate()" variant="primary" size="md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Supplier
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
                            <th class="py-3 px-6">Nama Perusahaan / Supplier</th>
                            <th class="py-3 px-6">Kontak Person</th>
                            <th class="py-3 px-6">Telepon / Email</th>
                            <th class="py-3 px-6">Alamat</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                <th class="py-3 px-6 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suppliers as $s)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-xs text-indigo-600">{{ $s->code }}</td>
                                <td class="py-3.5 px-6 font-semibold text-slate-900">
                                    {{ $s->name }}
                                    @if($s->tax_id)
                                        <span class="block text-[10px] text-slate-400 font-mono">NPWP: {{ $s->tax_id }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-slate-700 text-xs font-medium">{{ $s->contact_name ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-xs text-slate-600">
                                    <div>{{ $s->phone ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $s->email ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-6 text-slate-500 text-xs truncate max-w-xs">{{ $s->address ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-center">
                                    <x-badge :variant="$s->is_active ? 'emerald' : 'slate'" size="sm">
                                        {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                    <td class="py-3.5 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" 
                                                    @click="openEdit({{ $s->toJson() }})"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                                                    title="Edit Supplier">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </button>

                                            <form method="POST" action="{{ route('suppliers.destroy', $s) }}" onsubmit="return confirm('Hapus supplier ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors"
                                                        title="Hapus Supplier">
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
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada supplier yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="mt-4 pt-3 border-t border-slate-100">
                    {{ $suppliers->links() }}
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
            
            <div @click.away="showModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900" x-text="isEdit ? 'Edit Data Supplier' : 'Tambah Supplier Baru'"></h3>
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

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Kode Supplier</label>
                            <input type="text" 
                                   name="code" 
                                   x-model="code"
                                   required 
                                   placeholder="SUP-001"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 uppercase font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Perusahaan / Supplier</label>
                            <input type="text" 
                                   name="name" 
                                   x-model="name"
                                   required 
                                   placeholder="PT Distributor..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Kontak Person</label>
                            <input type="text" 
                                   name="contact_name" 
                                   x-model="contact_name"
                                   placeholder="Sales / Contact"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nomor Telepon / WA</label>
                            <input type="text" 
                                   name="phone" 
                                   x-model="phone"
                                   placeholder="0812..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email (Opsional)</label>
                            <input type="email" 
                                   name="email" 
                                   x-model="email"
                                   placeholder="order@vendor.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">NPWP / Tax ID (Opsional)</label>
                            <input type="text" 
                                   name="tax_id" 
                                   x-model="tax_id"
                                   placeholder="Nomor Pokok Wajib Pajak"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Alamat Kantor / Gudang</label>
                        <textarea name="address" 
                                  x-model="address"
                                  rows="2"
                                  placeholder="Alamat lengkap supplier..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50"></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="supplier_is_active" name="is_active" value="1" x-model="is_active" class="w-4 h-4 text-indigo-600 rounded">
                        <label for="supplier_is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Supplier Aktif</label>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                        <x-button type="button" @click="showModal = false" variant="secondary" size="sm">Batal</x-button>
                        <x-button type="submit" variant="primary" size="sm">Simpan Supplier</x-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
