<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hàng Đợi Kiểm Duyệt Nội Dung - Quản Trị Hệ Thống</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white"
      x-data="{ 
          activeTab: 'chapters',
          rejectModal: false,
          rejectType: '',
          rejectUrl: '',
          rejectItemTitle: '',
          rejectReason: '',
          presetReasons: [
              'Vi phạm quy định thuần phong mỹ tục, chứa nội dung khiêu dâm hoặc kích động bạo lực.',
              'Nghi vấn sao chép, vi phạm quyền tác giả không có giấy ủy quyền hợp lệ.',
              'Chất lượng nội dung không đạt chuẩn (lỗi chính tả nghiêm trọng, spam ký tự, định dạng vỡ).',
              'Gán nhãn độ tuổi không phù hợp với nội dung truyện.',
              'Khai báo bản quyền số không trùng khớp thông tin định danh tác giả.'
          ],
          openReject(type, url, title) {
              this.rejectType = type;
              this.rejectUrl = url;
              this.rejectItemTitle = title;
              this.rejectReason = this.presetReasons[0];
              this.rejectModal = true;
          }
      }">

    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="/" class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                    <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL ADMIN</span>
                </a>
                <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    Cổng Kiểm Duyệt • NĐ 147/2024 & NĐ 17/2023
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span>Nhân viên: <strong class="text-slate-200">{{ auth()->user()?->name ?? 'Moderator' }}</strong> ({{ auth()->user()?->role ?? 'moderator' }})</span>
                <span class="text-slate-600">|</span>
                <span class="hidden lg:inline text-slate-500">Phím tắt: Alt+A (Duyệt nhanh) • Alt+R (Từ chối)</span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
        <!-- Flash notifications -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Hàng Đợi Kiểm Duyệt Xuất Bản</h1>
                <p class="text-sm text-slate-400 mt-1">Kiểm tra tính hợp lệ của nội dung, sàng lọc từ ngữ vi phạm trước khi phân phối công khai.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="activeTab = 'chapters'"
                        :class="activeTab === 'chapters' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-2">
                    <span>Chương Chờ Duyệt</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-900/60 text-[11px] font-bold">{{ $pendingChapters->count() }}</span>
                </button>
                <button @click="activeTab = 'novels'"
                        :class="activeTab === 'novels' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-800 text-slate-400 hover:text-white'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-2">
                    <span>Tác Phẩm Mới</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-900/60 text-[11px] font-bold">{{ $pendingNovels->count() }}</span>
                </button>
            </div>
        </div>

        <!-- Tab 1: Chapters Moderation Queue -->
        <section x-show="activeTab === 'chapters'" class="space-y-4">
            @if($pendingChapters->isEmpty())
                <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800/60">
                    <span class="text-4xl block mb-2">🎉</span>
                    <h3 class="text-lg font-bold text-white">Hàng đợi sạch sẽ!</h3>
                    <p class="text-xs text-slate-400 mt-1">Hiện không có chương truyện nào cần kiểm duyệt.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach($pendingChapters as $chapter)
                        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Chờ Duyệt #{{ $chapter->id }}
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        Tác phẩm: <strong class="text-slate-200">{{ $chapter->novel?->title ?? 'N/A' }}</strong>
                                    </span>
                                    <span class="text-slate-600">•</span>
                                    <span class="text-xs text-slate-400">
                                        Tác giả: <span class="text-indigo-400 font-medium">{{ $chapter->novel?->author?->authorProfile?->pen_name ?? $chapter->novel?->author?->name }}</span>
                                    </span>
                                    <span class="text-slate-600">•</span>
                                    <span class="text-xs text-slate-500">{{ $chapter->created_at->diffForHumans() }}</span>
                                </div>
                                <h3 class="text-base font-bold text-white">
                                    Chương {{ (float)$chapter->chapter_number }}: {{ $chapter->title }}
                                </h3>
                                <div class="flex items-center gap-4 text-xs text-slate-400">
                                    <span>Độ dài: <strong class="text-slate-300">{{ number_format($chapter->word_count) }} từ</strong></span>
                                    <span>Phiên bản: <strong class="text-slate-300">v{{ $chapter->revision }}</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end lg:self-center">
                                <a href="{{ route('admin.moderation.chapters.review', $chapter) }}"
                                   class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 transition-all flex items-center gap-1.5">
                                    <span>🔍 Soát Lỗi & Highlight</span>
                                </a>

                                <form action="{{ route('admin.moderation.chapters.approve', $chapter) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                                        <span>✓ 1-Click Duyệt</span>
                                    </button>
                                </form>

                                <button type="button"
                                        @click="openReject('chapter', '{{ route('admin.moderation.chapters.reject', $chapter) }}', '{{ addslashes($chapter->title) }}')"
                                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-all">
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
                    <span class="text-4xl block mb-2">🎉</span>
                    <h3 class="text-lg font-bold text-white">Không có tác phẩm chờ duyệt!</h3>
                    <p class="text-xs text-slate-400 mt-1">Tất cả tác phẩm mới nộp đã được xử lý.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach($pendingNovels as $novel)
                        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-4">
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
                                <h3 class="text-lg font-bold text-white">{{ $novel->title }}</h3>
                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $novel->synopsis }}</p>
                            </div>

                            <div class="flex items-center gap-2 self-end lg:self-center">
                                <form action="{{ route('admin.moderation.novels.approve', $novel) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                                        <span>✓ 1-Click Duyệt Tác Phẩm</span>
                                    </button>
                                </form>

                                <button type="button"
                                        @click="openReject('novel', '{{ route('admin.moderation.novels.reject', $novel) }}', '{{ addslashes($novel->title) }}')"
                                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-all">
                                    ✕ Từ Chối
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Rejection Reason Modal -->
        <div x-show="rejectModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @keydown.escape.window="rejectModal = false">
            <div class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl space-y-4"
                 @click.away="rejectModal = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="text-rose-500">⚠️</span>
                        <span>Từ Chối Phê Duyệt: <span class="text-slate-300 font-semibold" x-text="rejectItemTitle"></span></span>
                    </h3>
                    <button @click="rejectModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <p class="text-xs text-slate-400">
                    Vui lòng chọn lý do từ chối mẫu hoặc nhập nội dung cụ thể để gửi thông báo hướng dẫn tác giả khắc phục.
                </p>

                <form :action="rejectUrl" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Lý do mẫu:</label>
                        <select @change="rejectReason = $event.target.value"
                                class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
                            <template x-for="preset in presetReasons" :key="preset">
                                <option :value="preset" x-text="preset"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Chi tiết giải trình từ chối:</label>
                        <textarea name="rejection_reason"
                                  x-model="rejectReason"
                                  rows="4"
                                  required
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-rose-500 leading-relaxed"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="rejectModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-slate-200">
                            Hủy bỏ
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/30 transition-all">
                            Xác Nhận Từ Chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
