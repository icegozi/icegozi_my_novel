<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phòng Trưng Bày 3D Cụ Hóa - {{ $novel->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
    <!-- Three.js Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/DRACOLoader.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased">
    <!-- Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <div>
                    <h1 class="font-heading font-bold text-sm tracking-wider text-white">PHÒNG TRƯNG BÀY 3D CỤ HÓA</h1>
                    <p class="text-[10px] text-slate-400 truncate max-w-xs">{{ $novel->title }}</p>
                </div>
            </div>
            <a href="{{ route('reader.novels.show', $novel->slug) }}" class="text-xs font-semibold text-slate-400 hover:text-white transition-colors flex items-center gap-1.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg px-2 py-1">
                <span>&larr;</span>
                <span>Về Tác Phẩm</span>
            </a>
        </div>
    </header>

    <!-- Main Exhibition Canvas & Stage -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- 3D Viewport -->
        <div class="lg:col-span-8 bg-slate-900/40 border border-slate-800/80 rounded-3xl relative overflow-hidden flex flex-col items-center justify-center min-h-[460px] shadow-2xl">
            <!-- WebGL Container for ProjectionStage -->
            <div id="stage-container" class="stage-viewport w-full h-full absolute inset-0 flex items-center justify-center"></div>
            
            <div id="loading-overlay" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6 z-10 transition-opacity duration-300">
                <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-sm font-semibold text-slate-200">Đang khởi tạo đài cụ hóa 3D...</p>
                <p class="text-xs text-slate-500 mt-1">Sử dụng công nghệ Three.js WebGL & Bộ giải nén DRACO</p>
            </div>

            <!-- Stage Controls & HUD -->
            <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between pointer-events-auto z-20">
                <div class="bg-slate-900/85 backdrop-blur-md border border-slate-800/90 px-4 py-2.5 rounded-2xl text-xs text-slate-300 flex items-center gap-3 shadow-xl">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" aria-hidden="true"></span>
                    <span id="current-model-name" class="font-bold text-white">Chưa chọn mô hình</span>
                    <span class="text-slate-600">•</span>
                    <span class="text-indigo-400 hidden sm:inline">Kéo chuột/chạm để xoay 360°</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="toggle-autorotate-btn"
                        class="px-3 py-2 rounded-2xl bg-slate-900/85 backdrop-blur-md border border-slate-800/90 hover:bg-slate-800 text-xs font-semibold text-slate-300 hover:text-white transition-colors flex items-center gap-1.5 shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        <span aria-hidden="true">🔄</span>
                        <span id="autorotate-label">Dừng xoay</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Asset Selector & Details -->
        <div class="lg:col-span-4 bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 flex flex-col justify-between space-y-6 shadow-xl">
            <div>
                <h2 class="text-base font-bold text-white mb-1">Danh Mục Bảo Vật & Nhân Vật</h2>
                <p class="text-xs text-slate-400 mb-4">Nhấp vào danh sách để đưa mô hình lên đài trình chiếu</p>

                <div id="assets-list" class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
                    @forelse ($novel->assets3d as $asset)
                        @php
                            $modelUrl = \Illuminate\Support\Str::startsWith($asset->model_file_url, ['http://', 'https://']) ? $asset->model_file_url : asset($asset->model_file_url);
                        @endphp
                        <button type="button"
                            id="asset-btn-{{ $asset->id }}"
                            class="asset-card-btn w-full text-left p-4 rounded-2xl border border-slate-800 bg-slate-950/60 hover:border-indigo-500/50 hover:bg-slate-900 transition-colors flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                            data-asset-id="{{ $asset->id }}"
                            onclick="selectModel('{{ e($asset->name) }}', '{{ $modelUrl }}', {{ (float) $asset->scale_factor }}, '{{ $asset->particle_effect }}', '{{ e($asset->description) }}', {{ $asset->id }})">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/20 text-indigo-300 capitalize">
                                        {{ $asset->asset_type }}
                                    </span>
                                    @if ($asset->particle_effect !== 'none')
                                        <span class="text-[10px] text-amber-300 font-mono">✨ {{ $asset->particle_effect }}</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-white text-xs group-hover:text-indigo-300 transition-colors">{{ $asset->name }}</h3>
                            </div>
                            <span class="text-xs text-slate-500 group-hover:text-white transition-colors" aria-hidden="true">&rarr;</span>
                        </button>
                    @empty
                        <div class="text-center py-8 text-slate-500 text-xs">
                            Tiểu thuyết này chưa có mô hình 3D nào.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Description Card -->
            <div id="model-detail-card" class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/80 text-xs space-y-2">
                <h3 class="font-bold text-white uppercase tracking-wider text-[11px]">Thông Tin Chi Tiết</h3>
                <p id="model-description-text" class="text-slate-400 leading-relaxed">
                    Chọn một mô hình từ danh sách phía trên để xem miêu tả pháp bảo/nhân vật.
                </p>
            </div>
        </div>
    </main>

    <script>
        let autoRotateActive = true;

        function selectModel(name, url, scale, particle, description, assetId) {
            const nameEl = document.getElementById('current-model-name');
            const descEl = document.getElementById('model-description-text');
            if (nameEl) nameEl.textContent = name;
            if (descEl) descEl.textContent = description || 'Không có mô tả chi tiết.';

            // Highlight active button
            document.querySelectorAll('.asset-card-btn').forEach(btn => {
                btn.classList.remove('border-indigo-500', 'bg-indigo-950/40', 'ring-1', 'ring-indigo-500');
            });
            if (assetId) {
                const activeBtn = document.getElementById(`asset-btn-${assetId}`);
                if (activeBtn) {
                    activeBtn.classList.add('border-indigo-500', 'bg-indigo-950/40', 'ring-1', 'ring-indigo-500');
                }
            }

            // Trigger 3D engine model load if initialized
            if (window.activeStage) {
                window.activeStage.loadModel(url, scale, particle);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const { ProjectionStage } = window.Novel3D || {};
            const stageContainer = document.getElementById('stage-container');

            if (stageContainer && ProjectionStage) {
                const stage = new ProjectionStage(stageContainer, {
                    interactive: true,
                    autoRotate: true
                });
                window.activeStage = stage;
                stage.setInteractive(true);

                // Auto-rotate toggle handler
                const autoRotateBtn = document.getElementById('toggle-autorotate-btn');
                const autoRotateLabel = document.getElementById('autorotate-label');
                if (autoRotateBtn) {
                    autoRotateBtn.addEventListener('click', () => {
                        autoRotateActive = !autoRotateActive;
                        stage.options.autoRotate = autoRotateActive;
                        if (stage.controls) {
                            stage.controls.autoRotate = autoRotateActive;
                        }
                        autoRotateLabel.textContent = autoRotateActive ? 'Dừng xoay' : 'Tự động xoay';
                    });
                }
            }

            // Hide loader after mount
            setTimeout(() => {
                const overlay = document.getElementById('loading-overlay');
                if (overlay) {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 300);
                }
            }, 600);

            // Auto-load first asset if available
            @if ($novel->assets3d->isNotEmpty())
                @php
                    $firstAsset = $novel->assets3d->first();
                    $firstModelUrl = \Illuminate\Support\Str::startsWith($firstAsset->model_file_url, ['http://', 'https://']) ? $firstAsset->model_file_url : asset($firstAsset->model_file_url);
                @endphp
                selectModel(
                    @json($firstAsset->name),
                    @json($firstModelUrl),
                    {{ (float) $firstAsset->scale_factor }},
                    @json($firstAsset->particle_effect),
                    @json($firstAsset->description),
                    {{ $firstAsset->id }}
                );
            @endif
        });
    </script>
</body>
</html>
