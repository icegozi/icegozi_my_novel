@extends('layouts.reader')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/60 sepia:bg-sepia-50 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-emerald-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto shadow-md">
                🔒
            </span>
            <h1 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                Đặt Lại Mật Khẩu
            </h1>
            <p class="text-xs text-slate-500">
                Nhập mật khẩu mới an toàn cho tài khoản của bạn
            </p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs space-y-1" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="/reset-password" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Địa chỉ Email
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    autocomplete="email"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                    Mật Khẩu Mới (Tối thiểu 8 ký tự)
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
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
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <button
                type="submit"
                class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/25 transition"
            >
                Cập Nhật Mật Khẩu
            </button>
        </form>
    </div>
</div>
@endsection
