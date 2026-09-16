<x-layouts.app title="Stock Opname Multi-Lokasi" header="Stock Opname & Rekonsiliasi Fisik">
    <div class="space-y-6">

        <!-- Top Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Stock Opname & Rekonsiliasi Fisik</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Hitung fisik inventori berkala di toko atau gudang, pantau selisih lebih/kurang, dan lakukan penyesuaian otomatis ke buku besar stok.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.ledger') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    Buku Besar Stok
                </a>

                <a href="{{ route('opnames.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 text-white font-semibold text-sm hover:bg-purple-700 shadow-sm shadow-purple-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    + Mulai Stock Opname Baru
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card class="p-5 border-l-4 border-l-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sesi Aktif Berjalan</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($activeCount) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sesi Selesai (Adjusted)</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($completedCount) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akumulasi Selisih Fisik</p>
                        <p class="text-2xl font-black {{ $totalVarianceQty < 0 ? 'text-rose-600' : ($totalVarianceQty > 0 ? 'text-emerald-600' : 'text-slate-900') }} mt-1">
                            {{ $totalVarianceQty > 0 ? '+' : '' }}{{ number_format($totalVarianceQty) }} unit
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Deviasi Nilai Buku (HPP)</p>
                        <p class="text-2xl font-black {{ $totalVarianceAmount < 0 ? 'text-rose-600' : ($totalVarianceAmount > 0 ? 'text-emerald-600' : 'text-slate-900') }} mt-1">
                            {{ $totalVarianceAmount > 0 ? '+Rp ' : ($totalVarianceAmount < 0 ? '-Rp ' : 'Rp ') }}{{ number_format(abs($totalVarianceAmount), 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('opnames.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi Stok</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Sesi</label>
                    <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 py-2">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-purple-500 focus:ring-purple-500 py-2">
                </div>

                <div class="lg:col-span-3 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter Sesi
                    </button>
                    @if(request()->anyFilled(['location_id', 'status', 'start_date', 'end_date', 'search']))
                        <a href="{{ route('opnames.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Opnames Data Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Opname</th>
                            <th class="px-4 py-4">Tanggal</th>
                            <th class="px-4 py-4">Lokasi Opname</th>
                            <th class="px-4 py-4">Cakupan Kategori</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-4 py-4 text-center">Fisik / Sistem</th>
                            <th class="px-4 py-4 text-right">Selisih Qty</th>
                            <th class="px-4 py-4 text-right">Deviasi Nilai (HPP)</th>
                            <th class="px-4 py-4">Dibuat Oleh</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($opnames as $opname)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-purple-600">
                                    <a href="{{ route('opnames.show', $opname) }}" class="hover:underline">
                                        {{ $opname->opname_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-slate-700">
                                    {{ $opname->opname_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900">{{ $opname->location->name }}</div>
                                    <div class="text-xs font-mono text-slate-400">[{{ $opname->location->code }}]</div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $opname->category?->name ?? 'Semua Produk' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $opname->status->badgeClass() }}">
                                        {{ $opname->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center font-mono text-xs whitespace-nowrap">
                                    <span class="font-bold text-slate-900">{{ number_format($opname->total_physical_qty) }}</span>
                                    <span class="text-slate-400"> / {{ number_format($opname->total_system_qty) }}</span>
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-bold whitespace-nowrap {{ $opname->total_variance_qty < 0 ? 'text-rose-600' : ($opname->total_variance_qty > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                                    {{ $opname->total_variance_qty > 0 ? '+' : '' }}{{ number_format($opname->total_variance_qty) }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-bold whitespace-nowrap text-xs {{ $opname->total_variance_amount < 0 ? 'text-rose-600' : ($opname->total_variance_amount > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                                    {{ $opname->formatted_variance_amount }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700">
                                    {{ $opname->creator->name }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('opnames.show', $opname) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-purple-600 transition-colors shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        {{ $opname->isEditable() ? 'Lembar Kerja' : 'Detail & Slip' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada sesi Stock Opname</p>
                                        <p class="text-xs text-slate-400 mt-1 max-w-sm">
                                            Mulai sesi opname untuk membandingkan stok fisik di toko atau gudang terhadap saldo sistem.
                                        </p>
                                        <a href="{{ route('opnames.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 text-white font-semibold text-xs hover:bg-purple-700 transition-colors">
                                            + Mulai Stock Opname Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($opnames->hasPages())
                <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                    {{ $opnames->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
