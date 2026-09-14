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
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 font-sans antialiased text-slate-800">

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
            <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.048-.32-2.124-.32-3.21a6.72 6.72 0 1113.44 0c0 1.086-.08 2.162-.32 3.21M18 19.5H6a2.25 2.25 0 01-2.25-2.25V9a2.25 2.25 0 012.25-2.25h12A2.25 2.25 0 0120.25 9v8.25A2.25 2.25 0 0118 19.5z" />
                </svg>
                Cetak Struk
            </button>
        </div>
    </div>

    <!-- Thermal Receipt Preview -->
    <div class="thermal-receipt max-w-sm mx-auto bg-white p-6 rounded-2xl shadow-md border border-slate-200 text-slate-900 font-mono text-xs leading-relaxed">
        
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

</body>
</html>
