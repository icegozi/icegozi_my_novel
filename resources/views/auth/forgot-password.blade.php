@extends('layouts.reader')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/60 sepia:bg-sepia-50 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto shadow-md">
                🔑
            </span>
            <h1 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                Khôi Phục Mật Khẩu
            </h1>
            <p class="text-xs text-slate-500">
                Nhập địa chỉ email đăng ký để nhận liên kết xác thực tạo mật khẩu mới
            </p>
        </div>

        @if (session('status'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs leading-relaxed" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs space-y-1" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="/forgot-password" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Địa chỉ Email Đã Đăng Ký
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="ban@example.com"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <button
                type="submit"
                class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/25 transition"
            >
                Gửi Hướng Dẫn Khôi Phục
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800/80">
            <a href="/login" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Quay lại trang Đăng Nhập
            </a>
        </div>
    </div>
</div>
@endsection
