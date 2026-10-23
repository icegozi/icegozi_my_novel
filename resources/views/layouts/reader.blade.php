<!DOCTYPE html>
<html lang="vi" class="sepia">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f5efe6">

    <meta name="theme-manager" content="active">
    <title>{{ $title ?? 'Novel Engine - Nền Tảng Đọc Tiểu Thuyết & Comic 3D' }}</title>
    @if(isset($metaDescription))
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    <!-- Google Fonts with Vietnamese diacritics support -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Inter:wght@400;500;600;700&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300;1,400&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Client Theme Init (blocking to prevent flash of wrong theme) -->
    <script>
        (function() {
            var theme = localStorage.getItem('novel_reader_theme') || 'sepia';
            document.documentElement.className = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col font-sans antialiased text-slate-900 dark:text-slate-100 sepia:text-sepia-text bg-[#f5efe6] dark:bg-[#0b0f19] sepia:bg-[#f5efe6] transition-colors">
    <!-- Accessibility Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 z-50 px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow-xl">
        Chuyển đến nội dung chính
    </a>

    @include('layouts.partials.header')

    <main id="main-content" class="flex-1 focus:outline-none" tabindex="-1">
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <x-alert type="success">{{ session('success') }}</x-alert>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <x-alert type="error">{{ session('error') }}</x-alert>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none *:pointer-events-auto" aria-live="polite"></div>

    @stack('scripts')
</body>
</html>
