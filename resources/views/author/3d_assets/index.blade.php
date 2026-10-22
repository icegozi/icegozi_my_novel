<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho Mô Hình 3D - {{ $novel->title }} - Author Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen pb-16">
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-xl tracking-wider text-white">3D ASSET GALLERY</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('author.novels.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    &larr; Danh Sách Tác Phẩm
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 pt-10 space-y-8">
        @if (session('status'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-400 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Kho Mô Hình 3D Cụ Hóa: {{ $novel->title }}</h1>
                <p class="text-sm text-slate-400 mt-1">Quản lý và thiết lập mô hình 3D (.glb) để trình diễn tương tác trong các chương truyện</p>
            </div>
        </div>

        <!-- Upload New 3D Asset Section -->
        <div class="bg-slate-900/50 backdrop-blur border border-slate-800/80 rounded-3xl p-8">
            <h3 class="text-lg font-bold text-white mb-2">Tải lên mô hình 3D mới (.glb)</h3>
            <p class="text-xs text-slate-400 mb-6">Mô hình phải là định dạng nhị phân glTF (GLB) có header hợp lệ, dung lượng tối đa 50MB.</p>

            <form action="{{ route('author.novels.3d-assets.store', $novel) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Tên mô hình / Pháp bảo / Nhân vật *</label>
                        <input type="text" id="name" name="name" required value="{{ old('name') }}"
                            class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500"
                            placeholder="VD: Hiên Viên Kiếm, Hắc Long Vương, Chu Tước Đài...">
                        @error('name')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="asset_type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Phân loại tài sản 3D *</label>
                        <select id="asset_type" name="asset_type" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="weapon" {{ old('asset_type') == 'weapon' ? 'selected' : '' }}>Vũ Khí / Binh Khí (weapon)</option>
                            <option value="character" {{ old('asset_type') == 'character' ? 'selected' : '' }}>Nhân Vật (character)</option>
                            <option value="beast" {{ old('asset_type') == 'beast' ? 'selected' : '' }}>Thần Thú / Linh Thú (beast)</option>
                            <option value="artifact" {{ old('asset_type') == 'artifact' ? 'selected' : '' }}>Pháp Bảo / Cổ Vật (artifact)</option>
                            <option value="environment" {{ old('asset_type') == 'environment' ? 'selected' : '' }}>Cảnh Quan / Môi Trường (environment)</option>
                        </select>
                        @error('asset_type')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="particle_effect" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Hiệu ứng hạt hào quang (Particle Effect)</label>
                        <select id="particle_effect" name="particle_effect"
                            class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="none">Không có (Mặc định)</option>
                            <option value="divine_sparks" {{ old('particle_effect') == 'divine_sparks' ? 'selected' : '' }}>Tia Sáng Thần Thánh (divine_sparks)</option>
                            <option value="fireflies" {{ old('particle_effect') == 'fireflies' ? 'selected' : '' }}>Đom Đóm Tinh Linh (fireflies)</option>
                            <option value="snow" {{ old('particle_effect') == 'snow' ? 'selected' : '' }}>Băng Tuyết (snow)</option>
                            <option value="rain" {{ old('particle_effect') == 'rain' ? 'selected' : '' }}>Mưa Linh Khí (rain)</option>
                            <option value="sakura" {{ old('particle_effect') == 'sakura' ? 'selected' : '' }}>Cánh Hoa Đào (sakura)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="scale_factor" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Tỷ lệ phóng to (Scale)</label>
                            <input type="number" step="0.05" min="0.1" max="10.0" id="scale_factor" name="scale_factor" value="{{ old('scale_factor', 1.00) }}"
                                class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="ambient_light_color" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Màu ánh sáng nền</label>
                            <input type="color" id="ambient_light_color" name="ambient_light_color" value="{{ old('ambient_light_color', '#ffffff') }}"
                                class="w-full h-11 bg-slate-950 border border-slate-800 rounded-2xl p-1 cursor-pointer">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="model_file" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Tệp mô hình 3D (.glb) *</label>
                    <input type="file" id="model_file" name="model_file" accept=".glb" required
                        class="w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-400 hover:file:bg-indigo-600/30 cursor-pointer">
                    @error('model_file')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Mô tả / Xuất xứ</label>
                    <textarea id="description" name="description" rows="2"
                        class="w-full bg-slate-950 border border-slate-800 rounded-2xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500"
                        placeholder="Mô tả ngắn về nguồn gốc, thần uy của mô hình..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-indigo-500/25 transition">
                        Tải Lên Mô Hình 3D
                    </button>
                </div>
            </form>
        </div>

        <!-- Gallery of 3D Assets -->
        <div class="bg-slate-900/50 backdrop-blur border border-slate-800/80 rounded-3xl p-8">
            <h3 class="text-lg font-bold text-white mb-4">Danh mục mô hình đã tải lên ({{ $assets->count() }})</h3>

            @if ($assets->isEmpty())
                <div class="text-center py-12 text-slate-500">
                    <p class="text-sm">Chưa có mô hình 3D nào trong kho của tiểu thuyết này.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($assets as $asset)
                        <div class="p-6 rounded-3xl border border-slate-800 bg-slate-950/60 flex flex-col justify-between hover:border-indigo-500/50 transition">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-500/20 text-indigo-400 capitalize">
                                        {{ $asset->asset_type }}
                                    </span>
                                    @if ($asset->particle_effect !== 'none')
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-amber-500/20 text-amber-300">
                                            ✨ {{ $asset->particle_effect }}
                                        </span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-white text-base mb-1">{{ $asset->name }}</h4>
                                <p class="text-xs text-slate-400 mb-3 line-clamp-2">{{ $asset->description ?? 'Không có mô tả' }}</p>
                                <div class="text-xs text-slate-500 space-y-1 font-mono">
                                    <div>Tỷ lệ: {{ $asset->scale_factor }}x</div>
                                    <div class="truncate">Đường dẫn: {{ $asset->model_file_url }}</div>
                                </div>
                            </div>

                            <div class="pt-4 mt-4 border-t border-slate-800/80 flex justify-end">
                                <form action="{{ route('author.novels.3d-assets.destroy', [$novel, $asset]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa mô hình 3D này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-medium">
                                        Xóa mô hình
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</body>
</html>
