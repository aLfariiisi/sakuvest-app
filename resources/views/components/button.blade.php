@props(['variant' => 'primary'])

@php
    $classes = $variant === 'primary' 
        ? 'bg-red-500 hover:bg-red-600 text-white shadow-sm shadow-red-500/25 py-3 px-5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2'
        : 'bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2';
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
    {{ $slot }}
</button>