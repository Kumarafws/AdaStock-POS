@props([
    'variant' => 'slate',
    'size' => 'md',
])

@php
$variants = [
    'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
    'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
    'rose' => 'bg-rose-50 text-rose-700 border-rose-200',
    'blue' => 'bg-blue-50 text-blue-700 border-blue-200',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs font-semibold',
    'md' => 'px-2.5 py-1 text-xs font-semibold',
    'lg' => 'px-3 py-1.5 text-sm font-semibold',
];

$classes = ($variants[$variant] ?? $variants['slate']) . ' ' . ($sizes[$size] ?? $sizes['md']) . ' inline-flex items-center gap-1.5 rounded-full border shadow-2xs transition-all';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
