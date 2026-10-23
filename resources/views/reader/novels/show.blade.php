@extends('layouts.reader')

@section('content')
<div class="space-y-12 pb-16" x-data="{ ratingModal: false, selectedRating: 5 }">
    <!-- Novel Hero Banner -->
    <div class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-gradient-to-b from-indigo-950/20 via-transparent to-transparent py-10 lg:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                <!-- Cover Image -->
                <div class="md:col-span-4 lg:col-span-3 flex justify-center">
                    <div class="w-56 sm:w-64 aspect-[3/4] rounded-2xl overflow-hidden shadow-book border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-800 relative group">
                        @if ($novel->cover_image)
                            <img src="{{ $novel->cover_image }}" alt="{{ $novel->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-tr from-indigo-900/40 to-purple-900/40">
                                <span class="font-heading text-4xl text-indigo-400 font-bold block mb-2">3D</span>
                                <span class="text-xs text-slate-400 font-medium">Tiểu Thuyết Tương Tác</span>
                            </div>
                        @endif

                        @if ($novel->assets3d->isNotEmpty())
                            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-indigo-600/90 text-white text-[10px] font-bold shadow-lg backdrop-blur flex items-center gap-1.5 border border-indigo-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Có 3D Engine
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Info Column -->
                <div class="md:col-span-8 lg:col-span-9 space-y-5">
                    <!-- Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($novel->genres as $genre)
                            <a href="/genres/{{ $genre->slug }}" class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 hover:bg-indigo-500/30 transition">
                                {{ $genre->name }}
                            </a>
                        @endforeach

                        @php
                            $ageMap = [
                                '13_plus' => '13+ Thiếu niên',
                                '16_plus' => '16+ Trưởng thành',
                                '18_plus' => '18+ Giới hạn',
                                'all' => 'Mọi lứa tuổi',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                            {{ $ageMap[$novel->age_rating] ?? $novel->age_rating }}
                        </span>

                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                            Bản quyền: {{ $novel->copyright_type === 'original' ? 'Tác Phẩm Gốc' : $novel->copyright_type }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
                        {{ $novel->title }}
                    </h1>

                    <!-- Author & Metrics -->
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <span>Tác giả:</span>
                            <strong class="text-slate-900 dark:text-slate-200 sepia:text-sepia-text">
                                {{ $novel->author->authorProfile->pen_name ?? $novel->author->name }}
                            </strong>
                        </span>
                        <span>•</span>
                        <span class="font-mono tabular-nums">{{ $novel->total_chapters }} chương</span>
                        <span>•</span>
                        <span class="font-mono tabular-nums">{{ number_format($novel->total_words) }} chữ</span>
                        <span>•</span>
                        <span class="font-mono tabular-nums">{{ number_format($novel->view_count) }} lượt đọc</span>
                        <span>•</span>
                        <span class="text-amber-500 font-bold">★ {{ number_format($novel->rating_avg, 2) }}</span>
                    </div>

                    <!-- Synopsis -->
                    <div class="pt-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h3>
                        <div class="text-sm text-slate-700 dark:text-slate-300 sepia:text-sepia-text leading-relaxed whitespace-pre-line bg-white/50 dark:bg-slate-900/40 sepia:bg-sepia-50 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border">
                            {{ $novel->synopsis }}
                        </div>
                    </div>

                    <!-- CTAs & Actions -->
                    <div class="flex flex-wrap items-center gap-3 pt-3">
                        @if ($novel->chapters->isNotEmpty())
                            <a
                                href="/stories/{{ $novel->slug }}/read/{{ $novel->chapters->first()->chapter_number }}"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/25 sepia:shadow-amber-900/25 transition-all transform hover:-translate-y-0.5 flex items-center gap-2"
                            >
                                <span aria-hidden="true">📖</span> Đọc Từ Đầu ({{ $novel->chapters->first()->title }})
                            </a>
                        @endif

                        <!-- Bookmark Button -->
                        <button
                            type="button"
                            id="btn-bookmark-toggle"
                            onclick="toggleStoryBookmark({{ $novel->id }})"
                            class="px-5 py-3 rounded-xl border {{ !empty($isBookmarked) ? 'border-indigo-600 bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'border-slate-300 dark:border-slate-700 sepia:border-sepia-border hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text' }} text-xs font-bold transition flex items-center gap-2"
                        >
                            <span aria-hidden="true">🔖</span> <span id="bookmark-btn-label">{{ !empty($isBookmarked) ? 'Đã Lưu Vào Tủ Sách' : 'Lưu Vào Tủ Sách' }}</span>
                        </button>

                        <!-- Share Button -->
                        <button
                            type="button"
                            id="btn-share-novel"
                            onclick="shareStory()"
                            class="px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 sepia:border-sepia-border hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text text-xs font-bold transition flex items-center gap-1.5"
                            title="Chia sẻ tác phẩm"
                        >
                            <span aria-hidden="true">🔗</span> <span>Chia Sẻ</span>
                        </button>

                        <!-- Rating Trigger Button -->
                        <button
                            type="button"
                            id="btn-rate-novel"
                            @click="ratingModal = true"
                            class="px-4 py-3 rounded-xl border border-amber-400/40 bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 text-xs font-bold transition flex items-center gap-1.5"
                            title="Đánh giá tác phẩm"
                        >
                            <span aria-hidden="true">★</span> <span>Đánh Giá</span>
                        </button>

                        @if ($novel->assets3d->isNotEmpty())
                            <a
                                href="{{ route('reader.novels.exhibition', $novel->slug) }}"
                                class="px-5 py-3 rounded-xl bg-purple-600/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-300 border border-purple-500/30 hover:bg-purple-600/20 text-xs font-bold transition flex items-center gap-2"
                            >
                                <span aria-hidden="true">✨</span> Phòng Trưng Bày 3D ({{ $novel->assets3d->count() }} mô hình)
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3D Assets Showcase in this Novel -->
    @if ($novel->assets3d->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text flex items-center gap-2">
                        <span>✨</span> Cổ Vật & Thần Binh 3D
                    </h2>
                    <p class="text-xs text-slate-500">Các mô hình 3D tương tác liên kết trực tiếp với diễn biến tác phẩm</p>
                </div>
                <a href="{{ route('reader.novels.exhibition', $novel->slug) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Vào phòng triển lãm 3D →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($novel->assets3d as $asset)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-indigo-500/10 flex items-center justify-center text-xl shrink-0">
                            🗡️
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white sepia:text-sepia-text truncate">{{ $asset->name }}</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-1">{{ $asset->description ?? 'Mô hình 3D tương tác' }}</p>
                            <span class="inline-block mt-1 text-[10px] font-mono text-indigo-600 dark:text-indigo-400 font-semibold uppercase">
                                {{ $asset->asset_type }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Chapter Directory Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-4">
            <div>
                <h2 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                    Danh Sách Chương
                </h2>
                <p class="text-xs text-slate-500">Tổng cộng {{ $novel->chapters->count() }} chương đã phát hành</p>
            </div>

            <!-- Chapter Search & Order Toggle -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    id="btn-sort-chapters"
                    onclick="toggleChapterOrder()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:border-indigo-500 transition flex items-center gap-1.5"
                >
                    <span id="sort-order-icon">↓</span> <span id="sort-order-label">Cũ nhất trước</span>
                </button>
                <input
                    type="search"
                    id="chapter-filter-input"
                    aria-label="Lọc danh sách chương"
                    placeholder="Lọc số chương, tên chương…"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900 sepia:bg-sepia-50 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    oninput="filterChapterRows(this.value)"
                >
            </div>
        </div>

        <!-- Chapter Rows List -->
        <div id="chapters-list-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse ($novel->chapters as $ch)
                <a
                    href="/stories/{{ $novel->slug }}/read/{{ $ch->chapter_number }}"
                    data-chapter-title="{{ strtolower($ch->title) }}"
                    class="chapter-item-row p-4 rounded-2xl border border-slate-200 dark:border-slate-800/80 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 hover:border-indigo-500/60 hover:shadow-sm transition flex items-center justify-between gap-4 group"
                >
                    <div class="min-w-0 space-y-1">
                        <span class="text-xs font-bold text-slate-900 dark:text-slate-200 sepia:text-sepia-text group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition truncate block">
                            {{ $ch->title }}
                        </span>
                        <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono tabular-nums">
                            <span>{{ number_format($ch->word_count) }} chữ</span>
                            <span>•</span>
                            <span>{{ $ch->published_at?->diffForHumans() ?? 'Đã phát hành' }}</span>
                        </div>
                    </div>

                    <span class="shrink-0 text-xs text-indigo-500 group-hover:translate-x-1 transition-transform">
                        →
                    </span>
                </a>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 text-xs">
                    Chưa có chương nào được xuất bản cho tác phẩm này.
                </div>
            @endforelse
        </div>
    <!-- Interactive Rating Modal -->
    <x-modal name="ratingModal" title="Đánh Giá Tác Phẩm">
        <form onsubmit="submitRating(event)" class="space-y-4">
            <p class="text-xs text-slate-300 sepia:text-sepia-text">Chọn số sao đánh giá cho tác phẩm <strong>{{ $novel->title }}</strong>:</p>
            <div class="flex items-center justify-center gap-2 py-2">
                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                    <button
                        type="button"
                        @click="selectedRating = star"
                        :aria-label="'Đánh giá ' + star + ' sao'"
                        class="text-2xl transition transform hover:scale-125 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 rounded-lg p-1"
                        :class="star <= selectedRating ? 'text-amber-400' : 'text-slate-600'"
                    >★</button>
                </template>
            </div>
            <div>
                <label for="rating-comment" class="block text-xs font-semibold text-slate-300 sepia:text-sepia-text mb-1">Cảm nghĩ của bạn (Tùy chọn):</label>
                <textarea id="rating-comment" rows="3" placeholder="Viết vài dòng chia sẻ về truyện…" class="w-full rounded-xl bg-slate-950/60 sepia:bg-sepia-100 border border-slate-800 sepia:border-sepia-border px-3 py-2 text-xs text-white sepia:text-sepia-text placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="ratingModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white">Hủy</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md">Gửi Đánh Giá</button>
            </div>
        </form>
    </x-modal>
</div>

<script>
    function filterChapterRows(term) {
        var query = term.toLowerCase().trim();
        var rows = document.querySelectorAll('.chapter-item-row');
        rows.forEach(function(row) {
            var title = row.getAttribute('data-chapter-title') || '';
            if (!query || title.includes(query)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    }

    var sortAsc = true;
    function toggleChapterOrder() {
        sortAsc = !sortAsc;
        var container = document.getElementById('chapters-list-container');
        if (!container) return;
        var rows = Array.from(container.querySelectorAll('.chapter-item-row'));
        rows.reverse();
        rows.forEach(function(row) {
            container.appendChild(row);
        });
        var label = document.getElementById('sort-order-label');
        var icon = document.getElementById('sort-order-icon');
        if (label) label.textContent = sortAsc ? 'Cũ nhất trước' : 'Mới nhất trước';
        if (icon) icon.textContent = sortAsc ? '↓' : '↑';
    }

    function shareStory() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
            if (window.showToast) {
                window.showToast('Đã sao chép liên kết truyện vào clipboard!', 'success');
            }
        }
    }

    function submitRating(e) {
        if (e) e.preventDefault();
        if (window.showToast) {
            window.showToast('Cảm ơn bạn đã gửi đánh giá tác phẩm!', 'success');
        }
        var modalCloseBtn = document.querySelector('[aria-label="Đóng cửa sổ"]');
        if (modalCloseBtn) modalCloseBtn.click();
    }

    function toggleStoryBookmark(novelId) {
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/api/bookmarks/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ novel_id: novelId })
        })
        .then(function(res) {
            if (res.status === 401) {
                window.location.href = '/login';
                return;
            }
            return res.json();
        })
        .then(function(data) {
            if (data && data.bookmarked !== undefined) {
                var btn = document.getElementById('btn-bookmark-toggle');
                var label = document.getElementById('bookmark-btn-label');
                if (label) label.textContent = data.bookmarked ? 'Đã Lưu Vào Tủ Sách' : 'Lưu Vào Tủ Sách';
                if (btn) {
                    if (data.bookmarked) {
                        btn.className = 'px-5 py-3 rounded-xl border border-indigo-600 bg-indigo-600 text-white shadow-md shadow-indigo-600/20 text-xs font-bold transition flex items-center gap-2';
                    } else {
                        btn.className = 'px-5 py-3 rounded-xl border border-slate-300 dark:border-slate-700 sepia:border-sepia-border hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text text-xs font-bold transition flex items-center gap-2';
                    }
                }
                if (window.showToast) {
                    window.showToast(data.message, 'success');
                }
            }
        })
        .catch(function(err) {
            console.error(err);
        });
    }
</script>
@endsection
