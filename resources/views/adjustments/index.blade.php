<x-layouts.app title="Penyesuaian Stok" header="Penyesuaian Stok">
    <div class="space-y-6">

        <!-- Top Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Berita Acara Penyesuaian Stok</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Dokumen resmi penyesuaian kuantitas fisik, mutasi barang rusak ke karantina, dan write-off pemusnahan.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Saldo Inventori
                </a>

                <a href="{{ route('adjustments.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    + Buat Penyesuaian Baru
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-card class="p-5">
            <form method="GET" action="{{ route('adjustments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                
                <!-- Location Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Lokasi</label>
                    <select name="location_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                [{{ $loc->code }}] {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reason Filter -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Alasan</label>
                    <select name="reason" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Alasan</option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason->value }}" {{ request('reason') === $reason->value ? 'selected' : '' }}>
                                {{ $reason->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Type Filter -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tipe Tindakan</label>
                    <select name="action_type" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                        <option value="">Semua Tindakan</option>
                        <option value="normal" {{ request('action_type') === 'normal' ? 'selected' : '' }}>Penyesuaian Normal</option>
                        <option value="to_quarantine" {{ request('action_type') === 'to_quarantine' ? 'selected' : '' }}>Karantina Rusak</option>
                        <option value="disposal" {{ request('action_type') === 'disposal' ? 'selected' : '' }}>Pemusnahan / Write-off</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pencarian</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="No. Penyesuaian, produk, catatan..." 
                           class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>

                <!-- Buttons -->
                <div class="lg:col-span-1 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-xs">
                        Filter
                    </button>
                </div>
            </form>
        </x-card>

        <!-- Adjustment Records Table -->
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Berita Acara</th>
                            <th class="px-4 py-4">Tanggal</th>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-4 py-4">Lokasi</th>
                            <th class="px-4 py-4 text-right">Kuantitas</th>
                            <th class="px-4 py-4">Alasan</th>
                            <th class="px-4 py-4 text-center">Tindakan</th>
                            <th class="px-4 py-4 text-right">Nilai HPP</th>
                            <th class="px-4 py-4">Petugas</th>
                            <th class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($adjustments as $adj)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Document Number -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-mono text-xs font-bold text-indigo-600">
                                        {{ $adj->adjustment_number }}
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500">
                                    <div class="font-bold text-slate-800">{{ $adj->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $adj->created_at->format('H:i') }} WIB</div>
                                </td>

                                <!-- Product -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $adj->product->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $adj->product->sku }}</div>
                                </td>

                                <!-- Location -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $adj->location->type->value === 'quarantine' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ $adj->location->code }}
                                    </span>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ $adj->location->name }}</div>
                                </td>

                                <!-- Quantity -->
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums">
                                    @if($adj->type === 'in')
                                        <span class="text-base font-extrabold text-emerald-600">
                                            +{{ number_format($adj->quantity) }}
                                        </span>
                                    @else
                                        <span class="text-base font-extrabold text-rose-600">
                                            -{{ number_format($adj->quantity) }}
                                        </span>
                                    @endif
                                    <span class="text-xs font-semibold text-slate-500">{{ $adj->product->base_unit_name }}</span>
                                </td>

                                <!-- Reason -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $adj->reason->badgeClass() }}">
                                        {{ $adj->reason->label() }}
                                    </span>
                                </td>

                                <!-- Action Type Badge -->
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    @if($adj->action_type === 'to_quarantine')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            Pindah Karantina
                                        </span>
                                    @elseif($adj->action_type === 'disposal')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Pemusnahan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            Normal
                                        </span>
                                    @endif
                                </td>

                                <!-- Cost Impact -->
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-xs font-semibold text-slate-800">
                                    Rp {{ number_format($adj->quantity * $adj->cogs_at_time, 0, ',', '.') }}
                                    <div class="text-[10px] text-slate-400">@ Rp {{ number_format($adj->cogs_at_time, 0, ',', '.') }}</div>
                                </td>

                                <!-- Adjuster -->
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700 font-semibold">
                                    {{ $adj->adjuster->name ?? '-' }}
                                </td>

                                <!-- Notes -->
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate" title="{{ $adj->notes }}">
                                    {{ $adj->notes ?? '-' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada berita acara penyesuaian</p>
                                    <p class="text-xs text-slate-500 mt-1">Klik tombol "+ Buat Penyesuaian Baru" untuk mencatat selisih fisik atau barang rusak.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($adjustments->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
