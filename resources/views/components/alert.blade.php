@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
$types = [
    'success' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400 sepia:text-emerald-800',
    'error' => 'bg-rose-500/10 border-rose-500/20 text-rose-400 sepia:text-rose-800',
    'warning' => 'bg-amber-500/10 border-amber-500/20 text-amber-400 sepia:text-amber-800',
    'info' => 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400 sepia:text-indigo-800',
];
$classes = 'p-4 rounded-2xl border text-xs font-medium flex items-center justify-between gap-3 ' . ($types[$type] ?? $types['info']);
@endphp

<div role="alert" aria-live="polite" x-data="{ open: true }" x-show="open" x-transition.opacity {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-center gap-2.5">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" @click="open = false" class="p-1 hover:opacity-75 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current rounded-lg" aria-label="Đóng thông báo"><span aria-hidden="true">✕</span></button>
    @endif
</div>
