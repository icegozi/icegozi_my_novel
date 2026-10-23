@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <!-- Search Bar & Filters Header -->
    <div class="space-y-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Tra Cứu Tác Phẩm</span>
            <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                Tìm Kiếm & Bộ Lọc Nâng Cao
            </h1>
        </div>

        <form action="/search" method="GET" class="space-y-4" role="search">
            <!-- Search Input -->
            <div class="relative max-w-3xl">
                <label for="search-page-input" class="sr-only">Nhập từ khóa tìm kiếm</label>
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input
                    type="search"
                    id="search-page-input"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Nhập tên truyện, tên tác giả hoặc nội dung muốn tìm…"
                    class="w-full pl-12 pr-24 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-slate-900 dark:text-white sepia:text-sepia-text text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition"
                >
                <button
                    type="submit"
                    class="absolute right-2 top-2 px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white text-xs font-bold transition shadow-sm"
                >
                    Tìm
                </button>
            </div>

            <!-- Facet Filters Row -->
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <!-- Genre Selector -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-genre" class="font-bold text-slate-600 dark:text-slate-400 sepia:text-sepia-muted">Thể loại:</label>
                    <select
                        id="filter-genre"
                        name="genre"
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-slate-800 dark:text-slate-200 sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="all">Tất cả thể loại</option>
                        @foreach ($genres as $g)
                            <option value="{{ $g->slug }}" {{ request('genre') === $g->slug ? 'selected' : '' }}>
                                {{ $g->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Age Rating Selector -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-age" class="font-bold text-slate-600 dark:text-slate-400 sepia:text-sepia-muted">Độ tuổi:</label>
                    <select
                        id="filter-age"
                        name="age_rating"
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-slate-800 dark:text-slate-200 sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="all">Mọi lứa tuổi</option>
                        <option value="all" {{ request('age_rating') === 'all' ? 'selected' : '' }}>Phổ thông (All)</option>
                        <option value="13_plus" {{ request('age_rating') === '13_plus' ? 'selected' : '' }}>13+ Thiếu niên</option>
                        <option value="16_plus" {{ request('age_rating') === '16_plus' ? 'selected' : '' }}>16+ Trưởng thành</option>
                        <option value="18_plus" {{ request('age_rating') === '18_plus' ? 'selected' : '' }}>18+ Giới hạn</option>
                    </select>
                </div>

                <!-- Sort Selector -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-sort" class="font-bold text-slate-600 dark:text-slate-400 sepia:text-sepia-muted">Sắp xếp:</label>
                    <select
                        id="filter-sort"
                        name="sort"
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-slate-800 dark:text-slate-200 sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Mới cập nhật</option>
                        <option value="views" {{ request('sort') === 'views' ? 'selected' : '' }}>Lượt đọc nhiều nhất</option>
                        <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Đánh giá cao nhất</option>
                        <option value="chapters" {{ request('sort') === 'chapters' ? 'selected' : '' }}>Nhiều chương nhất</option>
                    </select>
                </div>

                @if(request('q') || request('genre') || request('age_rating') || request('sort'))
                    <a href="/search" class="text-xs text-rose-500 hover:underline font-semibold ml-2">
                        Xóa tất cả bộ lọc ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Results Header -->
    <div class="flex items-center justify-between text-xs text-slate-500">
        <span>Tìm thấy <strong>{{ $novels->total() }}</strong> tác phẩm phù hợp</span>
        @if(request('q'))
            <span>Từ khóa: “<strong>{{ request('q') }}</strong>”</span>
        @endif
    </div>

    <!-- Novels Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 sm:gap-6">
        @forelse ($novels as $novel)
            <div class="group flex flex-col space-y-2">
                <a href="/stories/{{ $novel->slug }}" class="relative aspect-[3/4] rounded-2xl overflow-hidden shadow-book border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-800 transition-transform duration-300 group-hover:scale-105 group-hover:shadow-glow">
                    <img src="{{ $novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $novel->title }}" class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    @if ($novel->genres->isNotEmpty())
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur text-[10px] font-bold text-white">
                            {{ $novel->genres->first()->name }}
                        </span>
                    @endif

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
            <div class="col-span-full py-16 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-200 flex items-center justify-center mx-auto text-2xl text-slate-400">
                    🔍
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white sepia:text-sepia-text">
                        Không tìm thấy tác phẩm nào phù hợp
                    </h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Hãy thử kiểm tra lại chính tả hoặc tìm bằng các từ khóa ngắn gọn hơn, hoặc tham khảo các danh mục phổ biến bên dưới.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <a href="/popular" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-500 transition">
                        Khám Phá Truyện Hot
                    </a>
                    <a href="/latest" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Chương Mới Nhất
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-6">
        {{ $novels->links() }}
    </div>
</div>
@endsection
