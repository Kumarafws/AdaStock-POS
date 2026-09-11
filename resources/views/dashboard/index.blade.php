<x-layouts.app title="Dashboard Utama">
    <x-slot:header>
        Dashboard Operasional
    </x-slot:header>
    <x-slot:subtitle>
        Ringkasan status sistem, multi-lokasi, dan status operasional aktif.
    </x-slot:subtitle>

    <div class="space-y-6">

        <!-- Welcome Banner -->
        <div class="p-6 lg:p-8 rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white shadow-xl shadow-indigo-950/10 border border-indigo-700/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-200 text-xs font-semibold mb-3 border border-indigo-400/20">
                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                    Sesi Aktif: {{ $user->role->label() }}
                </div>
                <h2 class="text-2xl lg:text-3xl font-extrabold tracking-tight">
                    Halo, {{ $user->name }}!
                </h2>
                <p class="mt-1 text-sm text-indigo-200/80 max-w-xl">
                    @if($user->isCashier())
                        Anda bertugas di <strong>{{ $assignedStore->name ?? 'Toko Belum Ditentukan' }}</strong>. Pastikan untuk membuka register shift sebelum memulai transaksi kasir.
                    @else
                        Sistem AdaStock POS & Inventory Management berjalan normal. Seluruh pergerakan stok dicatat pada buku besar mutasi yang terintegrasi.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @if($user->isCashier())
                    <x-button href="{{ route('shifts.index') }}" variant="emerald" size="lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Buka / Tutup Shift
                    </x-button>

                    <x-button href="{{ route('pos.index') }}" variant="primary" size="lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        Layar Kasir (POS)
                    </x-button>
                @else
                    <x-button href="{{ route('locations.index') }}" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        Kelola Lokasi
                    </x-button>
                    @if($user->isAdmin())
                        <x-button href="{{ route('admin.users.index') }}" variant="primary" size="md">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            Kelola Pengguna
                        </x-button>
                    @endif
                @endif
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Toko Retail (POS)" 
                         :value="$stats['stores_count'] . ' Unit'" 
                         color="emerald">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.651V9.35m0 0a3.001 3.001 0 003.75-.614A2.993 2.993 0 009 9.35c.98 0 1.86-.47 2.41-1.2m0 0a2.993 2.993 0 002.41 1.2 2.993 2.993 0 002.41-1.2m0 0a3.001 3.001 0 003.75.614" />
                    </svg>
                </x-slot:icon>
                <x-slot:subtext>Titik transaksi penjualan aktif</x-slot:subtext>
            </x-stat-card>

            <x-stat-card title="Gudang Distribusi" 
                         :value="$stats['warehouses_count'] . ' Unit'" 
                         color="blue">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </x-slot:icon>
                <x-slot:subtext>Sentra stok & penerimaan supplier</x-slot:subtext>
            </x-stat-card>

            <x-stat-card title="Gudang Karantina" 
                         :value="$stats['quarantine_count'] . ' Unit'" 
                         color="rose">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </x-slot:icon>
                <x-slot:subtext>Penampungan barang rusak & retur</x-slot:subtext>
            </x-stat-card>

            <x-stat-card title="Pengguna Terdaftar" 
                         :value="$stats['active_users_count'] . ' Akun'" 
                         color="indigo">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </x-slot:icon>
                <x-slot:subtext>Admin, Manager, dan Kasir</x-slot:subtext>
            </x-stat-card>
        </div>

        <!-- Multi-Location Master Overview -->
        <x-card title="Jaringan Toko & Gudang (Multi-Location)" subtitle="Lokasi operasional aktif untuk penyimpanan inventori dan penjualan POS.">
            <x-slot:headerAction>
                @if($user->isAdmin() || $user->isManager())
                    <x-button href="{{ route('locations.index') }}" variant="secondary" size="sm">
                        Lihat Selengkapnya
                    </x-button>
                @endif
            </x-slot:headerAction>

            <div class="overflow-x-auto -mx-6 -my-6">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-6">Kode</th>
                            <th class="py-3 px-6">Nama Lokasi</th>
                            <th class="py-3 px-6">Tipe Lokasi</th>
                            <th class="py-3 px-6">Alamat</th>
                            <th class="py-3 px-6">Telepon</th>
                            <th class="py-3 px-6 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(\App\Models\Location::all() as $loc)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-bold text-xs text-slate-700">{{ $loc->code }}</td>
                                <td class="py-3.5 px-6 font-semibold text-slate-900">{{ $loc->name }}</td>
                                <td class="py-3.5 px-6">
                                    <x-badge :variant="$loc->type->value === 'store' ? 'emerald' : ($loc->type->value === 'warehouse' ? 'blue' : 'rose')" size="sm">
                                        {{ $loc->type->label() }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-6 text-slate-500 text-xs truncate max-w-xs">{{ $loc->address ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-slate-500 text-xs">{{ $loc->phone ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-center">
                                    @if($loc->is_active)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>
</x-layouts.app>
