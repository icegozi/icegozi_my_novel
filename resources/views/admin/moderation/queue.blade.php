@extends('layouts.admin', [
    'title' => 'Hàng Đợi Kiểm Duyệt Nội Dung - Quản Trị Hệ Thống',
    'bodyAttributes' => 'x-data="{ 
        activeTab: \'chapters\',
        rejectModal: false,
        rejectType: \'\',
        rejectUrl: \'\',
        rejectItemTitle: \'\',
        rejectReason: \'\',
        presetReasons: [
            \'Vi phạm quy định thuần phong mỹ tục, chứa nội dung khiêu dâm hoặc kích động bạo lực.\',
            \'Nghi vấn sao chép, vi phạm quyền tác giả không có giấy ủy quyền hợp lệ.\',
            \'Chất lượng nội dung không đạt chuẩn (lỗi chính tả nghiêm trọng, spam ký tự, định dạng vỡ).\',
            \'Gán nhãn độ tuổi không phù hợp với nội dung truyện.\',
            \'Khai báo bản quyền số không trùng khớp thông tin định danh tác giả.\'
        ],
        openReject(type, url, title) {
            this.rejectType = type;
            this.rejectUrl = url;
            this.rejectItemTitle = title;
            this.rejectReason = this.presetReasons[0];
            this.rejectModal = true;
        }
    }"'
])

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white">Hàng Đợi Kiểm Duyệt Xuất Bản</h1>
            <p class="text-sm text-slate-400 mt-1">Kiểm tra tính hợp lệ của nội dung, sàng lọc từ ngữ vi phạm trước khi phân phối công khai.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="activeTab = 'chapters'"
                    :class="activeTab === 'chapters' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2 min-h-[44px]">
                <span>Chương Chờ Duyệt</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-900/60 text-[11px] font-bold">{{ $pendingChapters->count() }}</span>
            </button>
            <button @click="activeTab = 'novels'"
                    :class="activeTab === 'novels' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2 min-h-[44px]">
                <span>Tác Phẩm Mới</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-900/60 text-[11px] font-bold">{{ $pendingNovels->count() }}</span>
            </button>
        </div>
    </div>

    <!-- Tab 1: Chapters Moderation Queue -->
    <section x-show="activeTab === 'chapters'" class="space-y-4">
        @if($pendingChapters->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800/60">
                <span class="text-4xl block mb-2" aria-hidden="true">🎉</span>
                <h3 class="text-lg font-bold text-white">Hàng đợi sạch sẽ!</h3>
                <p class="text-xs text-slate-400 mt-1">Hiện không có chương truyện nào cần kiểm duyệt.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($pendingChapters as $chapter)
                    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Chờ Duyệt #{{ $chapter->id }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    Tác phẩm: <strong class="text-slate-200">{{ $chapter->novel?->title ?? 'N/A' }}</strong>
                                </span>
                                <span class="text-slate-600" aria-hidden="true">•</span>
                                <span class="text-xs text-slate-400">
                                    Tác giả: <span class="text-indigo-400 font-medium">{{ $chapter->novel?->author?->authorProfile?->pen_name ?? $chapter->novel?->author?->name }}</span>
                                </span>
                                <span class="text-slate-600" aria-hidden="true">•</span>
                                <span class="text-xs text-slate-500">{{ $chapter->created_at->diffForHumans() }}</span>
                            </div>
                            <h2 class="text-base font-bold text-white">
                                Chương {{ (float)$chapter->chapter_number }}: {{ $chapter->title }}
                            </h2>
                            <div class="flex items-center gap-4 text-xs text-slate-400">
                                <span>Độ dài: <strong class="text-slate-300">{{ number_format($chapter->word_count) }} từ</strong></span>
                                <span>Phiên bản: <strong class="text-slate-300">v{{ $chapter->revision }}</strong></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end lg:self-center">
                            <a href="{{ route('admin.moderation.chapters.review', $chapter) }}"
                               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors flex items-center gap-1.5 min-h-[44px]">
                                <span>🔍 Soát Lỗi & Highlight</span>
                            </a>

                            <form action="{{ route('admin.moderation.chapters.approve', $chapter) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-colors flex items-center gap-1.5 min-h-[44px]">
                                    <span>✓ 1-Click Duyệt</span>
                                </button>
                            </form>

                            <button type="button"
                                    @click="openReject('chapter', '{{ route('admin.moderation.chapters.reject', $chapter) }}', '{{ addslashes($chapter->title) }}')"
                                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors min-h-[44px]">
                                ✕ Từ Chối
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Tab 2: Novels Moderation Queue -->
    <section x-show="activeTab === 'novels'" class="space-y-4">
        @if($pendingNovels->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800/60">
                <span class="text-4xl block mb-2" aria-hidden="true">🎉</span>
                <h3 class="text-lg font-bold text-white">Không có tác phẩm chờ duyệt!</h3>
                <p class="text-xs text-slate-400 mt-1">Tất cả tác phẩm mới nộp đã được xử lý.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($pendingNovels as $novel)
                    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-2 max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Tác Phẩm Chờ Duyệt #{{ $novel->id }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    {{ $novel->age_rating }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                    Quyền: {{ $novel->copyright_type }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    Tác giả: <span class="text-indigo-400 font-medium">{{ $novel->author?->authorProfile?->pen_name ?? $novel->author?->name }}</span>
                                </span>
                            </div>
                            <h2 class="text-lg font-bold text-white">{{ $novel->title }}</h2>
                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $novel->synopsis }}</p>
                        </div>

                        <div class="flex items-center gap-2 self-end lg:self-center">
                            <form action="{{ route('admin.moderation.novels.approve', $novel) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-colors flex items-center gap-1.5 min-h-[44px]">
                                    <span>✓ 1-Click Duyệt Tác Phẩm</span>
                                </button>
                            </form>

                            <button type="button"
                                    @click="openReject('novel', '{{ route('admin.moderation.novels.reject', $novel) }}', '{{ addslashes($novel->title) }}')"
                                    class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-colors min-h-[44px]">
                                ✕ Từ Chối
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Rejection Reason Modal -->
    <x-modal name="rejectModal" title="Từ Chối Phê Duyệt">
        <p class="text-xs text-slate-400 mb-4">
            Vui lòng chọn lý do từ chối mẫu hoặc nhập nội dung cụ thể để gửi thông báo hướng dẫn tác giả khắc phục.
        </p>

        <form :action="rejectUrl" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="preset-select" class="block text-xs font-semibold text-slate-300 mb-1">Lý do mẫu:</label>
                <select id="preset-select"
                        @change="rejectReason = $event.target.value"
                        class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
                    <template x-for="preset in presetReasons" :key="preset">
                        <option :value="preset" x-text="preset"></option>
                    </template>
                </select>
            </div>

            <div>
                <label for="rejection-detail" class="block text-xs font-semibold text-slate-300 mb-1">Chi tiết giải trình từ chối:</label>
                <textarea id="rejection-detail"
                          name="rejection_reason"
                          x-model="rejectReason"
                          rows="4"
                          required
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-rose-500 leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button type="button" @click="rejectModal = false" variant="ghost" size="sm">
                    Hủy bỏ
                </x-button>
                <x-button type="submit" variant="danger" size="sm">
                    Xác Nhận Từ Chối
                </x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
