@props([
    'type' => 'info',
])

@php
$types = [
    'success' => [
        'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'iconColor' => 'text-emerald-500',
    ],
    'error' => [
        'bg' => 'bg-rose-50 border-rose-200 text-rose-800',
        'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
        'iconColor' => 'text-rose-500',
    ],
    'warning' => [
        'bg' => 'bg-amber-50 border-amber-200 text-amber-800',
        'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        'iconColor' => 'text-amber-500',
    ],
    'info' => [
        'bg' => 'bg-indigo-50 border-indigo-200 text-indigo-800',
        'icon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
        'iconColor' => 'text-indigo-500',
    ],
];

$conf = $types[$type] ?? $types['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition.duration.300ms class="rounded-2xl border p-4 shadow-2xs {{ $conf['bg'] }} mb-4 flex items-start gap-3">
    <svg class="w-5 h-5 {{ $conf['iconColor'] }} shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $conf['icon'] }}" />
    </svg>
    <div class="text-sm font-medium flex-1">
        {{ $slot }}
    </div>
    <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
