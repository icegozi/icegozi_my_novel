@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-500">Bảng Xếp Hạng Độc Giả</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Bảng Xếp Hạng Truyện Hot</h1>
        <p class="text-xs text-slate-500">Xếp hạng dựa trên tổng số lượt đọc và đánh giá chất lượng từ cộng đồng độc giả</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($novels as $index => $novel)
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm flex gap-4 hover:border-indigo-500/40 hover:shadow-md transition relative group">
                <!-- Rank Medal / Number -->
                <div class="absolute -top-2.5 -left-2.5 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shadow-md {{ $index === 0 ? 'bg-amber-500 text-slate-950 ring-2 ring-amber-300' : ($index === 1 ? 'bg-slate-300 text-slate-950' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-slate-700 text-white')) }}">
                    {{ $index + 1 }}
                </div>

                <a href="/stories/{{ $novel->slug }}" class="w-24 aspect-[3/4] rounded-xl overflow-hidden shrink-0 shadow-book group-hover:scale-105 transition-transform">
                    <img src="{{ $novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=200&q=80' }}" alt="{{ $novel->title }}" class="w-full h-full object-cover" loading="lazy">
                </a>

                <div class="flex-1 min-w-0 space-y-1.5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            @if ($novel->genres->isNotEmpty())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                    {{ $novel->genres->first()->name }}
                                </span>
                            @endif
                            <span class="text-[10px] font-bold text-amber-500">
                                ★ {{ number_format($novel->rating_avg, 2) }}
                            </span>
                        </div>
                        <a href="/stories/{{ $novel->slug }}" class="font-bold text-sm text-slate-900 dark:text-white sepia:text-sepia-text line-clamp-1 group-hover:text-indigo-600 transition">
                            {{ $novel->title }}
                        </a>
                        <p class="text-[11px] text-slate-500 truncate">
                            {{ $novel->author->name ?? 'Tác giả' }}
                        </p>
                        <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted line-clamp-2 mt-1">
                            {{ $novel->synopsis }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 font-mono tabular-nums">
                        <span>{{ number_format($novel->view_count) }} lượt xem</span>
                        <span>{{ $novel->total_chapters }} chương</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500">
                Chưa có dữ liệu bảng xếp hạng
            </div>
        @endforelse
    </div>

    <div class="pt-6">
        {{ $novels->links() }}
    </div>
</div>
@endsection
