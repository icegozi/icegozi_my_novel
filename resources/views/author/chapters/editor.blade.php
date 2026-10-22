<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zen Editor - {{ $chapter->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-reading { font-family: 'Lora', Georgia, serif; }
        .font-heading { font-family: 'Cinzel', serif; }
        .zen-mode-active header { display: none !important; }
        .zen-mode-active #editor-toolbar { opacity: 0.15; transition: opacity 0.3s; }
        .zen-mode-active #editor-toolbar:hover { opacity: 1; }
        .zen-mode-active main { max-width: 900px !important; margin: 0 auto; padding-top: 3rem; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white">
    <!-- Top Bar -->
    <header class="border-b border-slate-800 bg-slate-900/70 backdrop-blur-xl sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/tac-gia/truyen/{{ $novel->id }}/edit" class="text-xs text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                    &larr; {{ $novel->title }}
                </a>
                <span class="text-slate-600">|</span>
                <span class="text-xs font-semibold text-slate-300">Chương {{ $chapter->chapter_number }}</span>
                <span id="sync-status" class="px-3 py-1 rounded-full text-xs font-medium border bg-emerald-500/10 text-emerald-400 border-emerald-500/20 transition-all">
                    Đã đồng bộ lên máy chủ an toàn
                </span>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-xs text-slate-400 flex items-center gap-1.5">
                    <span>Số từ:</span>
                    <strong id="word-count-display" class="text-slate-200 font-mono">{{ number_format($chapter->word_count) }} chữ</strong>
                </div>
                <button type="button" id="toggle-zen-mode" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all">
                    Zen Mode Toàn Màn Hình
                </button>
            </div>
        </div>
    </header>

    <!-- Editor Main Stage -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-6 py-8 flex flex-col">
        <div id="editor-toolbar" class="mb-4 flex items-center justify-between">
            <div class="text-xs text-slate-400">
                Phiên bản: <strong class="text-indigo-400 font-mono">v{{ $chapter->revision }}</strong>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="px-3 py-1 rounded-lg bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-semibold border border-indigo-500/30 transition-all">
                    + Gắn Mô Hình 3D Vào Đoạn Văn
                </button>
            </div>
        </div>

        <div class="flex-1 flex flex-col bg-slate-900/40 rounded-2xl border border-slate-800/80 p-8 shadow-2xl">
            <!-- Chapter Title Input -->
            <input
                type="text"
                id="chapter-title"
                value="{{ $chapter->title }}"
                placeholder="Nhập tiêu đề chương truyện..."
                class="w-full bg-transparent text-2xl md:text-3xl font-extrabold text-white placeholder-slate-600 border-b border-slate-800 pb-4 mb-6 focus:outline-none focus:border-indigo-500 transition-colors"
            >

            <!-- Chapter Content Textarea -->
            <textarea
                id="chapter-content"
                rows="22"
                placeholder="Bắt đầu viết nội dung chương tại đây. Bộ đệm IndexedDB sẽ tự động lưu ngay khi bạn gõ..."
                class="flex-1 w-full bg-transparent text-slate-200 font-reading text-lg leading-relaxed placeholder-slate-600 focus:outline-none resize-none"
            >{{ $chapter->content }}</textarea>
        </div>
    </main>

    <script src="/resources/js/zen-editor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new ZenEditorEngine({
                chapterId: {{ $chapter->id }},
                novelId: {{ $novel->id }},
                initialRevision: {{ $chapter->revision }},
                autosaveUrl: '/api/author/chapters/autosave',
                csrfToken: '{{ csrf_token() }}'
            });
        });
    </script>
</body>
</html>
