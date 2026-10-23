@props([
    'novel',
    'compact' => false,
])

@php
$novelSlug = $novel->slug ?? ($novel->id ?? '#');
$novelUrl = Route::has('reader.novels.show') ? route('reader.novels.show', $novelSlug) : url('/truyen/' . $novelSlug);
@endphp

<div {{ $attributes->merge(['class' => 'group relative flex flex-col bg-slate-900/40 sepia:bg-sepia-50/60 rounded-2xl p-3 border border-slate-800/80 sepia:border-sepia-border hover:border-indigo-500/50 sepia:hover:border-amber-500/50 transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 motion-reduce:transform-none hover:shadow-glow']) }}>
    <!-- Book Cover 3:4 Aspect Ratio -->
    <a href="{{ $novelUrl }}" class="relative block aspect-[3/4] w-full overflow-hidden rounded-xl bg-slate-950 shadow-book">
        <img src="{{ $novel->cover_image ?? '/images/default_cover.webp' }}"
             alt="{{ $novel->title }}"
             width="300"
             height="400"
             loading="lazy"
             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105 motion-reduce:transform-none">

        <!-- Age rating badge overlay -->
        @if(!empty($novel->age_rating))
            <div class="absolute top-2 left-2">
                <x-badge :variant="'age' . (int)$novel->age_rating" size="xs">
                    {{ (int)$novel->age_rating }}+
                </x-badge>
            </div>
        @endif

        @if(!empty($novel->is_3d))
            <div class="absolute top-2 right-2">
                <x-badge variant="brand" size="xs">3D</x-badge>
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
            <span class="text-xs font-semibold text-white">Xem chi&nbsp;tiết&nbsp;→</span>
        </div>
    </a>

    <!-- Meta & Title -->
    <div class="mt-3 flex flex-col flex-1 min-w-0">
        <a href="{{ $novelUrl }}" class="font-heading font-bold text-sm text-white sepia:text-sepia-text group-hover:text-indigo-400 sepia:group-hover:text-amber-800 transition-colors line-clamp-2 text-balance leading-snug">
            {{ $novel->title }}
        </a>

        <p class="text-xs text-slate-400 sepia:text-sepia-muted mt-1 truncate">
            {{ $novel->author_name ?? ($novel->author?->name ?? 'Tác giả') }}
        </p>

        <div class="mt-auto pt-2.5 flex items-center justify-between text-xs text-slate-400 sepia:text-sepia-muted">
            <x-rating :score="$novel->rating ?? ($novel->rating_avg ?? 5.0)" size="xs" />
            <span class="text-[11px] font-medium">{{ $novel->chapters_count ?? ($novel->total_chapters ?? 0) }} chương</span>
        </div>
    </div>
</div>
