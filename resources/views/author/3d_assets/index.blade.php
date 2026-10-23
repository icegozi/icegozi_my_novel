@extends('layouts.reader', [
    'title' => 'Kho Mô Hình 3D - ' . $novel->title . ' - Novel Engine',
    'metaDescription' => 'Quản lý tài sản 3D và mô hình cụ hóa tương tác cho tiểu thuyết.'
])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 space-y-8">
    @if (session('status'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm font-medium">
            {{ session('status') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('author.novels.index') }}" class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text flex items-center gap-1 transition-colors">
                    <span aria-hidden="true">&larr;</span> Danh Sách Tác Phẩm
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
                Kho Mô Hình 3D Cụ Hóa: {{ $novel->title }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 sepia:text-sepia-muted mt-1">
                Quản lý và thiết lập mô hình 3D (.glb) để trình diễn tương tác trong các chương truyện
            </p>
        </div>
    </div>

    <!-- Upload New 3D Asset Section -->
    <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 transition-colors">
        <h2 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text mb-1">
            Tải lên mô hình 3D mới (.glb)
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted mb-6">
            Mô hình phải là định dạng nhị phân glTF (GLB) có header hợp lệ, dung lượng tối đa 50MB.
        </p>

        <form action="{{ route('author.novels.3d-assets.store', $novel) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                        Tên mô hình / Pháp bảo / Nhân vật <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                        class="w-full bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800"
                        placeholder="VD: Hiên Viên Kiếm, Hắc Long Vương, Chu Tước Đài...">
                    @error('name')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="asset_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                        Phân loại tài sản 3D <span class="text-rose-500">*</span>
                    </label>
                    <select id="asset_type" name="asset_type" required
                        class="w-full bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800">
                        <option value="weapon" {{ old('asset_type') == 'weapon' ? 'selected' : '' }}>Vũ Khí / Binh Khí (weapon)</option>
                        <option value="character" {{ old('asset_type') == 'character' ? 'selected' : '' }}>Nhân Vật (character)</option>
                        <option value="beast" {{ old('asset_type') == 'beast' ? 'selected' : '' }}>Thần Thú / Linh Thú (beast)</option>
                        <option value="artifact" {{ old('asset_type') == 'artifact' ? 'selected' : '' }}>Pháp Bảo / Cổ Vật (artifact)</option>
                        <option value="environment" {{ old('asset_type') == 'environment' ? 'selected' : '' }}>Cảnh Quan / Môi Trường (environment)</option>
                    </select>
                    @error('asset_type')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="particle_effect" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                        Hiệu ứng hạt hào quang (Particle Effect)
                    </label>
                    <select id="particle_effect" name="particle_effect"
                        class="w-full bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800">
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
                        <label for="scale_factor" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                            Tỷ lệ phóng to (Scale)
                        </label>
                        <input type="number" step="0.05" min="0.1" max="10.0" id="scale_factor" name="scale_factor" value="{{ old('scale_factor', 1.00) }}"
                            class="w-full bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800">
                    </div>
                    <div>
                        <label for="ambient_light_color" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                            Màu ánh sáng nền
                        </label>
                        <input type="color" id="ambient_light_color" name="ambient_light_color" value="{{ old('ambient_light_color', '#ffffff') }}"
                            class="w-full h-10 bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-xl p-1 cursor-pointer">
                    </div>
                </div>
            </div>

            <div>
                <label for="model_file" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                    Tệp mô hình 3D (.glb) <span class="text-rose-500">*</span>
                </label>
                <input type="file" id="model_file" name="model_file" accept=".glb" required
                    class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 dark:file:bg-indigo-500/20 sepia:file:bg-amber-900/20 file:text-indigo-700 dark:file:text-indigo-400 sepia:file:text-amber-900 hover:file:opacity-80 cursor-pointer">
                @error('model_file')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                    Mô tả / Xuất xứ
                </label>
                <textarea id="description" name="description" rows="2"
                    class="w-full bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-xl px-4 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800"
                    placeholder="Mô tả ngắn về nguồn gốc, thần uy của mô hình..."></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md sepia:shadow-amber-900/20 transition">
                    Tải Lên Mô Hình 3D
                </button>
            </div>
        </form>
    </div>

    <!-- Gallery of 3D Assets -->
    <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 transition-colors">
        <h2 class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text mb-4">
            Danh mục mô hình đã tải lên ({{ $assets->count() }})
        </h2>

        @if ($assets->isEmpty())
            <div class="text-center py-12 text-slate-500 dark:text-slate-400 sepia:text-sepia-muted">
                <p class="text-xs sm:text-sm">Chưa có mô hình 3D nào trong kho của tiểu thuyết này.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($assets as $asset)
                    <div class="p-5 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-950/60 sepia:bg-sepia-100/60 flex flex-col justify-between hover:border-indigo-500/50 sepia:hover:border-amber-800/50 transition">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 text-[11px] font-semibold rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 capitalize border border-indigo-500/20 sepia:border-amber-800/30">
                                    {{ $asset->asset_type }}
                                </span>
                                @if ($asset->particle_effect !== 'none')
                                    <span class="px-2.5 py-1 text-[11px] font-medium rounded-full bg-amber-500/10 text-amber-800 dark:text-amber-300 sepia:text-amber-900 border border-amber-500/20">
                                        ✨ {{ $asset->particle_effect }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text text-base mb-1">{{ $asset->name }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted mb-3 line-clamp-2">{{ $asset->description ?? 'Không có mô tả' }}</p>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 sepia:text-sepia-muted space-y-1 font-mono">
                                <div>Tỷ lệ: {{ $asset->scale_factor }}x</div>
                                <div class="truncate">Đường dẫn: {{ $asset->model_file_url }}</div>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 sepia:border-sepia-border/60 flex justify-end">
                            <form action="{{ route('author.novels.3d-assets.destroy', [$novel, $asset]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa mô hình 3D này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 dark:text-rose-400 hover:text-rose-700 font-semibold">
                                    Xóa mô hình
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
