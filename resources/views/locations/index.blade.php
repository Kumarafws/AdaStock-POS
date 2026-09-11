<x-layouts.app title="Toko & Gudang">
    <x-slot:header>
        Manajemen Toko & Gudang
    </x-slot:header>
    <x-slot:subtitle>
        Daftar seluruh store retail, gudang logistik, dan area karantina barang rusak.
    </x-slot:subtitle>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($locations as $location)
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="font-mono text-xs font-bold px-2 py-1 rounded-md bg-slate-100 text-slate-700">
                                {{ $location->code }}
                            </span>
                            <x-badge :variant="$location->type->value === 'store' ? 'emerald' : ($location->type->value === 'warehouse' ? 'blue' : 'rose')" size="sm">
                                {{ $location->type->label() }}
                            </x-badge>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $location->name }}</h3>
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $location->address ?? 'Alamat belum diatur' }}</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                            <span>Telepon:</span>
                            <span class="font-medium text-slate-800">{{ $location->phone ?? '—' }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-xs text-slate-600">
                            <span>Staf Terdaftar:</span>
                            <span class="font-medium text-slate-800">{{ $location->users_count }} Pengguna</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Aktif Operasional
                        </span>
                        <span class="text-xs text-slate-400 font-mono">ID: #{{ $location->id }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
