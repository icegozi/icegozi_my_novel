@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Dấu Trang Yêu Thích</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Đánh Dấu</h1>
        <p class="text-xs text-slate-500">Danh mục các tác phẩm bạn đã gắn dấu trang để đọc và theo dõi</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs font-bold">
        <a href="/library" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Tất Cả Tủ Sách
        </a>
        <a href="/history" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Lịch Sử Đọc
        </a>
        <a href="/bookmarks" class="pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400">
            Dấu Trang Đã Lưu ({{ $bookmarks->total() }})
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
        @forelse ($bookmarks as $bm)
            @if ($bm->novel)
                <div class="group flex flex-col space-y-2">
                    <a href="/stories/{{ $bm->novel->slug }}" class="relative aspect-[3/4] rounded-2xl overflow-hidden shadow-book border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-800 transition-transform duration-300 group-hover:scale-105 group-hover:shadow-glow">
                        <img src="{{ $bm->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $bm->novel->title }}" class="w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <span class="absolute bottom-2 right-2 text-[10px] font-bold text-amber-400 bg-black/70 px-1.5 py-0.5 rounded">
                            ★ {{ number_format($bm->novel->rating_avg, 1) }}
                        </span>
                    </a>

                    <div class="space-y-1">
                        <a href="/stories/{{ $bm->novel->slug }}" class="font-bold text-xs line-clamp-1 text-slate-900 dark:text-white sepia:text-sepia-text group-hover:text-indigo-600 transition">
                            {{ $bm->novel->title }}
                        </a>
                        <p class="text-[11px] text-slate-500 truncate">
                            {{ $bm->novel->total_chapters }} chương
                        </p>
                        @if ($bm->novel->chapters->isNotEmpty())
                            <a href="/stories/{{ $bm->novel->slug }}/read/{{ $bm->novel->chapters->first()->chapter_number }}" class="inline-block text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                Đọc từ chương 1 →
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-full py-16 text-center text-slate-500">
                Chưa có truyện nào được đánh dấu.
            </div>
        @endforelse
    </div>

    <div class="pt-6">
        {{ $bookmarks->links() }}
    </div>
</div>
@endsection
