@props([
    'name',
    'title' => null,
    'maxWidth' => 'md',
])

@php
$maxWidths = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
];
$widthClass = $maxWidths[$maxWidth] ?? $maxWidths['md'];
@endphp

<div x-cloak
     x-show="{{ $name }}"
     @keydown.escape.window="{{ $name }} = false"
     class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="{{ $name }}"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         @click="{{ $name }} = false"></div>

    <!-- Panel -->
    <div x-show="{{ $name }}"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative w-full {{ $widthClass }} bg-slate-900 sepia:bg-sepia-50 border border-slate-800 sepia:border-sepia-border rounded-2xl shadow-2xl p-6 overflow-hidden z-10">

        @if($title)
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 sepia:border-sepia-border mb-4">
                <h3 class="font-heading font-bold text-lg text-white sepia:text-sepia-text">{{ $title }}</h3>
                <button type="button" @click="{{ $name }} = false" class="text-slate-400 hover:text-white p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" aria-label="Đóng cửa sổ">✕</button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
