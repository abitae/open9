@props([
    'variant' => 'auto',
])

@php
    $baseClass = 'h-8 w-auto max-w-44 object-contain object-left';
@endphp

@if ($variant === 'light')
    <img src="{{ asset('logo_normal.png') }}" alt="OPEN9" {{ $attributes->merge(['class' => $baseClass]) }}>
@elseif ($variant === 'dark')
    <img src="{{ asset('logo_black.png') }}" alt="OPEN9" {{ $attributes->merge(['class' => $baseClass]) }}>
@else
    <span class="inline-flex items-center">
        <img src="{{ asset('logo_normal.png') }}" alt="OPEN9" {{ $attributes->merge(['class' => $baseClass.' dark:hidden']) }}>
        <img src="{{ asset('logo_black.png') }}" alt="OPEN9" {{ $attributes->merge(['class' => $baseClass.' hidden dark:block']) }}>
    </span>
@endif
