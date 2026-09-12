<x-layouts.app title="Manajemen Shift Kasir" header="Shift Register Kasir">
    <div class="space-y-6">

        <!-- Active Shift Alert Banner (If Cashier has active open shift) -->
        @if($activeShift)
            <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-white text-emerald-800">
                                Shift Aktif Anda
                            </span>
                            <span class="font-mono text-xs font-bold text-emerald-100">{{ $activeShift->shift_number }}</span>
                        </div>
                        <p class="text-sm font-bold mt-1">
                            Dibuka sejak {{ $activeShift->opened_at->format('d/m/Y H:i') }} WIB ({{ $activeShift->duration }}) di {{ $activeShift->location->name }}
                        </p>
                        <p class="text-xs text-emerald-100 mt-0.5">
                            Modal Kas Awal: <span class="font-bold">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('pos.index') }}" 
                       class="px-4 py-2 rounded-xl bg-white text-emerald-800 font-bold text-xs hover:bg-emerald-50 transition-colors shadow-xs">
                        Buka POS Kasir &rarr;
                    </a>
                    <a href="{{ route('shifts.close.form', $activeShift) }}" 
                       class="px-4 py-2 rounded-xl bg-emerald-800/60 hover:bg-emerald-800 text-white font-bold text-xs border border-emerald-500/40 transition-colors">
                        Tutup Shift
                    </a>
                </div>
            </div>
        @endif

        <!-- Top Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Shift & Register Kasir</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Catatan buka-tutup register kasir, modal kas laci, dan rekonsiliasi kas fisik (*Z-Report*).
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                @if(!$activeShift)
                    <a href="{{ route('shifts.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buka Shift Kasir Baru
                    </a>
                @endif
            </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Shift Berjalan</p>
                        <p class="text-2xl font-extrabold text-emerald-600 mt-1 tabular-nums">{{ number_format($summary['total_active']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Register saat ini sedang aktif</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Shift Ditutup</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-1 tabular-nums">{{ number_format($summary['total_closed']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Telah direkonsiliasi laci kas</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omzet Kasir</p>
                        <p class="text-2xl font-extrabold text-indigo-600 mt-1 tabular-nums">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Akumulasi seluruh penjualan kasir</p>
            </x-card>

            <x-card class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Selisih Kas Fisik</p>
                        <p class="text-2xl font-extrabold tabular-nums mt-1 {{ $summary['total_difference'] < 0 ? 'text-rose-600' : ($summary['total_difference'] > 0 ? 'text-emerald-600' : 'text-slate-900') }}">
                            Rp {{ number_format($summary['total_difference'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl {{ $summary['total_difference'] < 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Total selisih laci saat tutup shift</p>
            </x-card>
        </div>

        <!-- Filter Bar (For Manager/Admin) -->
        @if(!$isCashier)
            <x-card class="p-5">
                <form method="GET" action="{{ route('shifts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kasir</label>
                        <select name="user_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            <option value="">Semua Kasir</option>
                            @foreach($cashiers as $c)
                                <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Toko</label>
                        <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            <option value="">Semua Toko</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                    [{{ $loc->code }}] {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status</label>
                        <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                            <option value="">Semua Status</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Shift Aktif (Buka)</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Selesai (Tutup)</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Buka</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                    </div>

                    <div class="lg:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                            Filter
                        </button>
                    </div>
                </form>
            </x-card>
        @endif

        <!-- Shifts Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Shift</th>
                            <th class="px-4 py-4">Kasir</th>
                            <th class="px-4 py-4">Toko</th>
                            <th class="px-4 py-4">Waktu Buka / Tutup</th>
                            <th class="px-4 py-4">Durasi</th>
                            <th class="px-4 py-4 text-right">Modal Awal</th>
                            <th class="px-4 py-4 text-right">Penjualan</th>
                            <th class="px-4 py-4 text-right">Selisih Kas</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($shifts as $shift)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('shifts.show', $shift) }}" class="font-mono text-xs font-bold text-indigo-600 hover:underline">
                                        {{ $shift->shift_number }}
                                    </a>
                                </td>

                                <td class="px-4 py-4 font-bold text-slate-900">
                                    {{ $shift->cashier->name }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700">
                                    {{ $shift->location->name }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-600">
                                    <div>Buka: <span class="font-semibold text-slate-800">{{ $shift->opened_at->format('d/m/Y H:i') }}</span></div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Tutup: {{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-600 font-medium">
                                    {{ $shift->duration }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-xs font-semibold text-slate-800">
                                    Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-bold text-slate-900">
                                    Rp {{ number_format($shift->total_sales_amount, 0, ',', '.') }}
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $shift->total_transactions_count }} transaksi</div>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-bold">
                                    @if($shift->isClosed())
                                        @if($shift->cash_difference == 0)
                                            <span class="text-slate-600">Rp 0 (Pas)</span>
                                        @elseif($shift->cash_difference > 0)
                                            <span class="text-emerald-600">+Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-rose-600">-Rp {{ number_format(abs($shift->cash_difference), 0, ',', '.') }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 font-normal">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $shift->status->badgeClass() }}">
                                        {{ $shift->status->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('shifts.show', $shift) }}" 
                                           class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors shadow-2xs">
                                            Laporan
                                        </a>

                                        @if($shift->isOpen())
                                            <a href="{{ route('shifts.close.form', $shift) }}" 
                                               class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition-colors border border-rose-200">
                                                Tutup Shift
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada riwayat shift kasir</p>
                                    <p class="text-xs text-slate-500 mt-1">Buka register shift baru untuk mulai melayani transaksi penjualan kasir.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($shifts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $shifts->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
