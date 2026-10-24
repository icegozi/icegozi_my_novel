@extends('layouts.admin')

@section('title', 'Tạo tác phẩm mới - Admin CMS')

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.novels.index') }}" 
           class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
           aria-label="Quay lại danh sách tác phẩm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Tạo tác phẩm mới</h1>
            <p class="text-sm text-slate-400">Đăng tải và khởi tạo tác phẩm thủ công thay mặt tác giả trên hệ thống</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
            <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
        <form action="{{ route('admin.novels.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Author Selection -->
                <div class="md:col-span-2">
                    <label for="author_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tác giả chủ quản <span class="text-rose-400">*</span>
                    </label>
                    <select id="author_id" 
                            name="author_id" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                        <option value="">-- Chọn tác giả đã xác minh --</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->user_id }}" {{ old('author_id') == $author->user_id ? 'selected' : '' }}>
                                {{ $author->pen_name }} ({{ $author->user?->name ?? 'User' }} - {{ $author->user?->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tiêu đề tác phẩm <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}" 
                           required 
                           maxlength="255"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors"
                           placeholder="Nhập tên tiểu thuyết, truyện dài...">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Trạng thái ban đầu <span class="text-rose-400">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Bản nháp (draft)</option>
                        <option value="pending_approval" {{ old('status') === 'pending_approval' ? 'selected' : '' }}>Chờ duyệt (pending)</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Xuất bản ngay (published)</option>
                    </select>
                </div>

                <!-- Age Rating -->
                <div>
                    <label for="age_rating" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Độ tuổi tiếp cận <span class="text-rose-400">*</span>
                    </label>
                    <select id="age_rating" 
                            name="age_rating" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                        <option value="all">Mọi lứa tuổi (All)</option>
                        <option value="13_plus">13+</option>
                        <option value="16_plus">16+</option>
                        <option value="18_plus">18+</option>
                    </select>
                </div>

                <!-- Copyright Type -->
                <div class="md:col-span-2">
                    <label for="copyright_type" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Phân loại bản quyền <span class="text-rose-400">*</span>
                    </label>
                    <select id="copyright_type" 
                            name="copyright_type" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                        <option value="original">Sáng tác gốc (Original)</option>
                        <option value="authorized_translation">Dịch có ủy quyền bản quyền</option>
                        <option value="public_domain">Tác phẩm phạm vi công cộng</option>
                    </select>
                </div>

                <!-- Synopsis -->
                <div class="md:col-span-2">
                    <label for="synopsis" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tóm tắt giới thiệu truyện <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="synopsis" 
                              name="synopsis" 
                              rows="4" 
                              required 
                              class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors"
                              placeholder="Nhập tóm tắt hấp dẫn thu hút độc giả...">{{ old('synopsis') }}</textarea>
                </div>

                <!-- Genres Checklist -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Thể loại tác phẩm</label>
                    <div class="flex flex-wrap gap-2 p-3 rounded-lg bg-slate-800/40 border border-slate-800 max-h-36 overflow-y-auto">
                        @foreach ($genres as $genre)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 text-xs text-slate-300 hover:text-white cursor-pointer select-none border border-slate-700 transition-colors">
                                <input type="checkbox" name="genres[]" value="{{ $genre->id }}" class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                {{ $genre->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('admin.novels.index') }}" 
                   class="px-5 py-2.5 rounded-lg border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 font-medium text-sm transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition-colors shadow-lg shadow-indigo-600/20">
                    Khởi tạo tác phẩm
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
