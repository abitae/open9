@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center">
            <x-brand-logo class="h-7 max-w-36" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center">
            <x-brand-logo class="h-7 max-w-36" />
        </x-slot>
    </flux:brand>
@endif
