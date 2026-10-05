@props([
    'href',
    'active' => false,
    'variant' => 'indigo', // 'indigo' or 'emerald'
])

@php
$activeClasses = [
    'indigo' => 'bg-indigo-600 text-white font-semibold shadow-sm',
    'emerald' => 'bg-emerald-600 text-white font-semibold shadow-sm',
];

$activeClass = $activeClasses[$variant] ?? $activeClasses['indigo'];
$inactiveClass = 'text-slate-300 hover:bg-slate-800 hover:text-white';
$classes = ($active ? $activeClass : $inactiveClass) . ' flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
