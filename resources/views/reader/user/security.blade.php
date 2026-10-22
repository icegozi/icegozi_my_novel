@extends('layouts.reader')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-rose-500">Bảo Vệ Tài Khoản</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Bảo Mật & Mật Khẩu</h1>
        <p class="text-xs text-slate-500">Quản lý mật khẩu đăng nhập và xem phiên làm việc đang hoạt động</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs font-bold">
        <a href="/settings" class="pb-3 text-slate-500 hover:text-slate-900 dark:hover:text-white transition">
            Tùy Biến Giao Diện Đọc
        </a>
        <a href="/settings/security" class="pb-3 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400">
            Bảo Mật & Mật Khẩu
        </a>
    </div>

    <div class="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm space-y-6">
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs space-y-1" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="/settings/security" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Mật khẩu hiện tại
                </label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                    autocomplete="current-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Mật khẩu mới (Tối thiểu 8 ký tự)
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Xác nhận lại mật khẩu mới
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div class="pt-2 flex justify-end">
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider shadow-md transition"
                >
                    Đổi Mật Khẩu
                </button>
            </div>
        </form>

        <!-- Sessions List -->
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500">Phiên Đăng Nhập Đang Hoạt Động</h4>
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                <div class="flex items-center gap-3">
                    <span class="text-xl">💻</span>
                    <div>
                        <strong class="text-slate-900 dark:text-white block">Trình duyệt này (Windows / Chrome)</strong>
                        <span class="text-slate-400 text-[11px]">127.0.0.1 • Đang hoạt động</span>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-500">
                    Hiện Tại
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
