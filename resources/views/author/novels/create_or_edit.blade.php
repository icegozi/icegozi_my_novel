@extends('layouts.reader', [
    'title' => ($novel->exists ? 'Chỉnh Sửa Tác Phẩm' : 'Tạo Tác Phẩm Mới') . ' - Studio Tác Giả - Novel Engine',
    'metaDescription' => 'Khai báo thông tin tác phẩm, thiết lập tác quyền và kiểm duyệt bản thảo.'
])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
    <!-- Header Banner -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <a href="/tac-gia/truyen" class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text flex items-center gap-1 transition-colors">
                <span aria-hidden="true">&larr;</span> Quay lại danh sách truyện
            </a>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
            {{ $novel->exists ? 'Chỉnh Sửa Thông Tin Tác Phẩm' : 'Khai Báo & Tạo Tác Phẩm Mới' }}
        </h1>
        <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs sm:text-sm mt-1 leading-relaxed">
            Tuân thủ nghiêm ngặt quy định gắn nhãn độ tuổi và quyền sở hữu trí tuệ số.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 text-xs sm:text-sm">
            <h4 class="font-bold text-rose-900 dark:text-rose-300 flex items-center gap-2 mb-2">
                <span aria-hidden="true">❌</span> Vui lòng kiểm tra lại các thông tin sau:
            </h4>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $novel->exists ? '/tac-gia/truyen/' . $novel->id : '/tac-gia/truyen' }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 space-y-6 transition-colors">
        @csrf
        @if($novel->exists)
            @method('PUT')
        @endif

        <div>
            <label for="title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                Tên tác phẩm <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="title" name="title" value="{{ old('title', $novel->title) }}" required placeholder="VD: Vũ Động Càn Khôn 3D…" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
        </div>

        <div>
            <label for="synopsis" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                Tóm tắt tác phẩm (Tối thiểu 20 ký tự) <span class="text-rose-500">*</span>
            </label>
            <textarea id="synopsis" name="synopsis" rows="4" required placeholder="Giới thiệu cốt truyện lôi cuốn, thế giới quan, nhân vật chính…" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 leading-relaxed transition-colors">{{ old('synopsis', $novel->synopsis) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="age_rating" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                    Phân loại nhãn độ tuổi <span class="text-rose-500">*</span>
                </label>
                <select id="age_rating" name="age_rating" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                    <option value="all" {{ old('age_rating', $novel->age_rating) === 'all' ? 'selected' : '' }}>ALL - Mọi lứa tuổi</option>
                    <option value="13_plus" {{ old('age_rating', $novel->age_rating) === '13_plus' ? 'selected' : '' }}>13+ (Tuổi thiếu niên)</option>
                    <option value="16_plus" {{ old('age_rating', $novel->age_rating) === '16_plus' ? 'selected' : '' }}>16+ (Hành động kịch tính)</option>
                    <option value="18_plus" {{ old('age_rating', $novel->age_rating) === '18_plus' ? 'selected' : '' }}>18+ (Cân nhắc kỹ trước khi đọc)</option>
                </select>
            </div>
            <div>
                <label for="copyright_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                    Nguồn gốc tác quyền <span class="text-rose-500">*</span>
                </label>
                <select id="copyright_type" name="copyright_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                    <option value="original" {{ old('copyright_type', $novel->copyright_type) === 'original' ? 'selected' : '' }}>Tác phẩm nguyên tác (Tự sáng tác)</option>
                    <option value="authorized_translation" {{ old('copyright_type', $novel->copyright_type) === 'authorized_translation' ? 'selected' : '' }}>Dịch thuật có ủy quyền bản quyền</option>
                    <option value="public_domain" {{ old('copyright_type', $novel->copyright_type) === 'public_domain' ? 'selected' : '' }}>Tác phẩm thuộc phạm vi công cộng</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-2">
                Thể loại (Chọn ít nhất 1) <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
                @php $selectedGenres = old('genres', $novel->genres->pluck('id')->toArray()); @endphp
                @foreach($genres as $genre)
                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border cursor-pointer hover:border-indigo-500/50 sepia:hover:border-amber-800/50 transition-colors">
                        <input type="checkbox" name="genres[]" value="{{ $genre->id }}" {{ in_array($genre->id, $selectedGenres) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 sepia:text-amber-800 focus:ring-indigo-500 bg-white dark:bg-slate-900 sepia:bg-sepia-card border-slate-300 dark:border-slate-700 sepia:border-sepia-border">
                        <span class="text-xs text-slate-800 dark:text-slate-200 sepia:text-sepia-text font-medium">{{ $genre->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label for="cover_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Ảnh bìa tác phẩm (WebP / PNG / JPG)</label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 dark:file:bg-indigo-500/20 sepia:file:bg-amber-900/20 file:text-indigo-700 dark:file:text-indigo-400 sepia:file:text-amber-900 hover:file:opacity-80 transition-opacity">
        </div>

        <div>
            <label for="copyright_license_notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Ghi chú giấy phép / Giấy ủy quyền bản quyền (nếu có)</label>
            <textarea id="copyright_license_notes" name="copyright_license_notes" rows="2" placeholder="Ghi rõ nguồn, số hợp đồng chuyển nhượng quyền tác giả…" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">{{ old('copyright_license_notes', $novel->copyright_license_notes) }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
            <a href="/tac-gia/truyen" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 sepia:text-sepia-muted hover:text-slate-900 dark:hover:text-white sepia:hover:text-sepia-text transition-colors">
                Hủy Bỏ
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-md sepia:shadow-amber-900/20 transition-colors shadow-md">
                {{ $novel->exists ? 'Cập Nhật Tác Phẩm' : 'Tạo Tác Phẩm & Lưu Nháp' }}
            </button>
        </div>
    </form>

    @if($novel->exists)
        <!-- Chapter Management Section -->
        <div class="mt-10 bg-white dark:bg-slate-900/60 sepia:bg-sepia-card rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border p-6 sm:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 border border-indigo-500/25 sepia:border-amber-800/30 mb-2">
                        <span aria-hidden="true">📖</span> Quản Lý Bản Thảo &amp; Xuất Bản
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
                        Quản Lý Chương &amp; Bản Thảo
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs sm:text-sm mt-1">
                        Hiện có <strong class="text-slate-900 dark:text-white sepia:text-sepia-text font-bold">{{ $novel->chapters->count() }}</strong> chương trong tác phẩm này.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('author.chapters.create', $novel->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 sepia:focus-visible:ring-amber-700 transition-colors" aria-label="Soạn chương mới cho {{ $novel->title }}">
                        <span aria-hidden="true">✍️</span>
                        <span>+ Soạn Chương Mới</span>
                    </a>
                    <a href="{{ route('author.novels.bulk-chapters.create', $novel->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 sepia:text-sepia-text bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 border border-slate-200 dark:border-slate-700 sepia:border-sepia-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-colors" aria-label="Tách chương hàng loạt từ văn bản thô cho {{ $novel->title }}">
                        <span aria-hidden="true">📥</span>
                        <span>Tách Chương Hàng Loạt</span>
                    </a>
                    <a href="{{ route('author.novels.3d-assets.index', $novel->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 sepia:text-sepia-text bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 border border-slate-200 dark:border-slate-700 sepia:border-sepia-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-colors" aria-label="Quản lý kho mô hình 3D cho {{ $novel->title }}">
                        <span aria-hidden="true">🎨</span>
                        <span>Kho Mô Hình 3D</span>
                    </a>
                </div>
            </div>

            @if($novel->chapters->isEmpty())
                <div class="text-center py-12 px-4">
                    <span class="text-4xl" aria-hidden="true">📝</span>
                    <h3 class="text-base font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text mt-3">Chưa có chương nào được viết</h3>
                    <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs mt-1 max-w-md mx-auto leading-relaxed">
                        Tác phẩm của bạn đã sẵn sàng! Hãy bắt đầu viết chương mở đầu bằng Zen Editor hoặc dán toàn bộ văn bản để hệ thống tự động bóc tách chương.
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-3">
                        <a href="{{ route('author.chapters.create', $novel->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-sm transition-colors">
                            <span>+ Bắt Đầu Viết Chương 1</span>
                        </a>
                        <a href="{{ route('author.novels.bulk-chapters.create', $novel->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 sepia:text-sepia-text bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 transition-colors">
                            <span>Tách Chương Hàng Loạt</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border">
                    <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300 sepia:text-sepia-text">
                        <thead class="bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-100 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs uppercase tracking-wider text-slate-600 dark:text-slate-400 sepia:text-sepia-muted">
                            <tr>
                                <th scope="col" class="px-4 py-3.5 font-semibold text-center w-24">Số chương</th>
                                <th scope="col" class="px-4 py-3.5 font-semibold">Tiêu đề chương</th>
                                <th scope="col" class="px-4 py-3.5 font-semibold text-center w-28">Số từ</th>
                                <th scope="col" class="px-4 py-3.5 font-semibold text-center w-32">Trạng thái</th>
                                <th scope="col" class="px-4 py-3.5 font-semibold text-right w-44">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 sepia:divide-sepia-border/60 bg-white dark:bg-slate-900 sepia:bg-sepia-card">
                            @foreach($novel->chapters as $chapter)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 sepia:hover:bg-sepia-100/40 transition-colors">
                                    <td class="px-4 py-3 text-center font-mono font-bold tabular-nums text-indigo-700 dark:text-indigo-400 sepia:text-amber-900">
                                        #{{ number_format($chapter->chapter_number, 0) }}
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        <div class="font-bold text-slate-900 dark:text-white sepia:text-sepia-text">
                                            {{ $chapter->title }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                            /{{ $chapter->slug }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono tabular-nums text-xs">
                                        {{ number_format($chapter->word_count) }} chữ
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($chapter->status === 'published')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 sepia:text-emerald-800 border border-emerald-500/25">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                                Xuất bản
                                            </span>
                                        @elseif($chapter->status === 'pending_approval')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-500/10 text-sky-700 dark:text-sky-400 sepia:text-sky-800 border border-sky-500/25">
                                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500" aria-hidden="true"></span>
                                                Chờ duyệt
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 sepia:text-amber-900 border border-amber-500/25">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                                                Bản nháp
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('author.chapters.edit', [$novel->id, $chapter->id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 bg-indigo-50 dark:bg-indigo-950/50 sepia:bg-amber-900/15 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 sepia:hover:bg-amber-900/30 border border-indigo-200 dark:border-indigo-800 sepia:border-amber-800/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-colors" aria-label="Sửa bản thảo {{ $chapter->title }}">
                                                <span aria-hidden="true">✏️</span>
                                                <span>Sửa</span>
                                            </a>
                                            <a href="{{ route('author.chapters.projections.index', $chapter->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 sepia:text-sepia-muted bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 border border-slate-200 dark:border-slate-700 sepia:border-sepia-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition-colors" title="Hiệu ứng 3D" aria-label="Gắn hiệu ứng 3D cho {{ $chapter->title }}">
                                                <span aria-hidden="true">🎨</span>
                                                <span>3D</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
