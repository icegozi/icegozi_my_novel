@extends('layouts.reader')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Tiến Trình Đọc</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Lịch Sử Đọc</h1>
        <p class="text-xs text-slate-500">Các tác phẩm và chương bạn đã từng đọc, tự động lưu lại vị trí đoạn văn</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs font-bold">
        <a href="/library" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Tất Cả Tủ Sách
        </a>
        <a href="/history" class="pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400">
            Lịch Sử Đọc ({{ $histories->total() }})
        </a>
        <a href="/bookmarks" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Dấu Trang Đã Lưu
        </a>
    </div>

    <div class="space-y-4">
        @forelse ($histories as $history)
            @if ($history->novel && $history->lastChapter)
                <div id="history-item-{{ $history->id }}" class="p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 flex items-center justify-between gap-4 hover:border-indigo-500/40 transition">
                    <div class="flex items-center gap-4 min-w-0">
                        <img src="{{ $history->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=150&q=80' }}" alt="{{ $history->novel->title }}" class="w-14 h-20 rounded-xl object-cover shrink-0">
                        <div class="min-w-0 space-y-1">
                            <a href="/stories/{{ $history->novel->slug }}" class="font-bold text-sm text-slate-900 dark:text-white sepia:text-sepia-text truncate block hover:text-indigo-600 transition">
                                {{ $history->novel->title }}
                            </a>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold truncate">
                                Đang đọc dở: {{ $history->lastChapter->title }}
                            </p>
                            <span class="text-[11px] text-slate-400 block tabular-nums">
                                Đọc lần cuối: {{ $history->updated_at?->diffForHumans() ?? 'Gần đây' }}
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <a href="/stories/{{ $history->novel->slug }}/read/{{ $history->lastChapter->chapter_number }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-sm">
                            Đọc Tiếp
                        </a>
                        <button type="button" 
                                onclick="deleteHistory({{ $history->id }})" 
                                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 sepia:border-sepia-border text-slate-500 hover:text-rose-600 hover:border-rose-300 dark:hover:border-rose-900/50 text-xs font-semibold transition flex items-center gap-1"
                                title="Xóa khỏi lịch sử đọc"
                                aria-label="Xóa khỏi lịch sử">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="hidden sm:inline">Xóa</span>
                        </button>
                    </div>
                </div>
            @endif
        @empty
            <div class="py-16 text-center text-slate-500 space-y-3">
                <span class="text-4xl block">⏳</span>
                <p class="text-sm font-semibold">Bạn chưa có lịch sử đọc nào</p>
                <p class="text-xs">Khi bạn đọc các chương truyện, hệ thống sẽ tự động ghi nhớ vị trí để bạn tiếp tục bất cứ lúc nào.</p>
                <a href="/" class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-500 transition">
                    Bắt đầu đọc truyện
                </a>
            </div>
        @endforelse
    </div>

    <div class="pt-6">
        {{ $histories->links() }}
    </div>
</div>

<script>
function deleteHistory(id) {
    if (!confirm('Bạn có chắc muốn xóa tác phẩm này khỏi lịch sử đọc?')) return;
    
    fetch('/api/history/' + id, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            const el = document.getElementById('history-item-' + id);
            if (el) {
                el.classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-300');
                setTimeout(() => el.remove(), 300);
            }
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Đã xóa khỏi lịch sử đọc', 'success');
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (typeof window.showToast === 'function') {
            window.showToast('Không thể xóa lịch sử đọc lúc này', 'error');
        }
    });
}
</script>
@endsection
