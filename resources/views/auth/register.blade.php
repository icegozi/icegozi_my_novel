@extends('layouts.reader')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/60 sepia:bg-sepia-50 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto shadow-md shadow-indigo-500/25">
                3D
            </span>
            <h1 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                Tạo Tài Khoản
            </h1>
            <p class="text-xs text-slate-500">Gia nhập cộng đồng người đọc và sáng tạo nội dung văn học</p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs space-y-1" role="alert" tabindex="-1">
                <h2 class="font-bold">Vui lòng kiểm tra lại thông tin:</h2>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/register" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Họ và Tên hoặc Bút Danh
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Nguyễn Văn A"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Địa chỉ Email
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    spellcheck="false"
                    placeholder="ban@example.com"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Mật khẩu (Tối thiểu 8 ký tự)
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Xác nhận lại mật khẩu
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div class="pt-1">
                <label class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted cursor-pointer">
                    <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>
                        Tôi đồng ý với <a href="/dieu-khoan-va-ban-quyen" class="underline text-indigo-600 dark:text-indigo-400" target="_blank">Điều khoản sử dụng</a> và <a href="/dieu-khoan-va-ban-quyen" class="underline text-indigo-600 dark:text-indigo-400" target="_blank">Chính sách bảo mật dữ liệu</a>.
                    </span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5"
            >
                Tạo Tài Khoản
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800/80">
            Đã có tài khoản?
            <a href="/login" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                Đăng nhập ngay
            </a>
        </div>
    </div>
</div>
@endsection
