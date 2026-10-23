<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thẩm Định Nội Dung: Chương {{ (float)$chapter->chapter_number }} - {{ $chapter->novel?->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
        .font-reader { font-family: 'Lora', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white"
      x-data="{
          rejectModal: false,
          rejectReason: 'Vi phạm quy định thuần phong mỹ tục, chứa từ ngữ kích động hoặc nội dung cấm theo quy định pháp luật.'
      }"
      @keydown.alt.a.window="$refs.approveForm.submit()"
      @keydown.alt.r.window="rejectModal = true">

    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.moderation.queue') }}" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs flex items-center gap-1.5 transition-all">
                    <span>&larr; Hàng Đợi</span>
                </a>
                <div>
                    <h2 class="text-xs font-semibold text-slate-400">Thẩm Định Chương Truyện</h2>
                    <p class="text-sm font-bold text-white truncate max-w-md">{{ $chapter->novel?->title }}</p>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-3">
                <form x-ref="approveForm" action="{{ route('admin.moderation.chapters.approve', $chapter) }}" method="POST" class="inline">
                    @csrf
                    <x-button type="submit"
                            size="sm"
                            title="Alt + A"
                            class="!bg-emerald-600 hover:!bg-emerald-500 text-white shadow-emerald-600/30">
                        <span>✓ Duyệt (Alt+A)</span>
                    </x-button>
                </form>

                <x-button type="button"
                        variant="ghost"
                        size="sm"
                        title="Alt + R"
                        @click="rejectModal = true"
                        class="!bg-rose-500/10 hover:!bg-rose-500/20 !text-rose-400 border border-rose-500/30">
                    <span>✕ Từ Chối (Alt+R)</span>
                </x-button>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <!-- Metadata card -->
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 flex flex-wrap items-center justify-between gap-4 text-xs">
            <div class="space-y-1">
                <div class="text-slate-400">
                    Tác giả: <strong class="text-indigo-400">{{ $chapter->novel?->author?->authorProfile?->pen_name ?? $chapter->novel?->author?->name }}</strong>
                </div>
                <div class="text-slate-400 flex items-center gap-2">
                    <span>Độ tuổi:</span>
                    <x-badge :variant="'age' . (int)$chapter->novel?->age_rating" size="xs">{{ $chapter->novel?->age_rating }}+</x-badge>
                    <span>Bản quyền:</span>
                    <x-badge variant="brand" size="xs">{{ $chapter->novel?->copyright_type }}</x-badge>
                </div>
            </div>
            <div class="space-y-1 text-right">
                <div class="text-slate-400">Số lượng từ: <strong class="text-slate-200">{{ number_format($chapter->word_count) }} từ</strong></div>
                <div class="text-slate-400">Thời gian nộp: <span class="text-slate-300">{{ $chapter->created_at->format('H:i d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- Chapter Content Container -->
        <article class="p-8 md:p-12 rounded-3xl bg-slate-900/80 border border-slate-800/80 shadow-xl space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Chương {{ (float)$chapter->chapter_number }}</span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white mt-1">{{ $chapter->title }}</h1>
            </div>

            @include('admin.moderation.partials.content_preview', ['content' => $chapter->content])
        </article>
    </main>

    <!-- Reject Modal -->
    <x-modal name="rejectModal" title="Từ Chối Phê Duyệt Chương Truyện">
        <form action="{{ route('admin.moderation.chapters.reject', $chapter) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Lý do từ chối:</label>
                <textarea name="rejection_reason"
                          x-model="rejectReason"
                          rows="4"
                          required
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-rose-500 leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button type="button" @click="rejectModal = false" variant="ghost" size="sm">
                    Hủy
                </x-button>
                <x-button type="submit" variant="danger" size="sm">
                    Xác Nhận Từ Chối
                </x-button>
            </div>
        </form>
    </x-modal>
</body>
</html>
