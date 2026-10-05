<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Sesi Kedaluwarsa | AdaStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800 antialiased font-sans">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shadow-sm">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <div>
            <span class="text-xs font-mono font-bold tracking-widest text-blue-600 uppercase">Error 419</span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">Sesi Telah Kedaluwarsa</h1>
            <p class="text-sm text-slate-500 mt-2">
                Token keamanan formulir Anda telah berakhir karena tidak ada aktivitas. Silakan muat ulang halaman atau login kembali.
            </p>
        </div>
        <div class="pt-2 flex items-center justify-center gap-3">
            <a href="javascript:location.reload()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                Muat Ulang Halaman
            </a>
            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-xs shadow-indigo-200 transition-colors">
                Masuk Kembali
            </a>
        </div>
    </div>
</body>
</html>
