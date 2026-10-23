@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Danh Mục Tác Phẩm</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Thể Loại Truyện & Comic</h1>
        <p class="text-xs text-slate-500">Khám phá các thế giới văn học và truyện tranh phân loại theo chủ đề</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($genres as $genre)
            <a href="/genres/{{ $genre->slug }}" class="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 hover:border-indigo-500/60 hover:shadow-lg transition group flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white sepia:text-sepia-text group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                            {{ $genre->name }}
                        </h2>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            {{ $genre->novels_count }} bộ
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted leading-relaxed line-clamp-3">
                        {{ $genre->description ?? 'Tuyển tập những tác phẩm chất lượng cao thuộc thể loại ' . $genre->name . '.' }}
                    </p>
                </div>

                <div class="pt-6 flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    <span>Xem danh sách truyện</span>
                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                </div>
            </a>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500">
                Chưa có thể loại nào
            </div>
        @endforelse
    </div>
</div>
@endsection
