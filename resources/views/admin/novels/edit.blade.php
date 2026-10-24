@extends('layouts.admin')

@section('title', 'Chỉnh sửa tác phẩm - ' . $novel->title)

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.novels.index') }}" 
               class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
               aria-label="Quay lại danh sách tác phẩm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Chỉnh sửa tác phẩm</h1>
                <p class="text-sm text-slate-400">Tác giả: <span class="text-indigo-400 font-semibold">{{ $novel->author?->authorProfile?->pen_name ?? $novel->author?->name ?? 'N/A' }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.novels.chapters.index', $novel) }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium transition-colors border border-slate-700">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Quản lý chương ({{ $novel->chapters()->count() }})
            </a>
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

    <form action="{{ route('admin.novels.update', $novel) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- General Info Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                Thông tin cơ bản tác phẩm
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tiêu đề tác phẩm <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $novel->title) }}" 
                           required 
                           maxlength="255"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Trạng thái xuất bản <span class="text-rose-400">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                        @foreach (['draft', 'pending_approval', 'published', 'paused', 'completed', 'rejected', 'takedown', 'archived'] as $st)
                            <option value="{{ $st }}" {{ old('status', $novel->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
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
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                        @foreach (['all' => 'Mọi lứa tuổi', '13_plus' => '13+', '16_plus' => '16+', '18_plus' => '18+'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('age_rating', $novel->age_rating) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Copyright Type -->
                <div>
                    <label for="copyright_type" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Bản quyền tác phẩm <span class="text-rose-400">*</span>
                    </label>
                    <select id="copyright_type" 
                            name="copyright_type" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                        <option value="original" {{ old('copyright_type', $novel->copyright_type) === 'original' ? 'selected' : '' }}>Sáng tác gốc (Original)</option>
                        <option value="authorized_translation" {{ old('copyright_type', $novel->copyright_type) === 'authorized_translation' ? 'selected' : '' }}>Chuyển ngữ có ủy quyền</option>
                        <option value="public_domain" {{ old('copyright_type', $novel->copyright_type) === 'public_domain' ? 'selected' : '' }}>Thuộc phạm vi công cộng</option>
                    </select>
                </div>

                <!-- Synopsis -->
                <div class="md:col-span-2">
                    <label for="synopsis" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tóm tắt nội dung <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="synopsis" 
                              name="synopsis" 
                              rows="4" 
                              required 
                              class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">{{ old('synopsis', $novel->synopsis) }}</textarea>
                </div>

                <!-- Tags Checklist -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Từ khóa / Nhãn Tags</label>
                    <div class="flex flex-wrap gap-2 p-3 rounded-lg bg-slate-800/40 border border-slate-800 max-h-36 overflow-y-auto">
                        @foreach ($allTags as $tag)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 text-xs text-slate-300 hover:text-white cursor-pointer select-none border border-slate-700 transition-colors">
                                <input type="checkbox" 
                                       name="tags[]" 
                                       value="{{ $tag->id }}" 
                                       {{ in_array($tag->id, old('tags', $novel->tags->pluck('id')->toArray())) ? 'checked' : '' }}
                                       class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Metadata Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Tối ưu SEO & OpenGraph Social Sharing
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Meta Title -->
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-slate-300 mb-1.5">
                        SEO Meta Title
                    </label>
                    <input type="text" 
                           id="meta_title" 
                           name="meta_title" 
                           value="{{ old('meta_title', $novel->meta_title) }}" 
                           maxlength="255"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors"
                           placeholder="Tiêu đề chuẩn SEO hiển thị trên Google...">
                </div>

                <!-- Canonical URL -->
                <div>
                    <label for="canonical_url" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Canonical URL
                    </label>
                    <input type="url" 
                           id="canonical_url" 
                           name="canonical_url" 
                           value="{{ old('canonical_url', $novel->canonical_url) }}" 
                           maxlength="500"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors"
                           placeholder="https://example.com/truyen/slug...">
                </div>

                <!-- OG Image -->
                <div>
                    <label for="og_image" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Ảnh đại diện OpenGraph (OG Image URL)
                    </label>
                    <input type="text" 
                           id="og_image" 
                           name="og_image" 
                           value="{{ old('og_image', $novel->og_image) }}" 
                           maxlength="255"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors"
                           placeholder="https://example.com/images/share.jpg...">
                </div>

                <!-- Meta Description -->
                <div class="md:col-span-2">
                    <label for="meta_description" class="block text-sm font-medium text-slate-300 mb-1.5">
                        SEO Meta Description (Khuyến nghị 150 - 160 ký tự)
                    </label>
                    <textarea id="meta_description" 
                              name="meta_description" 
                              rows="3" 
                              maxlength="500"
                              class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-colors"
                              placeholder="Mô tả tóm tắt chuẩn SEO thu hút lượt bấm từ trang tìm kiếm...">{{ old('meta_description', $novel->meta_description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.novels.index') }}" 
               class="px-5 py-2.5 rounded-lg border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 font-medium text-sm transition-colors">
                Hủy bỏ
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition-colors shadow-lg shadow-indigo-600/20">
                Lưu thay đổi tác phẩm
            </button>
        </div>
    </form>
</div>
@endsection
