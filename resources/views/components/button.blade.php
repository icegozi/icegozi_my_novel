@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-[color,background-color,border-color,box-shadow,transform] duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 active:scale-95 motion-reduce:transform-none disabled:opacity-50 disabled:pointer-events-none select-none';

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs min-h-[44px] sm:min-h-[36px] gap-1.5',
    'md' => 'px-4 py-2.5 text-sm min-h-[44px] gap-2',
    'lg' => 'px-6 py-3.5 text-base min-h-[50px] gap-2.5',
];

$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 focus-visible:ring-indigo-500',
    'secondary' => 'bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 sepia:bg-sepia-200 sepia:text-sepia-text sepia:hover:bg-sepia-300 border border-slate-700/50 sepia:border-sepia-border focus-visible:ring-slate-400',
    'amber' => 'bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-lg shadow-amber-500/20 focus-visible:ring-amber-500',
    'danger' => 'bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 focus-visible:ring-rose-500',
    'ghost' => 'hover:bg-slate-800/40 text-slate-300 hover:text-white sepia:text-sepia-muted sepia:hover:bg-sepia-200/60 focus-visible:ring-slate-400',
];

$classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
