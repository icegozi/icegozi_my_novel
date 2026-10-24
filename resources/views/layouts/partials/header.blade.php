<header class="border-b border-slate-200/80 dark:border-slate-800/80 sepia:border-sepia-border bg-white/80 dark:bg-slate-900/80 sepia:bg-sepia-50/90 backdrop-blur-md sticky top-0 z-40 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Logo & Brand -->
        <div class="flex items-center gap-6 shrink-0">
            <a href="/" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 flex items-center justify-center font-extrabold text-white text-base shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                    3D
                </span>
                <div class="flex flex-col">
                    <span class="font-heading font-black text-lg tracking-wider text-slate-900 dark:text-white sepia:text-sepia-text leading-tight">
                        NOVEL ENGINE
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 sepia:text-amber-800">
                        Đọc Truyện & Comic
                    </span>
                </div>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center gap-1 text-sm font-semibold text-slate-600 dark:text-slate-300 sepia:text-sepia-muted" aria-label="Menu chính">
                <a href="/" class="px-3 py-2 rounded-lg hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 transition {{ request()->is('/') ? 'text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 font-bold bg-indigo-50 dark:bg-slate-800/60 sepia:bg-sepia-200' : '' }}">
                    Trang Chủ
                </a>
                <a href="/genres" class="px-3 py-2 rounded-lg hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 transition {{ request()->is('genres*') ? 'text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 font-bold bg-indigo-50 dark:bg-slate-800/60 sepia:bg-sepia-200' : '' }}">
                    Thể Loại
                </a>
                <a href="/latest" class="px-3 py-2 rounded-lg hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 transition {{ request()->is('latest*') ? 'text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 font-bold bg-indigo-50 dark:bg-slate-800/60 sepia:bg-sepia-200' : '' }}">
                    Mới Cập Nhật
                </a>
                <a href="/popular" class="px-3 py-2 rounded-lg hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 transition {{ request()->is('popular*') ? 'text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 font-bold bg-indigo-50 dark:bg-slate-800/60 sepia:bg-sepia-200' : '' }}">
                    Bảng Xếp Hạng
                </a>
            </nav>
        </div>

        <!-- Search Bar -->
        <div class="flex-1 max-w-md hidden sm:block">
            <form action="/search" method="GET" class="relative" role="search">
                <label for="header-search-input" class="sr-only">Tìm kiếm truyện, tác giả, thể loại</label>
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input
                    type="search"
                    id="header-search-input"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Tìm kiếm tác phẩm, tác giả… (Ctrl+K)"
                    autocomplete="off"
                    class="w-full pl-10 pr-12 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-slate-50 dark:bg-slate-800/60 sepia:bg-sepia-100 text-slate-900 dark:text-slate-100 sepia:text-sepia-text placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                >
                <kbd class="hidden md:inline-flex absolute right-2.5 top-2.5 px-1.5 py-0.5 text-[10px] font-mono font-medium text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 rounded border border-slate-300 dark:border-slate-600">
                    ⌘K
                </kbd>
            </form>
        </div>

        <!-- Right Utilities & Auth -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Mobile Search Icon Link -->
            <a href="/search" class="sm:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Tìm kiếm truyện">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </a>

            <!-- Theme Switcher Button -->
            <button
                type="button"
                id="header-theme-toggle"
                onclick="window.toggleReaderTheme()"
                class="p-2 rounded-xl text-slate-600 dark:text-slate-300 sepia:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 border border-transparent dark:border-slate-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                aria-label="Đổi giao diện Sáng / Vàng (Sepia)"
                title="Đổi giao diện (Sáng / Vàng)"
            >
                <!-- Sun indicator (visible in sepia/warm to toggle to light) -->
                <svg class="w-5 h-5 hidden sepia:block text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Warm paper / book indicator (visible in light mode to toggle to yellow/sepia) -->
                <svg class="w-5 h-5 block sepia:hidden text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </button>

            @auth
                <!-- User Shortcuts -->
                <a href="/library" class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 sepia:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Tủ sách của tôi">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Tủ Sách</span>
                </a>

                <!-- User Dropdown Menu -->
                <div class="relative" id="user-menu-container">
                    <button
                        type="button"
                        id="user-menu-button"
                        aria-label="Menu tài khoản cá nhân"
                        aria-expanded="false"
                        aria-haspopup="true"
                        onclick="document.getElementById('user-dropdown').classList.toggle('hidden')"
                        class="flex items-center gap-2 p-1 rounded-xl hover:ring-2 hover:ring-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition"
                    >
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center uppercase shadow-sm">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                    </button>

                    <div
                        id="user-dropdown"
                        class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-white dark:bg-slate-900 sepia:bg-sepia-50 border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-2xl py-2 z-50 text-xs"
                    >
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                            <p class="font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="/profile" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Hồ Sơ Cá Nhân</a>
                        <a href="/library" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Tủ Sách Đã Lưu</a>
                        <a href="/history" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Lịch Sử Đọc</a>
                        <a href="/bookmarks" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Dấu Trang</a>
                        <a href="/notifications" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Thông Báo</a>
                        <a href="/settings" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Cài Đặt Đọc</a>
                        <a href="/settings/security" class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Bảo Mật & Mật Khẩu</a>
                        
                        @if(Auth::user()->role === 'author' || Auth::user()->role === 'admin')
                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                            <a href="/tac-gia/truyen" class="block px-4 py-2 font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-800 transition">Studio Tác Giả</a>
                        @endif

                        <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                        <form action="/logout" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                Đăng Xuất
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <a href="/login" class="px-3.5 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-200 sepia:text-sepia-text hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Đăng Nhập
                    </a>
                    <a href="/register" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white shadow-md shadow-indigo-500/20 sepia:shadow-amber-900/25 transition">
                        Đăng Ký
                    </a>
                </div>
            @endauth

            <!-- Mobile menu button -->
            <button
                type="button"
                id="mobile-menu-toggle"
                onclick="document.getElementById('mobile-nav').classList.toggle('hidden')"
                class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                aria-label="Mở danh mục điều hướng"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Nav Dropdown -->
    <div id="mobile-nav" class="hidden md:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 sepia:bg-sepia-50 px-4 pt-3 pb-6 space-y-2 text-sm font-semibold">
        <a href="/" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Trang Chủ</a>
        <a href="/genres" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Thể Loại</a>
        <a href="/latest" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Mới Cập Nhật</a>
        <a href="/popular" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Bảng Xếp Hạng</a>
        @auth
            <a href="/library" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Tủ Sách Cá Nhân</a>
            <a href="/history" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Lịch Sử Đọc</a>
            <a href="/settings" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Cài Đặt Đọc</a>
        @endauth
    </div>
</header>
