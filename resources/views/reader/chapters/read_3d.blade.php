<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $chapter->title }} - {{ $novel->title }} - Trình Đọc 3D</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/resources/css/reader-stage.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-novel { font-family: 'Merriweather', Georgia, serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
    <!-- Three.js Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/DRACOLoader.js"></script>
</head>
<body class="theme-dark min-h-screen flex flex-col antialiased">
    <!-- Top Nav Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/70 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('reader.novels.show', $novel->slug) }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white text-xs shadow-lg shadow-indigo-500/25">3D</span>
                    <span class="text-xs font-bold text-white uppercase tracking-wider hidden sm:inline">{{ $novel->title }}</span>
                </a>
            </div>

            <!-- Chapter selector & Title -->
            <div class="text-center truncate px-4">
                <span class="text-xs font-bold text-slate-200 block truncate">{{ $chapter->title }}</span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" id="toggle-stage-interactive"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-indigo-400 border border-slate-700 flex items-center gap-1.5 transition">
                    <span>🔄</span>
                    <span id="interactive-btn-label">Tương tác 360°</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Reading & 3D Stage Layout -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Column 1: Reading Text Stream -->
        <article class="lg:col-span-7 bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 sm:p-10 reader-surface backdrop-blur space-y-6">
            <div class="border-b border-slate-800/80 pb-6 mb-8 text-center space-y-2">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">{{ $novel->title }}</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $chapter->title }}</h1>
                <div class="text-xs text-slate-500">
                    Cập nhật: {{ $chapter->published_at?->diffForHumans() ?? 'Vừa xong' }} • {{ number_format($chapter->word_count) }} chữ
                </div>
            </div>

            <!-- Content with Paragraph IDs for 3D Intersection Observer -->
            <div id="reading-content-body" class="reading-content size-base font-novel space-y-6 leading-relaxed text-slate-200">
                @php
                    // Parse paragraphs and inject data-paragraph-index attribute
                    $content = $chapter->content;
                    if (str_contains($content, '<p>')) {
                        $parts = preg_split('/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
                        $index = 0;
                        $formattedHtml = '';
                        for ($i = 0; $i < count($parts); $i += 2) {
                            $segment = trim($parts[$i]);
                            if (!empty($segment)) {
                                // Add data-paragraph-index to opening <p>
                                if (preg_match('/^<p\b/i', $segment)) {
                                    $segment = preg_replace('/^<p/i', '<p data-paragraph-index="' . $index . '"', $segment);
                                } else {
                                    $segment = '<p data-paragraph-index="' . $index . '">' . $segment;
                                }
                                $closing = $parts[$i + 1] ?? '</p>';
                                $formattedHtml .= $segment . $closing . "\n";
                                $index++;
                            }
                        }
                        echo $formattedHtml;
                    } else {
                        // Plain text split by newlines
                        $lines = preg_split('/\r?\n/', $content);
                        $index = 0;
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line !== '') {
                                echo '<p data-paragraph-index="' . $index . '">' . e($line) . '</p>';
                                $index++;
                            }
                        }
                    }
                @endphp
            </div>

            <!-- Chapter Navigation Footer -->
            <div class="pt-10 border-t border-slate-800/80 flex items-center justify-between gap-4">
                @if ($previousChapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, (float) $previousChapter->chapter_number]) }}"
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-2">
                        &larr; Chương Trước
                    </a>
                @else
                    <span class="text-xs text-slate-600 cursor-not-allowed">Đã là chương đầu</span>
                @endif

                <a href="{{ route('reader.novels.show', $novel->slug) }}" class="text-xs text-slate-400 hover:text-white font-medium">
                    Mục Lục
                </a>

                @if ($nextChapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, (float) $nextChapter->chapter_number]) }}"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                        Chương Tiếp &rarr;
                    </a>
                @else
                    <span class="text-xs text-slate-600 cursor-not-allowed">Đã là chương mới nhất</span>
                @endif
            </div>
        </article>

        <!-- Column 2: Sticky 3D Hologram Projection Stage -->
        <aside class="lg:col-span-5 sticky top-24 space-y-4">
            <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 backdrop-blur shadow-2xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Đài Cụ Hóa 3D Trực Quan</h3>
                    </div>
                    <span id="hud-badge" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/20 text-indigo-300">
                        Sẵn Sàng
                    </span>
                </div>

                <!-- 3D Canvas Viewport (Defaults to pointer-events: none for touch scroll isolation) -->
                <div id="stage-container" class="stage-viewport w-full h-[380px] bg-slate-950/80 rounded-2xl border border-slate-800 relative overflow-hidden flex items-center justify-center">
                    <div id="stage-idle-notice" class="text-center p-6 text-slate-500 space-y-2">
                        <span class="text-3xl block">✨</span>
                        <p class="text-xs font-medium">Cuộn văn bản để kích hoạt mô hình 3D tương ứng</p>
                    </div>
                </div>

                <!-- HUD Hologram Title -->
                <div class="mt-4 pt-3 border-t border-slate-800 flex flex-col justify-between">
                    <h4 id="hud-hologram-title" class="font-bold text-white text-sm">Chưa có chiếu ảnh</h4>
                    <p id="hud-trigger-keyword" class="text-[11px] text-indigo-400 mt-0.5">Cuộn đến đoạn văn miêu tả để triệu hồi pháp bảo</p>
                </div>
            </div>
        </aside>
    </main>

    <!-- Embedded Projections Metadata -->
    <script id="projections-data" type="application/json">
        {!! json_encode($chapter->projections3d->map(function ($p) {
            return [
                'id' => $p->id,
                'paragraph_index' => $p->paragraph_index,
                'hologram_title' => $p->hologram_title,
                'trigger_keyword' => $p->trigger_keyword,
                'display_mode' => $p->display_mode,
                'auto_rotate' => $p->auto_rotate,
                'asset3d' => [
                    'name' => $p->asset3d->name ?? '',
                    'asset_type' => $p->asset3d->asset_type ?? '',
                    'model_file_url' => $p->asset3d->model_file_url ?? '',
                    'scale_factor' => (float) ($p->asset3d->scale_factor ?? 1.0),
                    'particle_effect' => $p->asset3d->particle_effect ?? 'none',
                ]
            ];
        }), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <!-- Reader Logic Engine -->
    <script type="module">
        import { ProjectionStage } from '/resources/js/3d-engine/projection-stage.js';
        import { Scroll3dObserver } from '/resources/js/scroll-3d-observer.js';

        const stageContainer = document.getElementById('stage-container');
        const projections = JSON.parse(document.getElementById('projections-data').textContent || '[]');

        // Initialize 3D stage
        const stage = new ProjectionStage(stageContainer, {
            interactive: false,
            autoRotate: true
        });
        window.activeStage = stage;

        // Initialize Scroll Observer
        const observer = new Scroll3dObserver(projections, stage, {
            title: document.getElementById('hud-hologram-title'),
            keyword: document.getElementById('hud-trigger-keyword'),
            badge: document.getElementById('hud-badge')
        });

        // Touch Isolation Toggle
        const toggleBtn = document.getElementById('toggle-stage-interactive');
        const btnLabel = document.getElementById('interactive-btn-label');
        let isInteractive = false;

        toggleBtn?.addEventListener('click', () => {
            isInteractive = !isInteractive;
            stageContainer.classList.toggle('is-interactive', isInteractive);
            stage.setInteractive(isInteractive);

            if (isInteractive) {
                btnLabel.textContent = 'Khóa Cuộn (Đang Xoay)';
                toggleBtn.classList.replace('text-indigo-400', 'text-amber-400');
            } else {
                btnLabel.textContent = 'Tương tác 360°';
                toggleBtn.classList.replace('text-amber-400', 'text-indigo-400');
            }
        });
    </script>

    <!-- Zen Toolbar Partial -->
    @include('reader.chapters.partials.zen_toolbar')

    <!-- Preferences Initialization -->
    <script type="module">
        import { ReaderPreferences } from '/resources/js/reader-preferences.js';
        new ReaderPreferences({
            apiEndpoint: "{{ route('reader.preferences.update') }}",
            csrfToken: "{{ csrf_token() }}"
        });
    </script>

    <!-- Reading Progress Tracker Initialization -->
    <script type="module">
        import { ReadingProgressTracker } from '/resources/js/reading-progress-tracker.js';
        new ReadingProgressTracker({{ $chapter->id }}, "{{ route('reader.progress.sync') }}", "{{ csrf_token() }}");
    </script>
</body>
</html>
