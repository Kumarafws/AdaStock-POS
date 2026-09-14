<x-layouts.app title="Audit Log Pembatalan Nota (Void)" header="Audit Log Transaksi Void">
    <div class="space-y-6">

        <!-- Top Header & Description -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Audit Trail Pembatalan Struk (Void)</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Rekaman log otorisasi pembatalan transaksi kasir dengan persetujuan PIN Supervisor.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('pos.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-colors shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke POS
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('pos.void-logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Cari Nomor Struk</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="INV/2026..." 
                           class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                        <a href="{{ route('pos.void-logs') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Void Logs Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Waktu Void</th>
                            <th class="px-4 py-4">No. Struk</th>
                            <th class="px-4 py-4">Toko</th>
                            <th class="px-4 py-4">Kasir Pemohon</th>
                            <th class="px-4 py-4">Otorisasi Supervisor</th>
                            <th class="px-4 py-4">Alasan Void</th>
                            <th class="px-4 py-4 text-right">Nilai Dibatalkan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700">
                                    <div class="font-bold">{{ $log->voided_at->format('d/m/Y H:i') }} WIB</div>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $log->voided_at->diffForHumans() }}</span>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <a href="{{ route('pos.receipt', $log->sale) }}" class="font-mono text-xs font-bold text-rose-600 hover:underline">
                                        {{ $log->sale->sale_number }}
                                    </a>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700">
                                    {{ $log->sale->location->name }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs font-bold text-slate-900">
                                    {{ $log->cashier->name }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap text-xs">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-bold text-[11px]">
                                        <svg class="w-3 h-3 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                        </svg>
                                        {{ $log->supervisor->name }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-xs">
                                    <div class="font-semibold text-slate-800">{{ $log->reason }}</div>
                                    @if($log->notes)
                                        <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $log->notes }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-xs font-bold text-rose-600">
                                    Rp {{ number_format($log->sale->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('pos.receipt', $log->sale) }}" 
                                       class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors shadow-2xs">
                                        Lihat Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <p class="font-bold text-slate-700">Belum ada catatan transaksi void</p>
                                    <p class="text-xs text-slate-500 mt-1">Seluruh pembatalan nota penjualan yang disetujui supervisor akan terdokumentasi di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
