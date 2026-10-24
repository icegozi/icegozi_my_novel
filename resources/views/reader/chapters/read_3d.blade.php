<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $chapter->title }} - {{ $novel->title }} - Trình Đọc 3D</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-novel { font-family: 'Merriweather', Georgia, serif; }
        .font-heading { font-family: 'Cinzel', serif; }

        /* Sepia Theme Styles */
        .theme-sepia, body.sepia, html.sepia {
            background-color: #f5efe6 !important;
            color: #2d2216 !important;
        }
        .theme-sepia .reader-surface, body.sepia .reader-surface {
            background-color: #fdfbf7 !important;
            border-color: #e4d6bf !important;
            color: #2d2216 !important;
        }
        .theme-sepia .reading-content, body.sepia .reading-content {
            color: #2d2216 !important;
        }
        .theme-sepia header, body.sepia header {
            background-color: rgba(253, 251, 247, 0.95) !important;
            border-color: #e4d6bf !important;
            color: #2d2216 !important;
        }

        /* Light Theme Styles */
        body.light, html.light {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }
        body.light .reader-surface {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
        body.light .reading-content {
            color: #0f172a !important;
        }
        body.light header {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-color: #e2e8f0 !important;
            color: #0f172a !important;
        }
    </style>
    <!-- Three.js Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/DRACOLoader.js"></script>
    <!-- Reader Controls: reader-controls.js is bundled into app.js -->
</head>
<body class="theme-sepia sepia min-h-screen flex flex-col antialiased transition-colors">
    <!-- Top Progress Bar -->
    <div id="reading-progress-bar" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 z-50 transition-all duration-150" style="width: 0%;"></div>

    <!-- Top Nav Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/70 backdrop-blur-xl sticky top-0 z-40 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('reader.novels.show', $novel->slug) }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white text-xs shadow-lg shadow-indigo-500/25">3D</span>
                    <span class="text-xs font-bold uppercase tracking-wider hidden sm:inline">{{ $novel->title }}</span>
                </a>
            </div>

            <!-- Chapter selector & Title -->
            <div class="text-center truncate px-4 flex items-center gap-2">
                @if ($previousChapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, (float) $previousChapter->chapter_number]) }}" rel="prev" class="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white" title="Chương trước (Phím ←)">
                        ←
                    </a>
                @endif
                <span class="text-xs font-bold text-slate-200 block truncate">{{ $chapter->title }}</span>
                @if ($nextChapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, (float) $nextChapter->chapter_number]) }}" rel="next" class="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white" title="Chương sau (Phím →)">
                        →
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <!-- TOC Drawer Trigger -->
                <button
                    type="button"
                    onclick="document.getElementById('toc-drawer').classList.toggle('hidden')"
                    class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 border border-slate-700 flex items-center gap-1.5 transition"
                    aria-label="Xem mục lục toàn bộ chương"
                >
                    <span aria-hidden="true">📑</span>
                    <span class="hidden md:inline">Mục Lục</span>
                </button>

                <!-- Reader Display Settings Trigger -->
                <button
                    type="button"
                    onclick="document.getElementById('settings-drawer').classList.toggle('hidden')"
                    class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 border border-slate-700 flex items-center gap-1.5 transition"
                    aria-label="Aa Cỡ Chữ - Cài đặt giao diện đọc"
                >
                    <span>Aa</span>
                    <span class="hidden md:inline">Cỡ Chữ</span>
                </button>

                @if ($novel->assets3d->isNotEmpty())
                    <button type="button" id="toggle-stage-interactive"
                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-indigo-400 border border-slate-700 flex items-center gap-1.5 transition">
                        <span aria-hidden="true">🔄</span>
                        <span id="interactive-btn-label">Tương tác 360°</span>
                    </button>
                @endif
            </div>
        </div>
    </header>

    <!-- Table of Contents Slide-Out Drawer -->
    <div id="toc-drawer" role="dialog" aria-modal="true" aria-label="Mục Lục Tác Phẩm" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex justify-start overscroll-contain">
        <div class="w-80 sm:w-96 bg-slate-900 border-r border-slate-800 h-full p-6 flex flex-col justify-between shadow-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-sm text-white">Mục Lục Tác Phẩm</h3>
                    <button type="button" onclick="document.getElementById('toc-drawer').classList.add('hidden')" class="p-1 text-slate-400 hover:text-white" aria-label="Đóng mục lục">✕</button>
                </div>
                <div class="overflow-y-auto max-h-[calc(100vh-140px)] space-y-1 text-xs">
                    @if (isset($allChapters))
                        @foreach ($allChapters as $ch)
                            <a
                                href="{{ route('reader.chapters.read', [$novel->slug, (float) $ch->chapter_number]) }}"
                                class="block p-3 rounded-xl transition {{ $ch->id === $chapter->id ? 'bg-indigo-600 text-white font-bold' : 'text-slate-300 hover:bg-slate-800' }}"
                            >
                                {{ $ch->title }}
                            </a>
                        @endforeach
                    @else
                        <a href="{{ route('reader.novels.show', $novel->slug) }}" class="text-indigo-400 hover:underline">
                            Xem trên trang chi tiết →
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reading Display Settings Slide-Out Drawer -->
    <div id="settings-drawer" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex justify-end">
        <div class="w-80 bg-slate-900 border-l border-slate-800 h-full p-6 flex flex-col justify-between shadow-2xl text-xs space-y-6">
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-sm text-white">Tùy Chỉnh Giao Diện Đọc</h3>
                    <button type="button" onclick="document.getElementById('settings-drawer').classList.add('hidden')" class="p-1 text-slate-400 hover:text-white" aria-label="Đóng cài đặt">✕</button>
                </div>

                <!-- Theme selector -->
                <div class="space-y-2">
                    <span class="font-semibold text-slate-400 block">Chủ Đề Nền:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="document.body.className='theme-sepia sepia'; window.setReaderTheme('sepia')" class="p-2.5 rounded-xl bg-[#f5efe6] border border-amber-300 text-amber-950 font-bold text-center">Màu Vàng (Giấy Cổ)</button>
                        <button type="button" onclick="document.body.className='light'; window.setReaderTheme('light')" class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 font-bold text-center">Sáng</button>
                    </div>
                </div>

                <!-- Font Size Controls -->
                <div class="space-y-2">
                    <span class="font-semibold text-slate-400 block">Cỡ Chữ:</span>
                    <div class="flex items-center justify-between bg-slate-950 p-2 rounded-xl border border-slate-800">
                        <button type="button" onclick="window.adjustFontSize(-2)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold">A-</button>
                        <span id="font-size-display-label" class="font-mono text-slate-300 font-bold">18px</span>
                        <button type="button" onclick="window.adjustFontSize(2)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold">A+</button>
                    </div>
                </div>

                <!-- Font Family -->
                <div class="space-y-2">
                    <span class="font-semibold text-slate-400 block">Kiểu Chữ:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="window.setFontFamily('serif')" class="p-2.5 rounded-xl bg-slate-800 text-white font-novel font-bold text-center hover:bg-slate-700">Có Chân (Serif)</button>
                        <button type="button" onclick="window.setFontFamily('sans')" class="p-2.5 rounded-xl bg-slate-800 text-white font-sans font-bold text-center hover:bg-slate-700">Không Chân</button>
                    </div>
                </div>

                <!-- Column Width -->
                <div class="space-y-2">
                    <span class="font-semibold text-slate-400 block">Độ Rộng Khung Đọc:</span>
                    <div class="grid grid-cols-3 gap-1.5 text-[11px]">
                        <button type="button" onclick="window.setContentWidth('max-w-xl')" class="p-2 rounded-xl bg-slate-800 text-slate-200 text-center hover:bg-slate-700">Gọn</button>
                        <button type="button" onclick="window.setContentWidth('max-w-2xl')" class="p-2 rounded-xl bg-slate-800 text-slate-200 text-center hover:bg-slate-700">Chuẩn</button>
                        <button type="button" onclick="window.setContentWidth('max-w-4xl')" class="p-2 rounded-xl bg-slate-800 text-slate-200 text-center hover:bg-slate-700">Rộng</button>
                    </div>
                </div>
            </div>

            <div class="text-[11px] text-slate-500 text-center pt-4 border-t border-slate-800">
                Phím tắt: ← Chương trước • → Chương tiếp • T Đổi nền
            </div>
        </div>
    </div>

    <!-- Reading & 3D Stage Layout -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 grid grid-cols-1 {{ $novel->assets3d->isNotEmpty() ? 'lg:grid-cols-12 gap-8' : '' }} items-start">
        <!-- Column 1: Reading Text or Comic Stream -->
        <article class="{{ $novel->assets3d->isNotEmpty() ? 'lg:col-span-7' : 'max-w-3xl mx-auto w-full' }} bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 sm:p-10 reader-surface backdrop-blur space-y-6">
            <div class="border-b border-slate-800/80 pb-6 mb-8 text-center space-y-2">
                <a href="{{ route('reader.novels.show', $novel->slug) }}" class="text-xs font-bold text-indigo-400 uppercase tracking-widest hover:underline block">
                    {{ $novel->title }}
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $chapter->title }}</h1>
                <div class="text-xs text-slate-500 font-mono tabular-nums">
                    Cập nhật: {{ $chapter->published_at?->diffForHumans() ?? 'Vừa xong' }} • {{ number_format($chapter->word_count) }} chữ
                </div>
            </div>

            <!-- Content Container -->
            <div id="reading-container" class="max-w-2xl mx-auto">
                <div id="reading-content-body" class="reading-content size-base font-novel space-y-6 leading-relaxed text-slate-200 text-base">
                    @php
                        $content = $chapter->content;
                        if (str_contains($content, 'comic-stream') || str_contains($content, '<img')) {
                            // Render comic stream directly
                            echo $content;
                        } elseif (str_contains($content, '<p>')) {
                            $parts = preg_split('/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
                            $index = 0;
                            $formattedHtml = '';
                            for ($i = 0; $i < count($parts); $i += 2) {
                                $segment = trim($parts[$i]);
                                if (!empty($segment)) {
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
            </div>

            <!-- Chapter Navigation Footer -->
            <div class="pt-10 border-t border-slate-800/80 flex items-center justify-between gap-4">
                @if ($previousChapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, (float) $previousChapter->chapter_number]) }}"
                        rel="prev"
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-2">
                        &larr; Chương Trước
                    </a>
                @else
                    <span class="text-xs text-slate-600 cursor-not-allowed">Đã là chương đầu</span>
                @endif

                <a href="{{ route('reader.novels.show', $novel->slug) }}" class="text-xs text-slate-400 hover:text-white font-medium">
                    Mục Lục Tác Phẩm
                </a>

                @if ($nextChapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, (float) $nextChapter->chapter_number]) }}"
                        rel="next"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                        Chương Tiếp &rarr;
                    </a>
                @else
                    <span class="text-xs text-slate-600 cursor-not-allowed">Đã là chương mới nhất</span>
                @endif
            </div>
        </article>

        @if ($novel->assets3d->isNotEmpty())
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

                    <!-- 3D Canvas Viewport -->
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
        @endif
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

    @if ($novel->assets3d->isNotEmpty())
        <!-- Reader 3D Logic Engine -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const { ProjectionStage, Scroll3dObserver } = window.Novel3D || {};
                const stageContainer = document.getElementById('stage-container');
                const projections = JSON.parse(document.getElementById('projections-data')?.textContent || '[]');

                if (stageContainer && ProjectionStage) {
                    const stage = new ProjectionStage(stageContainer, {
                        interactive: false,
                        autoRotate: true
                    });
                    window.activeStage = stage;

                    if (Scroll3dObserver) {
                        new Scroll3dObserver(projections, stage, {
                            title: document.getElementById('hud-hologram-title'),
                            keyword: document.getElementById('hud-trigger-keyword'),
                            badge: document.getElementById('hud-badge')
                        });
                    }

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
                }
            });
        </script>
    @endif

    <!-- Zen Toolbar Partial -->
    @include('reader.chapters.partials.zen_toolbar')

    <!-- Preferences & Progress Tracker Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const { ReaderPreferences, ReadingProgressTracker } = window.Novel3D || {};
            if (ReaderPreferences) {
                window.Novel3D = window.Novel3D || {};
                window.Novel3D.activePreferences = new ReaderPreferences({
                    apiEndpoint: "{{ route('reader.preferences.update') }}",
                    csrfToken: "{{ csrf_token() }}"
                });
            }
            if (ReadingProgressTracker) {
                new ReadingProgressTracker({{ $chapter->id }}, "{{ route('reader.progress.sync') }}", "{{ csrf_token() }}");
            }
        });
    </script>
</body>
</html>
