<x-layouts.guest>
    <div x-data="{
        login: '{{ old('login') }}',
        password: '',
        setRole(role) {
            if(role === 'admin') {
                this.login = 'admin';
                this.password = 'password';
            } else if(role === 'manager') {
                this.login = 'manager';
                this.password = 'password';
            } else if(role === 'warehouse') {
                this.login = 'warehouse';
                this.password = 'password';
            } else if(role === 'cashier') {
                this.login = 'cashier';
                this.password = 'password';
            }
        }
    }">
        <div class="mb-3">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Masuk ke Sistem</h2>
            <p class="text-xs text-slate-500">Silakan masukkan username/email dan kata sandi Anda.</p>
        </div>

        <!-- Quick Demo Switcher (4 Roles: Admin, Store Manager, Warehouse Staff, Cashier) -->
        <div class="mb-3 p-2.5 bg-slate-50 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Pilih Cepat Akun Demo:</p>
                <span class="text-[10px] text-slate-400 font-medium">Klik untuk isi otomatis</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                <button type="button" @click="setRole('admin')" class="px-2 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 text-indigo-700 text-xs font-semibold border border-indigo-200 transition-colors text-center">
                    Admin
                </button>
                <button type="button" @click="setRole('manager')" class="px-2 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 active:bg-amber-200 text-amber-700 text-xs font-semibold border border-amber-200 transition-colors text-center">
                    Manajer Toko
                </button>
                <button type="button" @click="setRole('warehouse')" class="px-2 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 active:bg-blue-200 text-blue-700 text-xs font-semibold border border-blue-200 transition-colors text-center">
                    Staf Gudang
                </button>
                <button type="button" @click="setRole('cashier')" class="px-2 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 active:bg-emerald-200 text-emerald-700 text-xs font-semibold border border-emerald-200 transition-colors text-center">
                    Kasir (POS)
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-3">
            @csrf

            <div>
                <label for="login" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Username / Email
                </label>
                <input id="login" 
                       name="login" 
                       type="text" 
                       x-model="login"
                       required 
                       autofocus
                       autocomplete="username"
                       placeholder="Masukkan username atau email"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-900 bg-slate-50/50">
            </div>

            <div>
                <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Kata Sandi
                </label>
                <input id="password" 
                       name="password" 
                       type="password" 
                       x-model="password"
                       required 
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-900 bg-slate-50/50">
            </div>

            <div class="flex items-center justify-between pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-xs font-medium text-slate-600">Ingat Saya</span>
                </label>
            </div>

            <div class="pt-1">
                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
                    Masuk Sekarang
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
