<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan | AdaStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800 antialiased font-sans">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-50 border border-indigo-200/60 flex items-center justify-center text-indigo-600 shadow-sm">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
        <div>
            <span class="text-xs font-mono font-bold tracking-widest text-indigo-600 uppercase">Error 404</span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">Halaman Tidak Ditemukan</h1>
            <p class="text-sm text-slate-500 mt-2">
                Tautan atau halaman yang Anda cari mungkin telah dipindahkan, diubah namanya, atau tidak tersedia.
            </p>
        </div>
        <div class="pt-2 flex items-center justify-center gap-3">
            <a href="{{ url()->previous() ?: route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                Kembali
            </a>
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-xs shadow-indigo-200 transition-colors">
                Ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
