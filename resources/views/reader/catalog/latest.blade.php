@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Dòng Thời Gian Xuất Bản</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Mới Cập Nhật</h1>
        <p class="text-xs text-slate-500">Danh sách các chương truyện mới nhất từ các tác giả và họa sĩ trên nền tảng</p>
    </div>

    <div class="space-y-4">
        @forelse ($chapters as $chapter)
            <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 flex items-center justify-between gap-4 hover:border-indigo-500/40 transition">
                <div class="flex items-center gap-4 min-w-0">
                    <img src="{{ $chapter->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=150&q=80' }}" alt="{{ $chapter->novel->title }}" class="w-14 h-20 rounded-xl object-cover shrink-0">
                    <div class="min-w-0 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                {{ $chapter->novel->genres->first()->name ?? 'Truyện' }}
                            </span>
                            <span class="text-[11px] text-slate-400 tabular-nums">
                                {{ $chapter->published_at?->diffForHumans() ?? 'Vừa xong' }}
                            </span>
                        </div>
                        <a href="/stories/{{ $chapter->novel->slug }}" class="font-bold text-sm text-slate-900 dark:text-white sepia:text-sepia-text truncate block hover:text-indigo-600 transition">
                            {{ $chapter->novel->title }}
                        </a>
                        <a href="/stories/{{ $chapter->novel->slug }}/read/{{ $chapter->chapter_number }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold truncate block hover:underline">
                            {{ $chapter->title }}
                        </a>
                        <p class="text-[11px] text-slate-500 truncate">
                            Tác giả: {{ $chapter->novel->author->name ?? 'Tác giả' }} • {{ number_format($chapter->word_count) }} chữ
                        </p>
                    </div>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <a href="/stories/{{ $chapter->novel->slug }}/read/{{ $chapter->chapter_number }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-sm">
                        Đọc Ngay
                    </a>
                </div>
            </div>
        @empty
            <div class="py-16 text-center text-slate-500 space-y-2">
                <span class="text-4xl block">📖</span>
                <p class="text-sm font-semibold">Chưa có chương mới nào được cập nhật</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-6">
        {{ $chapters->links() }}
    </div>
</div>
@endsection
