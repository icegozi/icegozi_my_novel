@extends('layouts.reader')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Tài Khoản Thành Viên</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Hồ Sơ Cá Nhân</h1>
        <p class="text-xs text-slate-500">Thông tin tài khoản, thống kê hoạt động đọc và các huy hiệu đạt được</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="md:col-span-1 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm text-center space-y-4">
            <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-purple-600 to-indigo-600 text-white font-black text-2xl flex items-center justify-center mx-auto uppercase shadow-lg shadow-indigo-500/25">
                {{ substr($user->name, 0, 1) }}
            </div>

            <div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white sepia:text-sepia-text">{{ $user->name }}</h3>
                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                    {{ $user->role }}
                </span>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-2 text-center text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px]">Đã Đánh Dấu</span>
                    <strong class="text-slate-900 dark:text-white sepia:text-sepia-text font-mono text-base tabular-nums">{{ $bookmarksCount }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Tác Phẩm Đã Đọc</span>
                    <strong class="text-slate-900 dark:text-white sepia:text-sepia-text font-mono text-base tabular-nums">{{ $historiesCount }}</strong>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="md:col-span-2 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm space-y-6">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white sepia:text-sepia-text border-b border-slate-100 dark:border-slate-800 pb-3">
                Cập Nhật Thông Tin Hiển Thị
            </h3>

            <form action="/profile" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                        Họ Tên / Bút Danh
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                        Email Đăng Ký (Không thể thay đổi)
                    </label>
                    <input
                        type="email"
                        id="email"
                        value="{{ $user->email }}"
                        disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/30 text-slate-400 text-sm cursor-not-allowed"
                    >
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1">
                        Số Điện Thoại Liên Hệ (Tùy chọn)
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="0988xxxxxx"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-sm text-slate-900 dark:text-white sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div class="pt-2 flex justify-end">
                    <button
                        type="submit"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-md transition"
                    >
                        Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
