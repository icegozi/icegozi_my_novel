@props([
    'hover' => false,
    'glow' => false,
    'padding' => 'normal',
])

@php
$paddings = [
    'none' => 'p-0',
    'compact' => 'p-4',
    'normal' => 'p-6',
    'spacious' => 'p-8',
];

$classes = 'rounded-2xl bg-white/80 dark:bg-slate-900/80 sepia:bg-sepia-50/90 border border-slate-200/80 dark:border-slate-800/80 sepia:border-sepia-border backdrop-blur-md transition-[transform,border-color,box-shadow] duration-200 '
    . ($paddings[$padding] ?? $paddings['normal'])
    . ($hover ? ' hover:-translate-y-1 motion-reduce:transform-none hover:border-slate-300 dark:hover:border-slate-700 sepia:hover:border-sepia-400' : '')
    . ($glow ? ' shadow-glow' : ' shadow-sm');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
