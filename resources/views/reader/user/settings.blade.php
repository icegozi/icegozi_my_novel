@extends('layouts.reader')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Trải Nghiệm Đọc</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Cài Đặt Đọc</h1>
        <p class="text-xs text-slate-500">Tùy biến các thông số mặc định cho trình đọc tiểu thuyết và truyện tranh</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs font-bold">
        <a href="/settings" class="pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400">
            Tùy Biến Giao Diện Đọc
        </a>
        <a href="/settings/security" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Bảo Mật & Mật Khẩu
        </a>
    </div>

    <div class="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm space-y-6">
        <form action="/settings" method="POST" class="space-y-6">
            @csrf

            <!-- Theme Default -->
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text">
                    Chủ Đề Mặc Định
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="p-4 rounded-2xl border border-amber-300 bg-[#f5efe6] text-amber-950 font-bold text-xs flex flex-col items-center gap-2 cursor-pointer hover:border-indigo-500 transition">
                        <input type="radio" name="theme" value="sepia" {{ ($preferences['theme'] ?? 'sepia') === 'sepia' ? 'checked' : '' }} class="text-indigo-600">
                        <span>Giấy Cổ (Sepia)</span>
                    </label>
                    <label class="p-4 rounded-2xl border border-slate-700 bg-slate-950 text-white font-bold text-xs flex flex-col items-center gap-2 cursor-pointer hover:border-indigo-500 transition">
                        <input type="radio" name="theme" value="dark" {{ ($preferences['theme'] ?? 'sepia') === 'dark' ? 'checked' : '' }} class="text-indigo-600">
                        <span>Tối (Dark)</span>
                    </label>
                    <label class="p-4 rounded-2xl border border-slate-200 bg-white text-slate-900 font-bold text-xs flex flex-col items-center gap-2 cursor-pointer hover:border-indigo-500 transition">
                        <input type="radio" name="theme" value="light" {{ ($preferences['theme'] ?? '') === 'light' ? 'checked' : '' }} class="text-indigo-600">
                        <span>Sáng (Light)</span>
                    </label>
                </div>
            </div>

            <!-- Font Size Default -->
            <div class="space-y-2">
                <label for="font_size" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text">
                    Cỡ Chữ Mặc Định
                </label>
                <select
                    id="font_size"
                    name="font_size"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="sm" {{ ($preferences['font_size'] ?? '') === 'sm' ? 'selected' : '' }}>Nhỏ (15px)</option>
                    <option value="base" {{ ($preferences['font_size'] ?? 'base') === 'base' ? 'selected' : '' }}>Tiêu chuẩn (18px)</option>
                    <option value="lg" {{ ($preferences['font_size'] ?? '') === 'lg' ? 'selected' : '' }}>Lớn (21px)</option>
                    <option value="xl" {{ ($preferences['font_size'] ?? '') === 'xl' ? 'selected' : '' }}>Rất lớn (24px)</option>
                </select>
            </div>

            <!-- Font Family Default -->
            <div class="space-y-2">
                <label for="font_family" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text">
                    Kiểu Phông Mặc Định
                </label>
                <select
                    id="font_family"
                    name="font_family"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="serif" {{ ($preferences['font_family'] ?? 'serif') === 'serif' ? 'selected' : '' }}>Merriweather (Có chân, chuẩn xuất bản sách)</option>
                    <option value="sans" {{ ($preferences['font_family'] ?? '') === 'sans' ? 'selected' : '' }}>Plus Jakarta Sans (Không chân, hiện đại)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-md transition"
                >
                    Lưu Tùy Chọn Đọc
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
