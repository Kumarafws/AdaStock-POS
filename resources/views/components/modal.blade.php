@props([
    'show' => 'showModal',
    'maxWidth' => 'max-w-lg',
    'title' => null,
    'subtitle' => null,
])

<div x-show="{{ $show }}" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    
    <div @click.away="{{ $show }} = false" 
         {{ $attributes->merge(['class' => "bg-white rounded-3xl {$maxWidth} w-full p-6 sm:p-7 shadow-2xl border border-slate-100 relative"]) }}>
        
        @if($title || isset($header))
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <div>
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h3 class="text-base font-bold text-slate-900">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    @endif
                </div>
                <button type="button" @click="{{ $show }} = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
