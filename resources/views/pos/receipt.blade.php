<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran {{ $sale->sale_number }} - AdaStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .thermal-receipt {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 80mm !important;
                padding: 4mm !important;
                margin: 0 auto !important;
            }
        }
    </style>
</head>
<body x-data="{ isVoidModalOpen: false }" class="bg-slate-100 min-h-screen p-4 sm:p-8 font-sans antialiased text-slate-800">

    <!-- Flash Messages (No-print) -->
    @if(session('success'))
        <div class="max-w-md mx-auto mb-4 no-print p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-2xs">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-md mx-auto mb-4 no-print p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-2xs">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-md mx-auto mb-6 no-print flex items-center justify-between gap-3">
        <a href="{{ route('pos.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors shadow-2xs">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Kasir (POS)
        </a>

        <div class="flex items-center gap-2">
            @if(!$sale->isVoided() && $sale->status !== \App\Enums\SaleStatus::RETURNED_FULL)
                <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    Retur Barang
                </a>
            @endif

            @if($sale->isCompleted() && $sale->shift->isOpen())
                <button type="button" 
                        @click="isVoidModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Batalkan (Void)
                </button>
            @endif

            <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.048-.32-2.124-.32-3.21a6.72 6.72 0 1113.44 0c0 1.086-.08 2.162-.32 3.21M18 19.5H6a2.25 2.25 0 01-2.25-2.25V9a2.25 2.25 0 012.25-2.25h12A2.25 2.25 0 0120.25 9v8.25A2.25 2.25 0 0118 19.5z" />
                </svg>
                Cetak Struk
            </button>
        </div>
    </div>

    <!-- Void Notice Banner (If Sale is Voided) -->
    @if($sale->isVoided())
        <div class="max-w-sm mx-auto mb-4 p-4 rounded-2xl bg-rose-600 text-white shadow-md text-center">
            <div class="flex items-center justify-center gap-2 font-black text-sm uppercase tracking-wider">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                TRANSAKSI INI TELAH DIBATALKAN (VOID)
            </div>
            @if($sale->voidLog)
                <p class="text-[11px] text-rose-100 mt-1">
                    Disetujui oleh Supervisor <strong>{{ $sale->voidLog->supervisor->name }}</strong> pada {{ $sale->voidLog->voided_at->format('d/m/Y H:i') }} WIB.
                </p>
                <p class="text-[11px] text-rose-200 mt-0.5">
                    Alasan: "{{ $sale->voidLog->reason }}"
                </p>
            @endif
        </div>
    @endif

    <!-- Thermal Receipt Preview -->
    <div class="thermal-receipt max-w-sm mx-auto bg-white p-6 rounded-2xl shadow-md border border-slate-200 text-slate-900 font-mono text-xs leading-relaxed relative overflow-hidden">
        
        @if($sale->isVoided())
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none z-10 opacity-15 rotate-[-25deg]">
                <span class="text-6xl font-black border-8 border-rose-600 text-rose-600 px-6 py-2 rounded-2xl uppercase tracking-widest">
                    VOIDED
                </span>
            </div>
        @endif

        <!-- Store Header -->
        <div class="text-center space-y-1 mb-4">
            <h1 class="text-base font-black tracking-wider uppercase">{{ $sale->location->name }}</h1>
            <p class="text-[11px] text-slate-600 leading-tight">{{ $sale->location->address }}</p>
            @if($sale->location->phone)
                <p class="text-[11px] text-slate-600">Telp: {{ $sale->location->phone }}</p>
            @endif
        </div>

        <div class="border-b border-dashed border-slate-400 my-3"></div>

        <!-- Sale Meta -->
        <div class="space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>No. Nota:</span>
                <span class="font-bold">{{ $sale->sale_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu:</span>
                <span>{{ $sale->transaction_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $sale->cashier->name }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span>{{ $sale->customer_name }}</span>
            </div>
            <div class="flex justify-between">
                <span>Status:</span>
                <span class="font-bold {{ $sale->isVoided() ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $sale->status->label() }}
                </span>
            </div>
        </div>

        <div class="border-b border-dashed border-slate-400 my-3"></div>

        <!-- Items Table -->
        <div class="space-y-2 text-[11px]">
            @foreach($sale->items as $item)
                <div>
                    <div class="font-bold text-slate-900">{{ $item->product->name }}</div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>
                            {{ $item->quantity }} {{ $item->unit_name }} x {{ number_format($item->unit_price, 0, ',', '.') }}
                            @if($item->discount_amount > 0)
                                <span class="text-rose-600">(-{{ number_format($item->discount_amount, 0, ',', '.') }})</span>
                            @endif
                        </span>
                        <span class="font-bold text-slate-900">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-b border-dashed border-slate-400 my-3"></div>

        <!-- Totals Summary -->
        <div class="space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
            </div>

            @if($sale->discount_amount > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Diskon:</span>
                    <span>- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
                @if($sale->discountAuthorizer)
                    <div class="flex justify-between text-[10px] text-purple-600">
                        <span>Otorisasi Diskon:</span>
                        <span>{{ $sale->discountAuthorizer->name }}</span>
                    </div>
                @endif
            @endif

            <div class="flex justify-between font-bold text-sm text-slate-900 pt-1 border-t border-slate-300">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="border-b border-dashed border-slate-400 my-3"></div>

        <!-- Payments -->
        <div class="space-y-1 text-[11px]">
            @foreach($sale->payments as $payment)
                <div class="flex justify-between">
                    <span>Bayar ({{ $payment->payment_method->label() }}):</span>
                    <span class="font-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                @if($payment->reference_number)
                    <div class="flex justify-between text-[10px] text-slate-500">
                        <span>Ref:</span>
                        <span>{{ $payment->reference_number }}</span>
                    </div>
                @endif
            @endforeach

            <div class="flex justify-between font-bold pt-1 border-t border-slate-200">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="border-b border-dashed border-slate-400 my-4"></div>

        <!-- Footer Note -->
        <div class="text-center space-y-1 text-[10px] text-slate-500">
            <p class="font-semibold">Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan tanpa menyertakan struk ini.</p>
            <p class="pt-1 text-[9px] text-slate-400">AdaStock Retail POS System</p>
        </div>

    </div>

    <!-- MODAL: Supervisor Void Authorization (Screen Only) -->
    @if($sale->isCompleted() && $sale->shift->isOpen())
        <div x-show="isVoidModalOpen" 
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 no-print"
             style="display: none;">
            
            <div @click.away="isVoidModalOpen = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden">
                
                <div class="px-6 py-4 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-rose-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285zM12 17.25h.008v.008H12v-.008z" />
                        </svg>
                        <h3 class="font-bold text-sm text-rose-900">Otorisasi Pembatalan Nota (Void)</h3>
                    </div>
                    <button type="button" @click="isVoidModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('pos.void', $sale) }}" class="p-6 space-y-4">
                    @csrf

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Nota:</span>
                            <span class="font-mono font-bold text-slate-900">{{ $sale->sale_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nilai Transaksi:</span>
                            <span class="font-bold text-rose-600 tabular-nums">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alasan Pembatalan <span class="text-rose-500">*</span>
                        </label>
                        <select name="reason" required class="w-full text-xs font-semibold rounded-xl border-slate-300 py-2.5 focus:border-rose-500 focus:ring-rose-500">
                            <option value="Salah input barang belanja">Salah input barang belanja</option>
                            <option value="Pelanggan batal beli">Pelanggan batal beli</option>
                            <option value="Transaksi tercatat ganda">Transaksi tercatat ganda</option>
                            <option value="Salah metode pembayaran">Salah metode pembayaran</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            PIN Otorisasi Supervisor / Manager <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                               name="pin" 
                               required 
                               autocomplete="off"
                               placeholder="Masukkan 6-digit PIN..." 
                               class="w-full text-center text-lg font-black tracking-widest rounded-xl border-slate-300 py-2.5 focus:border-rose-500 focus:ring-rose-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan Tambahan (Opsional)
                        </label>
                        <textarea name="notes" rows="2" placeholder="Keterangan pendukung..." class="w-full text-xs rounded-xl border-slate-300 py-2 px-3 focus:border-rose-500 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="isVoidModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm shadow-rose-600/20 transition-colors">
                            Konfirmasi Void Nota
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</body>
</html>
