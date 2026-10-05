<x-layouts.app title="Kelola Pengguna">
    <x-slot:header>
        Kelola Pengguna & Hak Akses
    </x-slot:header>
    <x-slot:subtitle>
        Daftar seluruh akun pengguna sistem, peran (Admin, Manager, Kasir), dan penugasan toko retail.
    </x-slot:subtitle>

    <div x-data="{
        showModal: false,
        isEdit: false,
        formUrl: '{{ route('admin.users.store') }}',
        name: '',
        username: '',
        email: '',
        password: '',
        role: 'cashier',
        assigned_store_id: '',
        supervisor_pin: '',
        phone: '',
        status: 'active',

        openCreate() {
            this.isEdit = false;
            this.formUrl = '{{ route('admin.users.store') }}';
            this.name = '';
            this.username = '';
            this.email = '';
            this.password = '';
            this.role = 'cashier';
            this.assigned_store_id = '';
            this.supervisor_pin = '';
            this.phone = '';
            this.status = 'active';
            this.showModal = true;
        },

        openEdit(u) {
            this.isEdit = true;
            this.formUrl = '/admin/users/' + u.id;
            this.name = u.name;
            this.username = u.username;
            this.email = u.email;
            this.password = '';
            this.role = u.role;
            this.assigned_store_id = u.assigned_store_id ? String(u.assigned_store_id) : '';
            this.supervisor_pin = '';
            this.phone = u.phone || '';
            this.status = u.status;
            this.showModal = true;
        }
    }" class="space-y-6">

        <!-- Top Action & Filter Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2.5 max-w-3xl w-full">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[220px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama, username, email, telepon..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs">
                </div>

                <!-- Role Filter -->
                <div class="w-44">
                    <select name="role" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs font-medium text-slate-700">
                        <option value="">Semua Peran</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->value }}" {{ request('role') === $r->value ? 'selected' : '' }}>
                                {{ $r->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-36">
                    <select name="status" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-2xs font-medium text-slate-700">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <x-button type="submit" variant="secondary" size="md">Filter</x-button>

                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline font-medium px-1">
                        Reset
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2">
                <x-button type="button" @click="openCreate()" variant="primary" size="md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.765Z" />
                    </svg>
                    Tambah Pengguna
                </x-button>
            </div>
        </div>

        <!-- Users Table Card -->
        <x-card title="Daftar Pengguna Sistem">
            <div class="overflow-x-auto -mx-6 -my-6">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Pengguna</th>
                            <th class="py-3 px-6">Username / Email</th>
                            <th class="py-3 px-6">Peran (Role)</th>
                            <th class="py-3 px-6">Penugasan Toko</th>
                            <th class="py-3 px-6">PIN Otorisasi</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shadow-2xs">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                {{ $u->name }}
                                                @if($u->id === auth()->id())
                                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-semibold">Anda</span>
                                                @endif
                                            </div>
                                            @if($u->phone)
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    {{ $u->phone }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="font-mono text-xs font-semibold text-slate-800">{{ $u->username }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <x-badge :variant="$u->role->value === 'admin' ? 'indigo' : ($u->role->value === 'manager' ? 'amber' : 'emerald')" size="sm">
                                        {{ $u->role->label() }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-6 text-xs text-slate-600">
                                    @if($u->assignedStore)
                                        <div class="font-medium text-slate-800">{{ $u->assignedStore->name }}</div>
                                        <span class="inline-block text-[10px] text-slate-400 font-mono">{{ $u->assignedStore->code }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Pusat / Semua Lokasi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-xs">
                                    @if($u->supervisor_pin)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-mono font-semibold text-[11px] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            ●●●●●● (Terset)
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Tidak Diperlukan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <x-status-badge :active="$u->status === 'active'" activeText="Aktif" inactiveText="Nonaktif" />
                                </td>
                                <td class="py-3.5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                @click="openEdit({{ json_encode([
                                                    'id' => $u->id,
                                                    'name' => $u->name,
                                                    'username' => $u->username,
                                                    'email' => $u->email,
                                                    'role' => $u->role->value,
                                                    'assigned_store_id' => $u->assigned_store_id,
                                                    'phone' => $u->phone,
                                                    'status' => $u->status,
                                                ]) }})"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Edit Pengguna">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan akun {{ addslashes($u->name) }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                        title="Hapus Pengguna">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8">
                                    <x-empty-state 
                                        title="Tidak ada pengguna yang sesuai" 
                                        subtitle="Coba ubah kata kunci pencarian atau sesuaikan filter peran dan status."
                                        :resetUrl="request()->hasAny(['search', 'role', 'status']) ? route('admin.users.index') : null" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Modal Form (Tambah / Edit Pengguna) -->
        <x-modal showVar="showModal" titleVar="isEdit ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru'" maxWidth="max-w-xl">
            <p class="text-xs text-slate-500 -mt-2 mb-4">Atur informasi kredensial, peran akses, dan penempatan toko pengguna.</p>

            <form :action="formUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           x-model="name"
                           required 
                           placeholder="Contoh: Rian Hidayat"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-semibold text-slate-800">
                </div>

                <!-- Username & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="username" 
                               x-model="username"
                               required 
                               placeholder="rian.kasir"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono text-slate-800 lowercase">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               x-model="email"
                               required 
                               placeholder="rian@adastock.local"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 text-slate-800">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Kata Sandi (Password) <span x-show="!isEdit" class="text-rose-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           x-model="password"
                           :required="!isEdit"
                           placeholder="Minimal 6 karakter"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 text-slate-800">
                    <p x-show="isEdit" class="text-[11px] text-slate-400 mt-1">Kosongkan jika Anda tidak ingin memperbarui kata sandi pengguna ini.</p>
                </div>

                <!-- Peran (Role) & Penugasan Toko -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Peran / Hak Akses <span class="text-rose-500">*</span>
                        </label>
                        <select name="role" 
                                x-model="role"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-medium text-slate-800">
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400 mt-1 block">Tingkatan wewenang pengguna dalam sistem.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Penempatan Toko Retail
                        </label>
                        <select name="assigned_store_id" 
                                x-model="assigned_store_id"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-medium text-slate-800">
                            <option value="">Pusat / Semua Toko</option>
                            @foreach($stores as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400 mt-1 block">Wajib dipilih bagi kasir yang bertugas di toko tertentu.</span>
                    </div>
                </div>

                <!-- PIN Supervisor & Nomor Telepon -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div x-show="role === 'manager' || role === 'admin'" x-transition>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            PIN Supervisor (4-10 Digit)
                        </label>
                        <input type="password" 
                               name="supervisor_pin" 
                               x-model="supervisor_pin"
                               maxlength="10"
                               placeholder="Contoh: 123456"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 font-mono tracking-widest text-slate-800">
                        <span class="text-[10px] text-slate-400 mt-1 block">Untuk menyetujui void transaksi penjualan di POS.</span>
                    </div>

                    <div :class="{'col-span-2': role === 'cashier'}">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nomor Telepon / WA (Opsional)
                        </label>
                        <input type="text" 
                               name="phone" 
                               x-model="phone"
                               placeholder="08123456789"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 text-slate-800">
                    </div>
                </div>

                <!-- Status Akun -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Status Akun
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="active" x-model="status" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span class="text-xs font-semibold text-slate-800">Aktif (Dapat Login)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="inactive" x-model="status" class="w-4 h-4 text-rose-600 focus:ring-rose-500 border-slate-300">
                            <span class="text-xs font-semibold text-slate-800">Nonaktif (Diblokir)</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-button type="button" @click="showModal = false" variant="secondary" size="md">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="md">
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna'"></span>
                    </x-button>
                </div>
            </form>
        </x-modal>

    </div>
</x-layouts.app>
