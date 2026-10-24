@extends('layouts.reader', [
    'title' => 'Quản Lý Tác Phẩm - Studio Tác Giả - Novel Engine',
    'metaDescription' => 'Khu vực quản lý tác phẩm sáng tác, xuất bản chương truyện và thiết lập hiệu ứng 3D.'
])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="p-1 hover:opacity-75">✕</button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 border border-indigo-500/25 sepia:border-amber-800/30 mb-2">
                <span aria-hidden="true">✍️</span> Studio Tác Giả Chuyên Nghiệp
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
                Danh Sách Tác Phẩm Của Bạn
            </h1>
            <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs sm:text-sm mt-1">
                Quản lý nội dung, xuất bản chương và gắn hiệu ứng 3D trực quan.
            </p>
        </div>

        <a href="/tac-gia/truyen/create" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-md sepia:shadow-amber-900/20 transition-colors shadow-md shrink-0">
            <span class="text-base leading-none">+</span>
            <span>Tạo Tác Phẩm Mới</span>
        </a>
    </div>

    @if($novels->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-slate-900/60 sepia:bg-sepia-card rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm p-8">
            <span class="text-4xl" aria-hidden="true">📚</span>
            <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text mt-3">Chưa có tác phẩm nào</h3>
            <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs mt-1 max-w-sm mx-auto">
                Bắt đầu hành trình sáng tác số ngay hôm nay với công cụ soạn thảo siêu mượt và hỗ trợ bản quyền đầy đủ.
            </p>
            <a href="/tac-gia/truyen/create" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 transition-colors">
                <span>Tạo Tác Phẩm Mới</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($novels as $novel)
                <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border p-5 hover:border-slate-300 dark:hover:border-slate-700 sepia:hover:border-sepia-border shadow-sm sepia:shadow-amber-900/5 transition-colors shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-start gap-4 mb-4">
                            @if($novel->cover_image)
                                <img src="{{ \Illuminate\Support\Str::startsWith($novel->cover_image, ['/', 'http://', 'https://']) ? $novel->cover_image : \Illuminate\Support\Facades\Storage::url($novel->cover_image) }}" alt="{{ $novel->title }}" width="64" height="88" class="w-16 h-22 object-cover rounded-xl border border-slate-200 dark:border-slate-700 sepia:border-sepia-border shadow-sm">
                            @else
                                <div class="w-16 h-22 bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 rounded-xl flex items-center justify-center text-2xl border border-slate-200 dark:border-slate-700 sepia:border-sepia-border text-slate-400">
                                    <span aria-hidden="true">📖</span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-300 sepia:text-amber-900 border border-indigo-500/20 sepia:border-amber-800/30 mb-1">
                                    {{ $novel->age_rating }}
                                </span>
                                <h3 class="font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text text-base truncate">
                                    {{ $novel->title }}
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted line-clamp-2 mt-1 leading-relaxed">
                                    {{ $novel->synopsis }}
                                </p>
                            </div>
                        </div>

                        <div class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted space-y-1.5 py-3 border-t border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                            <div class="flex justify-between">
                                <span>Số chương:</span>
                                <strong class="text-slate-900 dark:text-slate-200 sepia:text-sepia-text font-bold">{{ $novel->chapters->count() }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Bản quyền:</span>
                                <span class="text-emerald-700 dark:text-emerald-400 sepia:text-amber-900 uppercase font-mono text-[11px] font-semibold">{{ $novel->copyright_type }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60 space-y-2">
                        <!-- Primary Chapter Creation CTA -->
                        <a href="{{ route('author.chapters.create', $novel->id) }}" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 sepia:focus-visible:ring-amber-700 transition-colors" aria-label="Soạn Chương mới cho tác phẩm {{ $novel->title }}">
                            <span aria-hidden="true" class="text-sm">✍️</span>
                            <span>+ Soạn Chương</span>
                        </a>

                        <!-- Secondary Actions Grid -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <a href="{{ route('author.novels.bulk-chapters.create', $novel->id) }}" class="col-span-1 text-center py-2 px-1 rounded-xl bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 text-[11px] text-slate-700 dark:text-slate-300 sepia:text-sepia-text font-medium border border-slate-200 dark:border-slate-700 sepia:border-sepia-border transition-colors flex flex-col items-center justify-center min-h-[38px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" title="Tách chương hàng loạt" aria-label="Tách Chương hàng loạt cho {{ $novel->title }}">
                                <span aria-hidden="true" class="text-xs">📥</span>
                                <span class="truncate w-full text-center">Tách Chương</span>
                            </a>
                            <a href="{{ route('author.novels.3d-assets.index', $novel->id) }}" class="col-span-1 text-center py-2 px-1 rounded-xl bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 text-[11px] text-slate-700 dark:text-slate-300 sepia:text-sepia-text font-medium border border-slate-200 dark:border-slate-700 sepia:border-sepia-border transition-colors flex flex-col items-center justify-center min-h-[38px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" title="Quản lý kho mô hình 3D" aria-label="Kho 3D tác phẩm {{ $novel->title }}">
                                <span aria-hidden="true" class="text-xs">🎨</span>
                                <span class="truncate w-full text-center">Kho 3D</span>
                            </a>
                            <a href="/tac-gia/truyen/{{ $novel->id }}/edit" class="col-span-1 text-center py-2 px-1 rounded-xl bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 text-[11px] text-slate-700 dark:text-slate-300 sepia:text-sepia-text font-medium border border-slate-200 dark:border-slate-700 sepia:border-sepia-border transition-colors flex flex-col items-center justify-center min-h-[38px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" title="Sửa thông tin tác phẩm" aria-label="Sửa thông tin tác phẩm {{ $novel->title }}">
                                <span aria-hidden="true" class="text-xs">⚙️</span>
                                <span class="truncate w-full text-center">Sửa Truyện</span>
                            </a>
                            <a href="{{ route('reader.stories.show', $novel->slug) }}" target="_blank" rel="noopener noreferrer" class="col-span-1 text-center py-2 px-1 rounded-xl bg-slate-50 dark:bg-slate-900 sepia:bg-sepia-input hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 text-[11px] text-slate-600 dark:text-slate-400 sepia:text-sepia-muted border border-slate-200 dark:border-slate-800 sepia:border-sepia-border transition-colors flex flex-col items-center justify-center min-h-[38px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" title="Xem trên giao diện người đọc" aria-label="Xem trên giao diện người đọc">
                                <span aria-hidden="true" class="text-xs">👁️</span>
                                <span class="truncate w-full text-center">Xem Đọc</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $novels->links() }}
        </div>
    @endif
</div>
@endsection
