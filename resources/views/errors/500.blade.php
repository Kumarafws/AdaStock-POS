<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Kesalahan Server | AdaStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800 antialiased font-sans">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-rose-50 border border-rose-200/60 flex items-center justify-center text-rose-600 shadow-sm">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <div>
            <span class="text-xs font-mono font-bold tracking-widest text-rose-600 uppercase">Error 500</span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">Kesalahan Sistem Internal</h1>
            <p class="text-sm text-slate-500 mt-2">
                Terjadi kendala teknis pada server saat memproses permintaan Anda. Tim kami telah mencatat peristiwa ini.
            </p>
        </div>
        <div class="pt-2 flex items-center justify-center gap-3">
            <a href="javascript:location.reload()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                Coba Lagi
            </a>
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-xs shadow-indigo-200 transition-colors">
                Ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
