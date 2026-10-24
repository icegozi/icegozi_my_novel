@extends('layouts.admin')

@section('title', 'Chỉnh sửa chương - ' . $chapter->title)

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.novels.chapters.index', $chapter->novel_id) }}" 
               class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
               aria-label="Quay lại danh sách chương">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Chỉnh sửa nội dung chương</h1>
                <p class="text-sm text-slate-400">
                    Thuộc tác phẩm: <span class="text-indigo-400 font-semibold">{{ $chapter->novel?->title }}</span> 
                    | Phiên bản hiện tại: <span class="font-mono text-emerald-400 font-semibold">rev #{{ $chapter->revision }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ Route::has('admin.chapters.history') ? route('admin.chapters.history', $chapter) : '#' }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium transition-colors border border-slate-700">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Lịch sử phiên bản & Diff
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

    <form action="{{ route('admin.chapters.update', $chapter) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tiêu đề chương <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $chapter->title) }}" 
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
                        @foreach (['draft', 'pending_approval', 'published', 'scheduled', 'rejected', 'archived'] as $st)
                            <option value="{{ $st }}" {{ old('status', $chapter->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Scheduled At -->
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Hẹn giờ đăng tự động (Scheduled At)
                    </label>
                    <input type="datetime-local" 
                           id="scheduled_at" 
                           name="scheduled_at" 
                           value="{{ old('scheduled_at', $chapter->scheduled_at?->format('Y-m-d\TH:i')) }}" 
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                </div>

                <!-- Change Summary -->
                <div class="md:col-span-2">
                    <label for="change_summary" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Ghi chú bản sửa đổi (Lưu vào lịch sử phiên bản)
                    </label>
                    <input type="text" 
                           id="change_summary" 
                           name="change_summary" 
                           value="{{ old('change_summary') }}" 
                           placeholder="Ví dụ: Sửa chính tả đoạn cuối, chuẩn hóa xưng hô..." 
                           maxlength="255"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                </div>

                <!-- Content Area -->
                <div class="md:col-span-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="content" class="block text-sm font-medium text-slate-300">
                            Nội dung chi tiết chương <span class="text-rose-400">*</span>
                        </label>
                        <span class="text-xs text-slate-500 font-mono">
                            Hiện tại: {{ number_format($chapter->word_count) }} từ
                        </span>
                    </div>
                    <textarea id="content" 
                              name="content" 
                              rows="16" 
                              required 
                              class="w-full p-4 rounded-lg bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors font-serif leading-relaxed text-base">{{ old('content', $chapter->content) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.novels.chapters.index', $chapter->novel_id) }}" 
               class="px-5 py-2.5 rounded-lg border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 font-medium text-sm transition-colors">
                Hủy bỏ
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition-colors shadow-lg shadow-indigo-600/20">
                Lưu chương & Tạo phiên bản mới
            </button>
        </div>
    </form>
</div>
@endsection
