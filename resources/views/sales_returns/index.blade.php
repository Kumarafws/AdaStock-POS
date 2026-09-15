<x-layouts.app title="Retur Penjualan Pelanggan" header="Retur Penjualan Pelanggan">
    <div class="space-y-6">

        <!-- Top Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Retur Penjualan Pelanggan</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Kelola pengembalian barang dari pelanggan, pemisahan barang layak jual vs karantina rusak, dan pengembalian dana kasir.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    Buka Kasir POS
                </a>

                <a href="{{ route('returns.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 text-white font-semibold text-sm hover:bg-amber-700 shadow-sm shadow-amber-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    + Proses Retur Pelanggan
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card class="p-5 border-l-4 border-l-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Dokumen Retur</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalReturnsCount) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-rose-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Refund</p>
                        <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totalRefundAmount, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Refund Hari Ini</p>
                        <p class="text-2xl font-black text-blue-600 mt-1">Rp {{ number_format($todayRefundAmount, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                </div>
            </x-card>

            <x-card class="p-5 border-l-4 border-l-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Barang Rusak (Karantina)</p>
                        <p class="text-2xl font-black text-purple-600 mt-1">{{ number_format($damagedItemsCount) }} unit</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="No. Retur, No. Faktur, Pelanggan, Alasan..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Metode Refund</label>
                    <select name="refund_method" class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 py-2">
                        <option value="">Semua Metode</option>
                        @foreach($refundMethods as $method)
                            <option value="{{ $method->value }}" {{ request('refund_method') === $method->value ? 'selected' : '' }}>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 py-2">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 py-2">
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'refund_method', 'start_date', 'end_date']))
                        <a href="{{ route('returns.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Returns Data Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Retur</th>
                            <th class="px-4 py-4">Waktu Retur</th>
                            <th class="px-4 py-4">Ref. Faktur</th>
                            <th class="px-4 py-4">Pelanggan</th>
                            <th class="px-4 py-4">Jumlah Item</th>
                            <th class="px-4 py-4">Metode Refund</th>
                            <th class="px-4 py-4 text-right">Nilai Refund</th>
                            <th class="px-4 py-4">Kasir / Petugas</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($returns as $return)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-amber-600">
                                    <a href="{{ route('returns.show', $return) }}" class="hover:underline">
                                        {{ $return->return_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-slate-700">
                                    {{ $return->return_date->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="font-mono text-slate-900 font-medium">{{ $return->sale->sale_number }}</span>
                                </td>
                                <td class="px-4 py-4 font-medium text-slate-900">
                                    {{ $return->sale->customer_name ?: 'Pelanggan Umum' }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $return->total_quantity }} unit
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $return->refund_method->badgeClass() }}">
                                        {{ $return->refund_method->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                    {{ $return->formatted_total_refund }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-slate-700 text-xs">
                                    <div class="font-medium text-slate-800">{{ $return->cashier->name }}</div>
                                    @if($return->shift)
                                        <div class="text-slate-400 font-mono text-[10px]">{{ $return->shift->shift_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('returns.show', $return) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-amber-600 transition-colors shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Detail & Slip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada riwayat retur penjualan pelanggan</p>
                                        <p class="text-xs text-slate-400 mt-1 max-w-sm">
                                            Jika pelanggan ingin menukar atau mengembalikan barang, klik tombol di bawah untuk memproses retur.
                                        </p>
                                        <a href="{{ route('returns.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-600 text-white font-semibold text-xs hover:bg-amber-700 transition-colors">
                                            + Buat Retur Penjualan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($returns->hasPages())
                <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                    {{ $returns->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
