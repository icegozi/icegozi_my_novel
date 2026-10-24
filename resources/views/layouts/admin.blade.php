<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Quản Trị Hệ Thống - Novel Engine' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&family=Lora:ital,wght@0,400;0,500;1,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
        .font-reader { font-family: 'Lora', serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white"
      x-data="{ mobileNavOpen: false }"
      {{ $bodyAttributes ?? '' }}>

    <!-- Mobile Off-Canvas Drawer Backdrop -->
    <div x-cloak
         x-show="mobileNavOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 lg:hidden transition-opacity"
         @click="mobileNavOpen = false"
         aria-hidden="true"></div>

    <!-- Sidebar Navigation (Desktop Fixed + Mobile Drawer) -->
    <aside id="admin-sidebar"
           :class="mobileNavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900/95 border-r border-slate-800/80 flex flex-col justify-between transition-transform duration-200 ease-in-out shadow-2xl lg:shadow-none"
           role="dialog"
           aria-modal="true"
           aria-label="Menu điều hướng quản trị">
        
        <!-- Sidebar Header -->
        <div class="p-6 border-b border-slate-800/80 flex items-center justify-between">
            <a href="/admin/dashboard" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-lg tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL ADMIN</span>
            </a>
            <button type="button"
                    @click="mobileNavOpen = false"
                    class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    aria-label="Đóng menu quản trị">
                <span aria-hidden="true">✕</span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5" aria-label="Menu điều hướng chính">
            @php
                $currentUrl = request()->path();
                $navItems = [
                    ['url' => '/admin/dashboard', 'match' => 'admin/dashboard*', 'label' => 'Bảng Điều Khiển', 'icon' => '📊'],
                    ['url' => '/admin/tac-pham', 'match' => 'admin/tac-pham*', 'label' => 'Quản Lý Tác Phẩm', 'icon' => '📚'],
                    ['url' => '/admin/tac-gia', 'match' => 'admin/tac-gia*', 'label' => 'Quản Lý Tác Giả', 'icon' => '✍️'],
                    ['url' => '/admin/kiem-duyet', 'match' => 'admin/kiem-duyet*', 'label' => 'Hàng Đợi Kiểm Duyệt', 'icon' => '🛡️'],
                    ['url' => '/admin/ban-quyen', 'match' => 'admin/ban-quyen*', 'label' => 'Bản Quyền & DMCA', 'icon' => '⚖️'],
                    ['url' => '/admin/dinh-danh-tac-gia', 'match' => 'admin/dinh-danh-tac-gia*', 'label' => 'Định Danh e-KYC', 'icon' => '🪪'],
                    ['url' => '/admin/du-lieu', 'match' => 'admin/du-lieu*', 'label' => 'Nhập Xuất Dữ Liệu', 'icon' => '📥'],
                    ['url' => '/admin/nhat-ky-an-ninh', 'match' => 'admin/nhat-ky-an-ninh*', 'label' => 'Nhật Ký An Ninh', 'icon' => '📜'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php
                    $isActive = request()->is($item['match']);
                @endphp
                <a href="{{ $item['url'] }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold min-h-[44px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $isActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base" aria-hidden="true">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/40">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="truncate max-w-[120px]">{{ auth()->user()?->name ?? 'Moderator' }}</span>
                </div>
                <span class="uppercase text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-bold">{{ auth()->user()?->role ?? 'admin' }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:text-white hover:bg-rose-600/20 border border-rose-500/20 transition-colors min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                    <span aria-hidden="true">🚪</span> Đăng Xuất
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Top Navbar -->
        <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
            <div class="px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button type="button"
                            @click="mobileNavOpen = true"
                            class="lg:hidden p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                            aria-label="Mở menu quản trị">
                        <span class="text-xl" aria-hidden="true">☰</span>
                    </button>
                    <span class="hidden sm:inline-block">
                        <x-badge variant="amber" size="sm">Cổng Kiểm Duyệt • NĐ 147/2024</x-badge>
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-400">
                    <span class="hidden md:inline text-slate-500">Phím tắt: Alt+A (Duyệt) • Alt+R (Từ chối)</span>
                    <a href="/" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1 min-h-[44px] py-2 px-1">
                        <span>Trang Độc Giả</span> <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-6">
                    <x-alert type="success">{{ session('success') }}</x-alert>
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-6">
                    <x-alert type="warning">{{ session('warning') }}</x-alert>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6">
                    <x-alert type="error">{{ session('error') }}</x-alert>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
