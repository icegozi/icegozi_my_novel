@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Không Gian Cá Nhân</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Tủ Sách Cá Nhân</h1>
        <p class="text-xs text-slate-500">Quản lý các tác phẩm yêu thích, truyện đang theo dõi và danh sách đã lưu</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs font-bold">
        <a href="/library" class="pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400">
            Tất Cả Tủ Sách ({{ $bookmarks->count() }})
        </a>
        <a href="/history" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Lịch Sử Đọc ({{ $histories->count() }})
        </a>
        <a href="/bookmarks" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Dấu Trang Đã Lưu
        </a>
    </div>

    <!-- Bookmarks Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
        @forelse ($bookmarks as $bm)
            @if ($bm->novel)
                <div class="group flex flex-col space-y-2 relative" id="bookmark-card-{{ $bm->novel->id }}">
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
                                Đọc ngay →
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-full py-16 text-center space-y-3 text-slate-500">
                <span class="text-4xl block">📚</span>
                <p class="text-sm font-semibold">Tủ sách của bạn hiện đang trống</p>
                <p class="text-xs">Hãy nhấn vào nút “Lưu Vào Tủ Sách” khi xem các tác phẩm yêu thích để theo dõi tại đây.</p>
                <a href="/popular" class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-500 transition">
                    Khám phá truyện hay
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
