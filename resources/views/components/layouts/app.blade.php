<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'AdaStock POS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden" 
             @click="sidebarOpen = false"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" 
               class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">
            
            <!-- Sidebar Header / Brand -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/40">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 shadow-md shadow-indigo-600/30 flex items-center justify-center text-white font-extrabold text-lg">
                        AS
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white">Ada<span class="text-indigo-400">Stock</span></span>
                        <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-400">Retail & POS</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Current Store / Context Pill -->
            <div class="p-4 mx-4 mt-4 rounded-2xl bg-slate-800/60 border border-slate-700/60">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi Aktif</span>
                </div>
                <div class="mt-1 text-sm font-bold text-white truncate">
                    {{ auth()->user()->assignedStore->name ?? 'Semua Lokasi (Pusat)' }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    {{ auth()->user()->assignedStore->code ?? 'KONSOLIDASI' }}
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                
                <!-- General Section -->
                <div class="px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    Menu Utama
                </div>

                <a href="{{ route('dashboard') }}" 
                   class="{{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    Dashboard
                </a>

                <!-- Products Catalog (Accessible by all roles) -->
                <a href="{{ route('products.index') }}" 
                   class="{{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    Katalog Produk
                </a>

                <!-- Stock Inventory (Accessible by all roles) -->
                <a href="{{ route('inventory.index') }}" 
                   class="{{ request()->routeIs('inventory.index') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.247 2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z" />
                    </svg>
                    Saldo Inventori
                </a>

                <!-- POS Section (Cashier, Manager, Admin) -->
                <a href="{{ route('pos.index') }}" 
                   class="{{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    Kasir (POS)
                </a>

                <a href="{{ route('shifts.index') }}" 
                   class="{{ request()->routeIs('shifts.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                    <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Shift Kasir
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <a href="{{ route('pos.void-logs') }}" 
                       class="{{ request()->routeIs('pos.void-logs') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Log Transaksi Void
                    </a>
                @endif

                <!-- Inventory & Operations (Admin & Manager) -->
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <div class="pt-5 px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        Manajemen Stok & Mutasi
                    </div>

                    <a href="{{ route('inventory.ledger') }}" 
                       class="{{ request()->routeIs('inventory.ledger') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        Buku Besar Stok
                    </a>

                    <a href="{{ route('adjustments.index') }}" 
                       class="{{ request()->routeIs('adjustments.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        Penyesuaian Stok
                    </a>

                    <div class="pt-5 px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        Pengadaan & Pembelian
                    </div>

                    <a href="{{ route('purchasing.orders.index') }}" 
                       class="{{ request()->routeIs('purchasing.orders.*') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Pesanan Pembelian (PO)
                    </a>

                    <a href="{{ route('purchasing.receipts.index') }}" 
                       class="{{ request()->routeIs('purchasing.receipts.*') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.125c.621 0 1.125.504 1.125 1.125v3.75m-6.75-4.875H6a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25h1.5" />
                        </svg>
                        Penerimaan Barang (GR)
                    </a>

                    <a href="{{ route('purchasing.returns.index') }}" 
                       class="{{ request()->routeIs('purchasing.returns.*') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                        </svg>
                        Retur Supplier
                    </a>

                    <div class="pt-5 px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        Manajemen Master
                    </div>

                    <a href="{{ route('categories.index') }}" 
                       class="{{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        Kategori
                    </a>

                    <a href="{{ route('brands.index') }}" 
                       class="{{ request()->routeIs('brands.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" />
                        </svg>
                        Merek / Brand
                    </a>

                    <a href="{{ route('suppliers.index') }}" 
                       class="{{ request()->routeIs('suppliers.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.125c.621 0 1.125.504 1.125 1.125v3.75m-6.75-4.875H6a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25h1.5" />
                        </svg>
                        Pemasok / Supplier
                    </a>

                    <a href="{{ route('locations.index') }}" 
                       class="{{ request()->routeIs('locations.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        Toko & Gudang
                    </a>
                @endif

                <!-- Administration (Admin Only) -->
                @if(auth()->user()->isAdmin())
                    <div class="pt-5 px-3 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        Administrasi Sistem
                    </div>

                    <a href="{{ route('admin.users.index') }}" 
                       class="{{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all">
                        <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        Kelola Pengguna
                    </a>
                @endif
            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ auth()->user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Top Navbar -->
            <header class="h-20 bg-white border-b border-slate-200/80 flex items-center justify-between px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $header ?? $title ?? 'Dashboard' }}</h1>
                        @if(isset($subtitle))
                            <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-badge variant="indigo" size="md">
                        {{ auth()->user()->role->label() }}
                    </x-badge>

                    @if(auth()->user()->assignedStore)
                        <x-badge variant="emerald" size="md">
                            {{ auth()->user()->assignedStore->name }}
                        </x-badge>
                    @endif
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
                <!-- Flash Alerts -->
                @if(session('success'))
                    <x-alert type="success">{{ session('success') }}</x-alert>
                @endif
                @if(session('error'))
                    <x-alert type="error">{{ session('error') }}</x-alert>
                @endif
                @if(session('warning'))
                    <x-alert type="warning">{{ session('warning') }}</x-alert>
                @endif
                @if(session('info'))
                    <x-alert type="info">{{ session('info') }}</x-alert>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
