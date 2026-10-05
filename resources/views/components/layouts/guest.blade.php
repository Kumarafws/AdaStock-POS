<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AdaStock POS') }} — Masuk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col py-4 sm:py-6 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">
    <div class="my-auto w-full max-w-md mx-auto">
        <!-- Logo & Brand Header (Horizontal inline to save vertical space & prevent cutoff) -->
        <div class="text-center mb-3 sm:mb-4">
            <div class="inline-flex items-center justify-center gap-2.5 mb-1">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 shadow-md shadow-indigo-600/30 text-white font-black text-lg tracking-tighter ring-4 ring-indigo-500/20">
                    AS
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">Ada<span class="text-indigo-400">Stock</span></h1>
            </div>
            <p class="text-xs text-slate-400 font-medium">Sistem Retail Inventory & Point of Sale Terpadu</p>
        </div>

        <!-- Form Card Container -->
        <div class="bg-white text-slate-800 p-5 sm:p-6 shadow-2xl rounded-2xl border border-slate-100/10 backdrop-blur-xl">
            {{ $slot }}
        </div>

        <p class="mt-3 text-center text-[11px] text-slate-400">
            &copy; {{ date('Y') }} AdaStock System. Dilindungi arsitektur MVC & OOP Laravel 13.
        </p>
    </div>
</body>
</html>
