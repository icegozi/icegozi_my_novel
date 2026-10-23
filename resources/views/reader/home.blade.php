@extends('layouts.reader')

@section('content')
<div class="space-y-16 pb-16">
    <!-- Hero Spotlight Section -->
    @if ($featured)
        <section class="relative overflow-hidden border-b border-slate-200/80 dark:border-slate-800/80 sepia:border-sepia-border bg-gradient-to-b from-indigo-950/20 via-transparent to-transparent py-10 lg:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <!-- Left: Book Artwork & 3D Badge -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative group w-64 sm:w-72 aspect-[3/4] rounded-2xl overflow-hidden shadow-book border border-slate-700/50 dark:border-slate-800 transition-transform duration-300 group-hover:scale-[1.02]">
                            <img
                                src="{{ $featured->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=600&q=80' }}"
                                alt="{{ $featured->title }}"
                                class="w-full h-full object-cover"
                                fetchpriority="high"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent opacity-80"></div>
                            
                            @if ($featured->assets3d->isNotEmpty())
                                <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-indigo-600/90 text-white text-[11px] font-bold shadow-lg backdrop-blur flex items-center gap-1.5 border border-indigo-400/30">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    Hình Chiếu 3D
                                </div>
                            @endif

                            <div class="absolute bottom-4 left-4 right-4 text-white">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400 block mb-1">
                                    ★ {{ number_format($featured->rating_avg, 2) }} / 5.0
                                </span>
                                <p class="text-xs text-slate-300 line-clamp-1">
                                    Tác giả: {{ $featured->author->name ?? 'Cổ Kim Mộng' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Story Details & CTAs -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge variant="brand" size="sm">
                                Truyện Đề Cử Hôm Nay
                            </x-badge>

                            @foreach ($featured->genres->take(3) as $genre)
                                <a href="/genres/{{ $genre->slug }}" class="px-3 py-1 rounded-full text-xs font-medium bg-slate-200/60 dark:bg-slate-800/60 sepia:bg-sepia-200 text-slate-700 dark:text-slate-300 sepia:text-sepia-text hover:bg-indigo-500/20 transition">
                                    {{ $genre->name }}
                                </a>
                            @endforeach

                            <x-badge :variant="'age' . (int)$featured->age_rating" size="xs">
                                {{ strtoupper($featured->age_rating) }}
                            </x-badge>
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight text-balance">
                            {{ $featured->title }}
                        </h1>

                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed line-clamp-4 text-pretty">
                            {{ $featured->synopsis }}
                        </p>

                        <!-- Stats Strip -->
                        <div class="grid grid-cols-3 gap-4 py-3 border-y border-slate-200/80 dark:border-slate-800/80 sepia:border-sepia-border text-center sm:text-left">
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block">Số Chương</span>
                                <span class="text-base font-bold font-mono text-slate-900 dark:text-white sepia:text-sepia-text tabular-nums">
                                    {{ $featured->total_chapters }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block">Lượt Đọc</span>
                                <span class="text-base font-bold font-mono text-slate-900 dark:text-white sepia:text-sepia-text tabular-nums">
                                    {{ number_format($featured->view_count) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block">Đánh Dấu</span>
                                <span class="text-base font-bold font-mono text-slate-900 dark:text-white sepia:text-sepia-text tabular-nums">
                                    {{ number_format($featured->bookmark_count) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            @if ($featured->chapters->isNotEmpty())
                                <x-button
                                    href="/stories/{{ $featured->slug }}/read/{{ $featured->chapters->first()->chapter_number }}"
                                    variant="primary"
                                    size="md"
                                >
                                    <span>📖</span> Đọc Ngay (Chương 1)
                                </x-button>
                            @endif

                            <x-button
                                href="/stories/{{ $featured->slug }}"
                                variant="secondary"
                                size="md"
                            >
                                Chi Tiết Tác Phẩm
                            </x-button>

                            @if ($featured->assets3d->isNotEmpty())
                                <x-button
                                    href="/truyen/{{ $featured->slug }}/phong-trung-bay-3d"
                                    variant="ghost"
                                    size="md"
                                    class="!bg-purple-600/10 dark:!bg-purple-500/20 !text-purple-600 dark:!text-purple-300 border border-purple-500/30 hover:!bg-purple-600/20"
                                >
                                    <span>✨</span> Phòng Trưng Bày 3D
                                </x-button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
        <!-- Continue Reading Section (If Auth) -->
        @if ($continueReading && $continueReading->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text flex items-center gap-2">
                        <span>🔖</span> Tiếp Tục Đọc Dở
                    </h2>
                    <a href="/history" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Toàn bộ lịch sử →
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($continueReading as $history)
                        @if ($history->novel && $history->lastChapter)
                            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/60 sepia:bg-sepia-50 shadow-sm flex items-center gap-4 hover:border-indigo-500/50 transition">
                                <img src="{{ $history->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=150&q=80' }}" alt="{{ $history->novel->title }}" class="w-14 h-20 object-cover rounded-xl shrink-0">
                                <div class="flex-1 min-w-0 space-y-1">
                                    <h4 class="font-bold text-xs truncate text-slate-900 dark:text-white sepia:text-sepia-text">{{ $history->novel->title }}</h4>
                                    <p class="text-[11px] text-slate-500 truncate">{{ $history->lastChapter->title }}</p>
                                    <a href="/stories/{{ $history->novel->slug }}/read/{{ $history->lastChapter->chapter_number }}" class="inline-block mt-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Đọc tiếp →
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Trending Novels Section -->
        <section class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                        Xu Hướng Đọc Nhiều
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Những tác phẩm được đón đọc và bàn luận nhiều nhất trong tuần</p>
                </div>
                <a href="/popular" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Xem tất cả →
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-6">
                @foreach ($trendingNovels as $novel)
                    <x-novel-card :novel="$novel" />
                @endforeach
            </div>
        </section>

        <!-- Latest Chapter Updates Feed -->
        <section class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                        Mới Cập Nhật
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Các chương truyện và kỳ báo mới nhất vừa được xuất bản</p>
                </div>
                <a href="/latest" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Xem dòng thời gian →
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($latestChapters as $chapter)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm flex items-center justify-between gap-4 hover:border-slate-400 dark:hover:border-slate-700 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $chapter->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=100&q=80' }}" alt="{{ $chapter->novel->title }}" class="w-12 h-16 rounded-lg object-cover shrink-0">
                            <div class="min-w-0 space-y-1">
                                <a href="/stories/{{ $chapter->novel->slug }}" class="font-bold text-xs truncate block text-slate-900 dark:text-white sepia:text-sepia-text hover:text-indigo-600 transition">
                                    {{ $chapter->novel->title }}
                                </a>
                                <a href="/stories/{{ $chapter->novel->slug }}/read/{{ $chapter->chapter_number }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold truncate block hover:underline">
                                    {{ $chapter->title }}
                                </a>
                                <span class="text-[10px] text-slate-400 block">
                                    {{ $chapter->novel->genres->first()->name ?? 'Truyện' }} • {{ $chapter->published_at?->diffForHumans() ?? 'Vừa xong' }}
                                </span>
                            </div>
                        </div>

                        <a href="/stories/{{ $chapter->novel->slug }}/read/{{ $chapter->chapter_number }}" class="shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition">
                            Đọc
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Genres Visual Grid -->
        <section class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                        Khám Phá Theo Thể Loại
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Chọn lọc theo phong cách đọc và sở thích văn học</p>
                </div>
                <a href="/genres" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Tất cả thể loại →
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($genres as $genre)
                    <a href="/genres/{{ $genre->slug }}" class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 text-center hover:border-indigo-500/50 hover:shadow-md transition group">
                        <span class="block text-sm font-bold text-slate-900 dark:text-white sepia:text-sepia-text group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                            {{ $genre->name }}
                        </span>
                        <span class="mt-1 inline-block text-[11px] text-slate-500">
                            {{ $genre->novels_count }} tác phẩm
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
