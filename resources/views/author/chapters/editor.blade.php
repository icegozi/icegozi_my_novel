<!DOCTYPE html>
<html lang="vi" class="sepia">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zen Editor - {{ $chapter->title }} - Novel Engine</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        (function() {
            var theme = localStorage.getItem('novel_reader_theme') || 'sepia';
            document.documentElement.className = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-reading { font-family: 'Lora', Georgia, serif; }
        .zen-mode-active header { display: none !important; }
        .zen-mode-active #editor-toolbar { opacity: 0.15; transition: opacity 0.3s; }
        .zen-mode-active #editor-toolbar:hover { opacity: 1; }
        .zen-mode-active main { max-width: 900px !important; margin: 0 auto; padding-top: 2rem; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] sepia:bg-[#f5efe6] text-slate-900 dark:text-slate-100 sepia:text-sepia-text antialiased min-h-screen flex flex-col transition-colors selection:bg-indigo-500 selection:text-white">
    <!-- Top Bar -->
    <header class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white/80 dark:bg-slate-900/80 sepia:bg-sepia-50/90 backdrop-blur-xl sticky top-0 z-40 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/tac-gia/truyen/{{ $novel->id }}/edit" class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text transition-colors flex items-center gap-1.5 font-medium">
                    <span aria-hidden="true">&larr;</span> {{ $novel->title }}
                </a>
                <span class="text-slate-300 dark:text-slate-700 sepia:text-sepia-border">|</span>
                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 sepia:text-sepia-text">Chương {{ $chapter->chapter_number }}</span>
                <span id="sync-status" class="px-3 py-1 rounded-full text-[11px] font-medium border bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20 transition-all">
                    Đã đồng bộ lên máy chủ an toàn
                </span>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <div class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted flex items-center gap-1.5">
                    <span>Số từ:</span>
                    <strong id="word-count-display" class="text-slate-900 dark:text-slate-200 sepia:text-sepia-text font-mono font-bold">{{ number_format($chapter->word_count) }} chữ</strong>
                </div>
                <button type="button" id="toggle-zen-mode" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text border border-slate-200 dark:border-slate-700 sepia:border-sepia-border transition-all">
                    Zen Mode Toàn Màn Hình
                </button>
            </div>
        </div>
    </header>

    <!-- Editor Main Stage -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 flex flex-col">
        <div id="editor-toolbar" class="mb-4 flex items-center justify-between">
            <div class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted">
                Phiên bản: <strong class="text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 font-mono font-bold">v{{ $chapter->revision }}</strong>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('author.novels.3d-assets.index', $novel) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 hover:bg-indigo-500/20 text-xs font-semibold border border-indigo-500/25 sepia:border-amber-800/30 transition-all">
                    + Quản Lý Mô Hình 3D
                </a>
            </div>
        </div>

        <div class="flex-1 flex flex-col bg-white dark:bg-slate-900/60 sepia:bg-sepia-card rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 transition-colors">
            <!-- Chapter Title Input -->
            <input
                type="text"
                id="chapter-title"
                value="{{ $chapter->title }}"
                placeholder="Nhập tiêu đề chương truyện..."
                class="w-full bg-transparent text-xl sm:text-2xl md:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text placeholder-slate-400 dark:placeholder-slate-600 sepia:placeholder-sepia-muted border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-4 mb-6 focus:outline-none focus:border-indigo-500 sepia:focus:border-amber-800 transition-colors"
            >

            <!-- Chapter Content Textarea -->
            <textarea
                id="chapter-content"
                rows="22"
                placeholder="Bắt đầu viết nội dung chương tại đây. Bộ đệm IndexedDB sẽ tự động lưu ngay khi bạn gõ..."
                class="flex-1 w-full bg-transparent text-slate-800 dark:text-slate-200 sepia:text-sepia-text font-reading text-base sm:text-lg leading-relaxed placeholder-slate-400 dark:placeholder-slate-600 sepia:placeholder-sepia-muted focus:outline-none resize-none"
            >{{ $chapter->content }}</textarea>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof ZenEditorEngine !== 'undefined') {
                new ZenEditorEngine({
                    chapterId: {{ $chapter->id }},
                    novelId: {{ $novel->id }},
                    initialRevision: {{ $chapter->revision }},
                    autosaveUrl: '/api/author/chapters/autosave',
                    csrfToken: '{{ csrf_token() }}'
                });
            }
        });
    </script>
</body>
</html>
