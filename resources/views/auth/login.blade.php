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
            } else if(role === 'cashier') {
                this.login = 'cashier';
                this.password = 'password';
            }
        }
    }">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Masuk ke Sistem</h2>
            <p class="text-xs text-slate-500 mt-1">Silakan masukkan username/email dan kata sandi Anda.</p>
        </div>

        <!-- Quick Demo Switcher -->
        <div class="mb-6 p-3 bg-slate-50 rounded-2xl border border-slate-200">
            <p class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Cepat Akun Demo:</p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="setRole('admin')" class="px-2 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200 transition-colors">
                    Admin
                </button>
                <button type="button" @click="setRole('manager')" class="px-2 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold border border-amber-200 transition-colors">
                    Manager
                </button>
                <button type="button" @click="setRole('cashier')" class="px-2 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200 transition-colors">
                    Kasir (POS)
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
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
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-900 bg-slate-50/50">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi
                </label>
                <input id="password" 
                       name="password" 
                       type="password" 
                       x-model="password"
                       required 
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-900 bg-slate-50/50">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-xs font-medium text-slate-600">Ingat Saya</span>
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
                    Masuk Sekarang
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
