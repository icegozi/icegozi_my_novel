@extends('layouts.reader', [
    'title' => '404 - Trang Không Tồn Tại | Novel Engine'
])

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16">
    <div class="max-w-xl w-full text-center space-y-8">
        <div class="relative">
            <span class="font-heading font-black text-8xl sm:text-9xl tracking-widest text-indigo-500/20 dark:text-indigo-400/10 block select-none">
                404
            </span>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 rounded-3xl bg-indigo-600/10 dark:bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-4xl shadow-glow backdrop-blur-sm">
                    📜
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h1 class="text-3xl sm:text-4xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                Trang Không Tồn Tại
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 sepia:text-sepia-muted leading-relaxed max-w-md mx-auto">
                Chương truyện hoặc trang bạn đang tìm kiếm dường như đã bị cuốn vào hư không thời gian, hoặc đường dẫn đã có sự thay đổi.
            </p>
        </div>

        <!-- Quick Search -->
        <div class="max-w-md mx-auto">
            <form action="/search" method="GET" class="relative" role="search">
                <input
                    type="search"
                    name="q"
                    placeholder="Tìm tên tác phẩm hoặc tác giả…"
                    class="w-full pl-10 pr-20 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-xs text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
                <button
                    type="submit"
                    class="absolute right-2 top-2 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white text-xs font-bold transition"
                >
                    Tìm
                </button>
            </form>
        </div>

        <!-- Quick Navigation Recovery Links -->
        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a
                href="/"
                class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 sepia:shadow-amber-900/25 transition-all transform hover:-translate-y-0.5 flex items-center gap-2"
            >
                <span>🏠</span> Trở Về Trang Chủ
            </a>
            <a
                href="/popular"
                class="px-5 py-3 rounded-xl border border-slate-300 dark:border-slate-700 sepia:border-sepia-border hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text text-xs font-bold transition"
            >
                Bảng Xếp Hạng
            </a>
            <a
                href="/genres"
                class="px-5 py-3 rounded-xl border border-slate-300 dark:border-slate-700 sepia:border-sepia-border hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text text-xs font-bold transition"
            >
                Khám Phá Thể Loại
            </a>
        </div>
    </div>
</div>
@endsection
