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
                <div id="bookmark-card-{{ $bm->novel->id }}" class="group flex flex-col space-y-2 relative">
                    <div class="relative aspect-[3/4] rounded-2xl overflow-hidden shadow-book border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-800 transition-transform duration-300 group-hover:scale-105 group-hover:shadow-glow">
                        <a href="/stories/{{ $bm->novel->slug }}" class="block w-full h-full">
                            <img src="{{ $bm->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=300&q=80' }}" alt="{{ $bm->novel->title }}" width="300" height="400" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                        <span class="absolute bottom-2 right-2 text-[10px] font-bold text-amber-400 bg-black/70 px-1.5 py-0.5 rounded pointer-events-none">
                            ★ {{ number_format($bm->novel->rating_avg, 1) }}
                        </span>
                        <button type="button" 
                                onclick="removeBookmark({{ $bm->novel->id }})" 
                                class="absolute top-2 right-2 p-1.5 rounded-lg bg-black/60 hover:bg-rose-600 text-white opacity-0 group-hover:opacity-100 focus-visible:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 transition-all duration-200 shadow z-10"
                                title="Bỏ lưu khỏi Tủ Sách"
                                aria-label="Bỏ lưu">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

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

<script>
function removeBookmark(novelId) {
    fetch('/api/bookmarks/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ novel_id: novelId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            const card = document.getElementById('bookmark-card-' + novelId);
            if (card) {
                card.classList.add('opacity-0', 'scale-90', 'transition-all', 'duration-300');
                setTimeout(() => card.remove(), 300);
            }
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Đã cập nhật Tủ Sách', 'success');
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (typeof window.showToast === 'function') {
            window.showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
        }
    });
}
</script>
@endsection
