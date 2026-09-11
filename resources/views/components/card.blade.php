@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden transition-all']) }}>
    @if($title || isset($headerAction))
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                @if($title)
                    <h3 class="text-base font-bold text-slate-800 tracking-tight">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($headerAction)
                <div>
                    {{ $headerAction }}
                </div>
            @endisset
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-3 bg-slate-50/80 border-t border-slate-100">
            {{ $footer }}
        </div>
    @endisset
</div>
