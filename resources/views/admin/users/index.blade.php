<x-layouts.app title="Kelola Pengguna">
    <x-slot:header>
        Kelola Pengguna & Hak Akses
    </x-slot:header>
    <x-slot:subtitle>
        Daftar seluruh akun pengguna sistem, peran (Admin, Manager, Kasir), dan penempatan toko.
    </x-slot:subtitle>

    <div class="space-y-6">
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach(\App\Models\User::with('assignedStore')->get() as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <span class="font-bold text-slate-900">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="font-medium text-slate-800 text-xs">{{ $u->username }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </td>
                                <td class="py-3.5 px-6">
                                    <x-badge :variant="$u->role->value === 'admin' ? 'indigo' : ($u->role->value === 'manager' ? 'amber' : 'emerald')">
                                        {{ $u->role->label() }}
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-6 text-xs text-slate-600">
                                    @if($u->assignedStore)
                                        <span class="font-medium text-slate-800">{{ $u->assignedStore->name }}</span>
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $u->assignedStore->code }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Pusat / Semua Lokasi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-xs">
                                    @if($u->supervisor_pin)
                                        <span class="text-emerald-700 font-mono font-medium">●●●●●● (Terset)</span>
                                    @else
                                        <span class="text-slate-400 italic">Tidak Diperlukan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
