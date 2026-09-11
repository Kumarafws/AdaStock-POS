<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
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
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 shadow-xl shadow-indigo-600/30 text-white font-black text-2xl tracking-tighter mb-4 ring-4 ring-indigo-500/20">
            AS
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white">Ada<span class="text-indigo-400">Stock</span></h1>
        <p class="mt-1 text-sm text-slate-400 font-medium">Sistem Retail Inventory & Point of Sale Terpadu</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white text-slate-800 py-8 px-6 shadow-2xl rounded-3xl sm:px-10 border border-slate-100/10 backdrop-blur-xl">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} AdaStock System. Dilindungi arsitektur MVC & OOP Laravel 13.
        </p>
    </div>
</body>
</html>
