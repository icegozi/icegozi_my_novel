@extends('layouts.reader')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/60 sepia:bg-sepia-50 shadow-xl space-y-6">
        <!-- Card Header -->
        <div class="text-center space-y-2">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-extrabold text-lg flex items-center justify-center mx-auto shadow-md shadow-indigo-500/25">
                3D
            </span>
            <h1 class="text-2xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">
                Đăng Nhập
            </h1>
            <p class="text-xs text-slate-500">Tiếp tục hành trình đọc và đồng bộ hóa thư viện cá nhân</p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs space-y-1" role="alert" tabindex="-1">
                <h2 class="font-bold">Đã có lỗi xảy ra:</h2>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/login" method="POST" class="space-y-4">
            @csrf
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
                    autofocus
                    autocomplete="email"
                    spellcheck="false"
                    placeholder="ban@example.com"
                    class="w-full px-4 py-2.5 rounded-xl border @error('email') border-rose-500 @else border-slate-200 dark:border-slate-800 sepia:border-sepia-border @enderror bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text">
                        Mật khẩu
                    </label>
                    <a href="/forgot-password" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                        Quên mật khẩu?
                    </a>
                </div>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-4 pr-10 py-2.5 rounded-xl border @error('password') border-rose-500 @else border-slate-200 dark:border-slate-800 sepia:border-sepia-border @enderror bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <button
                        type="button"
                        onclick="togglePasswordVisibility('password', this)"
                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        aria-label="Ẩn hiện mật khẩu"
                    >
                        👁️
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600 dark:text-slate-400 sepia:text-sepia-muted cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5"
            >
                Đăng Nhập
            </button>
        </form>

        <!-- Demo Account Quick Fill Pill -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 text-center space-y-2">
            <span class="text-[11px] text-slate-400 block">Tài khoản thử nghiệm demo:</span>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <button
                    type="button"
                    onclick="fillCredentials('author@novel3d.vn', 'password123')"
                    class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-500 hover:bg-indigo-500/20 text-[11px] font-semibold border border-indigo-500/20"
                >
                    Tác giả (author@novel3d.vn)
                </button>
                <button
                    type="button"
                    onclick="fillCredentials('admin@novel3d.vn', 'password123')"
                    class="px-2.5 py-1 rounded-lg bg-purple-500/10 text-purple-500 hover:bg-purple-500/20 text-[11px] font-semibold border border-purple-500/20"
                >
                    Quản trị viên (admin@novel3d.vn)
                </button>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500">
            Chưa có tài khoản?
            <a href="/register" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                Đăng ký ngay
            </a>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId, btn) {
        var field = document.getElementById(fieldId);
        if (field.type === 'password') {
            field.type = 'text';
            btn.textContent = '🔒';
        } else {
            field.type = 'password';
            btn.textContent = '👁️';
        }
    }

    function fillCredentials(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
