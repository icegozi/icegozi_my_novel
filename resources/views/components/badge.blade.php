@props([
    'variant' => 'brand',
    'size' => 'sm',
    'dot' => false,
])

@php
$baseClasses = 'inline-flex items-center font-bold tracking-wide rounded-full border transition-colors';

$sizes = [
    'xs' => 'px-2 py-0.5 text-[10px] gap-1',
    'sm' => 'px-2.5 py-1 text-xs gap-1.5',
    'md' => 'px-3 py-1.5 text-xs gap-2',
];

$variants = [
    'brand' => 'bg-indigo-500/15 text-indigo-400 sepia:text-indigo-800 border-indigo-500/25',
    'amber' => 'bg-amber-500/15 text-amber-400 sepia:text-amber-800 border-amber-500/25',
    'emerald' => 'bg-emerald-500/15 text-emerald-400 sepia:text-emerald-800 border-emerald-500/25',
    'rose' => 'bg-rose-500/15 text-rose-400 sepia:text-rose-800 border-rose-500/25',
    'slate' => 'bg-slate-800/80 text-slate-300 sepia:bg-sepia-200 sepia:text-sepia-text border-slate-700/60 sepia:border-sepia-border',
    'age13' => 'bg-cyan-500/15 text-cyan-400 sepia:text-cyan-800 border-cyan-500/30 font-black',
    'age16' => 'bg-amber-500/15 text-amber-400 sepia:text-amber-800 border-amber-500/30 font-black',
    'age18' => 'bg-rose-500/15 text-rose-400 sepia:text-rose-800 border-rose-500/30 font-black',
];

$classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['sm']) . ' ' . ($variants[$variant] ?? $variants['brand']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80 animate-pulse"></span>
    @endif
    {{ $slot }}
</span>
