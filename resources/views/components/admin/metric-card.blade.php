@props([
    'label',
    'value',
    'hint' => null,
    'href' => null,
    'icon' => null,
    'tone' => 'default',
])

@php($tag = filled($href) ? 'a' : 'div')

<{{ $tag }}
    @if (filled($href)) href="{{ $href }}" wire:navigate @endif
    {{ $attributes->class([
        'group relative overflow-hidden rounded-2xl border p-4 shadow-sm transition',
        'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' => $tone === 'default',
        'border-amber-500/40 bg-amber-500/5 dark:border-amber-400/30 dark:bg-amber-400/10' => $tone === 'attention',
        'border-emerald-500/30 bg-emerald-500/5 dark:border-emerald-400/20 dark:bg-emerald-400/10' => $tone === 'success',
        'hover:border-accent/40 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent' => filled($href),
    ]) }}
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-xs font-medium tracking-wide text-zinc-500 uppercase">{{ $label }}</div>
            <div class="mt-1.5 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $value }}</div>
            @if (filled($hint))
                <div class="mt-1 text-xs text-zinc-400">{{ $hint }}</div>
            @endif
        </div>
        @if (filled($icon))
            <div @class([
                'flex size-10 shrink-0 items-center justify-center rounded-xl',
                'bg-accent/15 text-accent' => $tone === 'default',
                'bg-amber-400/15 text-amber-500 dark:text-amber-300' => $tone === 'attention',
                'bg-emerald-400/15 text-emerald-500 dark:text-emerald-300' => $tone === 'success',
            ])>
                <flux:icon :icon="$icon" variant="mini" class="size-5" />
            </div>
        @endif
    </div>
    {{ $slot }}
</{{ $tag }}>
