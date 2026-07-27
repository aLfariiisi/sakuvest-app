@props(['type' => 'masuk'])

@php
    $badgeClass = $type === 'masuk' 
        ? 'bg-emerald-50 text-emerald-600' 
        : 'bg-red-50 text-red-600';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ' . $badgeClass]) }}>
    {{ $slot }}
</span>