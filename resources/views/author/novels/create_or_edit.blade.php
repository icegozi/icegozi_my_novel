<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $novel->exists ? 'Chỉnh Sửa Tác Phẩm' : 'Tạo Tác Phẩm Mới' }} - Author Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen">
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/tac-gia/truyen" class="text-xs text-slate-400 hover:text-indigo-400 flex items-center gap-1.5 transition-colors">
                &larr; Quay lại danh sách
            </a>
            <span class="font-heading font-bold text-lg text-white">AUTHOR STUDIO</span>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-white">
                {{ $novel->exists ? 'Chỉnh Sửa Thông Tin Tác Phẩm' : 'Khai Báo & Tạo Tác Phẩm Mới' }}
            </h1>
            <p class="text-slate-400 text-sm mt-1">Tuân thủ nghiêm ngặt quy định gắn nhãn độ tuổi và quyền sở hữu trí tuệ số.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm">
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $novel->exists ? '/tac-gia/truyen/' . $novel->id : '/tac-gia/truyen' }}" method="POST" enctype="multipart/form-data" class="bg-slate-900/40 p-8 rounded-2xl border border-slate-800 space-y-6">
            @csrf
            @if($novel->exists)
                @method('PUT')
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tên tác phẩm <span class="text-rose-400">*</span></label>
                <input type="text" name="title" value="{{ old('title', $novel->title) }}" required placeholder="VD: Vũ Động Càn Khôn 3D" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tóm tắt tác phẩm (Tối thiểu 20 ký tự) <span class="text-rose-400">*</span></label>
                <textarea name="synopsis" rows="4" required placeholder="Giới thiệu cốt truyện lôi cuốn, thế giới quan, nhân vật chính..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">{{ old('synopsis', $novel->synopsis) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phân loại nhãn độ tuổi <span class="text-rose-400">*</span></label>
                    <select name="age_rating" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        <option value="all" {{ old('age_rating', $novel->age_rating) === 'all' ? 'selected' : '' }}>ALL - Mọi lứa tuổi</option>
                        <option value="13_plus" {{ old('age_rating', $novel->age_rating) === '13_plus' ? 'selected' : '' }}>13+ (Tuổi thiếu niên)</option>
                        <option value="16_plus" {{ old('age_rating', $novel->age_rating) === '16_plus' ? 'selected' : '' }}>16+ (Hành động kịch tính)</option>
                        <option value="18_plus" {{ old('age_rating', $novel->age_rating) === '18_plus' ? 'selected' : '' }}>18+ (Cân nhắc kỹ trước khi đọc)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nguồn gốc tác quyền <span class="text-rose-400">*</span></label>
                    <select name="copyright_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        <option value="original" {{ old('copyright_type', $novel->copyright_type) === 'original' ? 'selected' : '' }}>Tác phẩm nguyên tác (Tự sáng tác)</option>
                        <option value="authorized_translation" {{ old('copyright_type', $novel->copyright_type) === 'authorized_translation' ? 'selected' : '' }}>Dịch thuật có ủy quyền bản quyền</option>
                        <option value="public_domain" {{ old('copyright_type', $novel->copyright_type) === 'public_domain' ? 'selected' : '' }}>Tác phẩm thuộc phạm vi công cộng</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Thể loại (Chọn ít nhất 1) <span class="text-rose-400">*</span></label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @php $selectedGenres = old('genres', $novel->genres->pluck('id')->toArray()); @endphp
                    @foreach($genres as $genre)
                        <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-800/50 border border-slate-700/60 cursor-pointer hover:border-indigo-500/50 transition-colors">
                            <input type="checkbox" name="genres[]" value="{{ $genre->id }}" {{ in_array($genre->id, $selectedGenres) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700">
                            <span class="text-xs text-slate-200">{{ $genre->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ảnh bìa tác phẩm (WebP / PNG / JPG)</label>
                <input type="file" name="cover_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-400 hover:file:bg-indigo-600 hover:file:text-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ghi chú giấy phép / Giấy ủy quyền bản quyền (nếu có)</label>
                <textarea name="copyright_license_notes" rows="2" placeholder="Ghi rõ nguồn, số hợp đồng chuyển nhượng quyền tác giả..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">{{ old('copyright_license_notes', $novel->copyright_license_notes) }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="/tac-gia/truyen" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    Hủy Bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-500/20 transition-all">
                    {{ $novel->exists ? 'Cập Nhật Tác Phẩm' : 'Tạo Tác Phẩm & Lưu Nháp' }}
                </button>
            </div>
        </form>
    </main>
</body>
</html>
