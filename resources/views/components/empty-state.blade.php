@props([
    'title' => 'Tidak ada data ditemukan',
    'subtitle' => null,
    'resetUrl' => null,
    'resetText' => 'Reset Pencarian',
])

<div {{ $attributes->merge(['class' => 'col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200/80 p-8 shadow-xs']) }}>
    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>
    <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
    @if($subtitle)
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto leading-relaxed">{{ $subtitle }}</p>
    @endif
    @if($resetUrl)
        <div class="mt-4">
            <a href="{{ $resetUrl }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                {{ $resetText }}
            </a>
        </div>
    @endif
    {{ $slot }}
</div>
