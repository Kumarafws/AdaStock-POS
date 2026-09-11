@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'color' => 'indigo',
])

@php
$colors = [
    'indigo' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
    'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    'amber' => 'bg-amber-50 text-amber-600 border-amber-100',
    'rose' => 'bg-rose-50 text-rose-600 border-rose-100',
    'blue' => 'bg-blue-50 text-blue-600 border-blue-100',
];
$iconClass = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all">
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $title }}</span>
        @if($icon)
            <div class="w-10 h-10 rounded-xl flex items-center justify-center border {{ $iconClass }}">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-4 flex items-baseline justify-between">
        <div class="text-2xl font-extrabold text-slate-900 num-tabular tracking-tight">
            {{ $value }}
        </div>
        @if($trend)
            <div class="text-xs font-semibold {{ $trend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
            </div>
        @endif
    </div>

    @if(isset($subtext))
        <div class="mt-2 text-xs text-slate-500">
            {{ $subtext }}
        </div>
    @endif
</div>
