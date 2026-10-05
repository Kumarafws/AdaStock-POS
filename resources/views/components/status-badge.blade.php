@props([
    'active' => true,
    'activeText' => 'Aktif',
    'inactiveText' => 'Nonaktif',
])

@if($active)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600']) }}>
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        {{ $activeText }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400']) }}>
        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
        {{ $inactiveText }}
    </span>
@endif
