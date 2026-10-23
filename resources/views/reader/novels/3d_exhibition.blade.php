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
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col">
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
            <a href="{{ route('reader.novels.show', $novel->slug) }}" class="text-xs font-semibold text-slate-400 hover:text-white transition">
                &larr; Về Tác Phẩm
            </a>
        </div>
    </header>

    <!-- Main Exhibition Canvas & Stage -->
    <div class="flex-1 max-w-7xl w-full mx-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- 3D Viewport -->
        <div class="lg:col-span-8 bg-slate-900/40 border border-slate-800/80 rounded-3xl relative overflow-hidden flex flex-col items-center justify-center min-h-[450px]">
            <canvas id="exhibition-canvas" class="w-full h-full absolute inset-0"></canvas>
            
            <div id="loading-overlay" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6 z-10 transition-opacity duration-300">
                <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-sm font-semibold text-slate-200">Đang khởi tạo đài cụ hóa 3D...</p>
                <p class="text-xs text-slate-500 mt-1">Sử dụng công nghệ Three.js WebGL & Bộ giải nén DRACO</p>
            </div>

            <!-- Stage Controls -->
            <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between pointer-events-auto z-20">
                <div class="bg-slate-900/80 backdrop-blur border border-slate-800 px-4 py-2 rounded-2xl text-xs text-slate-300 flex items-center gap-3">
                    <span id="current-model-name" class="font-bold text-white">Chưa chọn mô hình</span>
                    <span>•</span>
                    <span class="text-indigo-400">Kéo chuột/chạm để xoay 360°</span>
                </div>
            </div>
        </div>

        <!-- Asset Selector & Details -->
        <div class="lg:col-span-4 bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 flex flex-col justify-between space-y-6">
            <div>
                <h2 class="text-base font-bold text-white mb-1">Danh Mục Bảo Vật & Nhân Vật</h2>
                <p class="text-xs text-slate-400 mb-4">Nhấp vào danh sách để đưa mô hình lên đài trình chiếu</p>

                <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                    @forelse ($novel->assets3d as $asset)
                        <button type="button"
                            class="w-full text-left p-4 rounded-2xl border border-slate-800 bg-slate-950/60 hover:border-indigo-500/50 hover:bg-slate-900 transition flex items-center justify-between group"
                            onclick="selectModel('{{ $asset->name }}', '{{ $asset->model_file_url }}', {{ $asset->scale_factor }}, '{{ $asset->particle_effect }}', '{{ $asset->description }}')">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/20 text-indigo-300 capitalize">
                                        {{ $asset->asset_type }}
                                    </span>
                                    @if ($asset->particle_effect !== 'none')
                                        <span class="text-[10px] text-amber-300 font-mono">✨ {{ $asset->particle_effect }}</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-white text-xs group-hover:text-indigo-300 transition">{{ $asset->name }}</h3>
                            </div>
                            <span class="text-xs text-slate-500 group-hover:text-white">&rarr;</span>
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
                <h4 class="font-bold text-white uppercase tracking-wider text-[11px]">Thông Tin Chi Tiết</h4>
                <p id="model-description-text" class="text-slate-400 leading-relaxed">
                    Chọn một mô hình từ danh sách phía trên để xem miêu tả pháp bảo/nhân vật.
                </p>
            </div>
        </div>
    </div>

    <script>
        function selectModel(name, url, scale, particle, description) {
            document.getElementById('current-model-name').textContent = name;
            document.getElementById('model-description-text').textContent = description || 'Không có mô tả chi tiết.';
            
            // Trigger 3D engine model load if initialized
            if (window.activeStage) {
                window.activeStage.loadModel(url, scale, particle);
            }
        }

        // Hide loader after mount
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const overlay = document.getElementById('loading-overlay');
                if (overlay) overlay.style.opacity = '0';
                setTimeout(() => overlay?.remove(), 300);
            }, 600);
        });
    </script>
</body>
</html>
