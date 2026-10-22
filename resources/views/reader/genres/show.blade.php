@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="/genres" class="hover:underline">Thể Loại</a>
            <span>/</span>
            <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $genre->name }}</span>
        </div>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
            {{ $genre->name }}
        </h1>
        <p class="text-xs text-slate-500 max-w-3xl leading-relaxed">
            {{ $genre->description ?? 'Tổng hợp các tiểu thuyết và truyện tranh thuộc phân loại ' . $genre->name . '.' }}
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
        @forelse ($novels as $novel)
            <div class="group flex flex-col space-y-2">
                <a href="/stories/{{ $novel->slug }}" class="relative aspect-[3/4] rounded-2xl overflow-hidden shadow-book border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-800 transition-transform duration-300 group-hover:scale-105 group-hover:shadow-glow">
                    <img src="{{ $novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $novel->title }}" class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span class="absolute bottom-2 right-2 text-[10px] font-bold text-amber-400 bg-black/70 px-1.5 py-0.5 rounded">
                        ★ {{ number_format($novel->rating_avg, 1) }}
                    </span>
                </a>

                <div class="space-y-1">
                    <a href="/stories/{{ $novel->slug }}" class="font-bold text-xs line-clamp-1 text-slate-900 dark:text-white sepia:text-sepia-text group-hover:text-indigo-600 transition">
                        {{ $novel->title }}
                    </a>
                    <p class="text-[11px] text-slate-500 truncate">
                        {{ $novel->author->name ?? 'Tác giả' }}
                    </p>
                    <p class="text-[10px] text-slate-400">
                        {{ $novel->total_chapters }} chương • {{ number_format($novel->view_count) }} lượt đọc
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500">
                Chưa có truyện nào trong thể loại này
            </div>
        @endforelse
    </div>

    <div class="pt-6">
        {{ $novels->links() }}
    </div>
</div>
@endsection
