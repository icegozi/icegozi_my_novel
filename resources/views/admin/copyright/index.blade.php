<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khiếu Nại Bản Quyền Số - Quản Trị Hệ Thống</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="/" class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                    <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL ADMIN</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                    Cổng Xử Lý Bản Quyền • NĐ 17/2023/NĐ-CP
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="{{ route('admin.moderation.queue') }}" class="text-slate-400 hover:text-white">
                    &larr; Hàng Đợi Kiểm Duyệt
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Xử Lý Khiếu Nại Bản Quyền Số & Vi Phạm</h1>
                <p class="text-sm text-slate-400 mt-1">Quy trình Thông báo và Tháo dỡ (Notice-and-Takedown) tuân thủ Nghị định 17/2023/NĐ-CP.</p>
            </div>
            <!-- Status filters -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <a href="{{ route('admin.copyright.index') }}" class="px-3 py-1.5 rounded-xl font-medium {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Tất cả</a>
                <a href="{{ route('admin.copyright.index', ['status' => 'received']) }}" class="px-3 py-1.5 rounded-xl font-medium {{ request('status') === 'received' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Tiếp nhận mới</a>
                <a href="{{ route('admin.copyright.index', ['status' => 'temporary_takedown']) }}" class="px-3 py-1.5 rounded-xl font-medium {{ request('status') === 'temporary_takedown' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Đang tạm ẩn</a>
                <a href="{{ route('admin.copyright.index', ['status' => 'under_review']) }}" class="px-3 py-1.5 rounded-xl font-medium {{ request('status') === 'under_review' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Có phản đối</a>
                <a href="{{ route('admin.copyright.index', ['status' => 'resolved']) }}" class="px-3 py-1.5 rounded-xl font-medium {{ request('status') === 'resolved' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Đã giải quyết</a>
            </div>
        </div>

        @if($reports->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800">
                <span class="text-4xl block mb-2">🛡️</span>
                <h3 class="text-lg font-bold text-white">Không có đơn khiếu nại nào</h3>
                <p class="text-xs text-slate-400 mt-1">Hiện tại không có tranh chấp bản quyền hoặc đơn vi phạm nào trong danh mục này.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($reports as $report)
                    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold 
                                    @if($report->status === 'received') bg-blue-500/10 text-blue-400 border border-blue-500/20
                                    @elseif($report->status === 'temporary_takedown') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @elseif($report->status === 'under_review') bg-purple-500/10 text-purple-400 border border-purple-500/20
                                    @elseif($report->status === 'resolved') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @else bg-slate-500/10 text-slate-400 border border-slate-500/20 @endif">
                                    {{ strtoupper($report->status) }}
                                </span>
                                <span class="text-xs text-slate-400">Đơn #{{ $report->id }}</span>
                                <span class="text-slate-600">•</span>
                                <span class="text-xs text-slate-300 font-semibold">{{ $report->reporter_email }}</span>
                                <span class="text-slate-600">•</span>
                                <span class="text-xs text-slate-500">{{ $report->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="text-sm text-slate-200">
                                Khiếu nại <strong class="text-indigo-400">{{ $report->reportable_type }} #{{ $report->reportable_id }}</strong>: 
                                <span class="text-slate-400 line-clamp-1">{{ $report->description }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end md:self-center">
                            <a href="{{ route('admin.copyright.show', $report) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all">
                                Xem Hồ Sơ & Đối Soát &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $reports->links() }}
            </div>
        @endif
    </main>
</body>
</html>
