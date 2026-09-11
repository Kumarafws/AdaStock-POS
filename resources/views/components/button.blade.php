@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/20 active:bg-indigo-800 border border-transparent',
    'secondary' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-2xs active:bg-slate-100',
    'emerald' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/20 active:bg-emerald-800 border border-transparent',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm shadow-rose-600/20 active:bg-rose-800 border border-transparent',
    'ghost' => 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-transparent',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs font-medium rounded-lg',
    'md' => 'px-4 py-2.5 text-sm font-semibold rounded-xl',
    'lg' => 'px-6 py-3.5 text-base font-semibold rounded-2xl',
];

$classes = ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']) . ' inline-flex items-center justify-center gap-2 cursor-pointer transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:opacity-50 disabled:cursor-not-allowed';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
