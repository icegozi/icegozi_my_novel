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
                                class="absolute top-2 right-2 p-1.5 rounded-lg bg-black/60 hover:bg-rose-600 text-white opacity-0 group-hover:opacity-100 focus-visible:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 transition-opacity transition-colors duration-200 shadow z-10"
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
                                Đọc ngay →
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-full py-16 text-center space-y-3 text-slate-500">
                <span class="text-4xl block" aria-hidden="true">📚</span>
                <p class="text-sm font-semibold">Tủ sách của bạn hiện đang trống</p>
                <p class="text-xs">Hãy nhấn vào nút “Lưu Vào Tủ Sách” khi xem các tác phẩm yêu thích để theo dõi tại đây.</p>
                <a href="/popular" class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-500 transition">
                    Khám phá truyện hay
                </a>
            </div>
        @endforelse
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
