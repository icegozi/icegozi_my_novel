@extends('layouts.admin', ['title' => 'Bảng Điều Khiển Quản Trị - Novel Engine'])

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold font-heading text-white">Bảng Điều Khiển Tổng Quan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Giám sát hoạt động xuất bản, số liệu tăng trưởng và hàng đợi xử lý vi phạm.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.moderation.queue') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-colors flex items-center gap-2 min-h-[44px]">
                <span aria-hidden="true">🛡️</span>
                <span>Hàng Đợi Duyệt</span>
                @if($stats['pending_novels'] + $stats['pending_chapters'] > 0)
                    <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-bold">
                        {{ $stats['pending_novels'] + $stats['pending_chapters'] }}
                    </span>
                @endif
            </a>
            <a href="{{ route('admin.kyc.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors flex items-center gap-2 min-h-[44px]">
                <span aria-hidden="true">🪪</span>
                <span>Hồ Sơ e-KYC</span>
                @if($stats['pending_kyc'] > 0)
                    <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-bold">
                        {{ $stats['pending_kyc'] }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Novels -->
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Tổng Tác Phẩm</span>
                <span class="text-base" aria-hidden="true">📚</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-white font-heading">
                {{ number_format($stats['total_novels']) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="text-emerald-400 font-semibold">{{ $stats['published_novels'] }} đã xuất bản</span>
                <span class="text-slate-600" aria-hidden="true">•</span>
                <span class="text-amber-400 font-semibold">{{ $stats['pending_novels'] }} Chờ Duyệt Xuất Bản</span>
            </div>
        </div>

        <!-- Card 2: Chapters -->
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Tổng Chương Truyện</span>
                <span class="text-base" aria-hidden="true">📖</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-white font-heading">
                {{ number_format($stats['total_chapters']) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="text-amber-400 font-semibold">{{ $stats['pending_chapters'] }} chương chờ duyệt</span>
            </div>
        </div>

        <!-- Card 3: Authors -->
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Tác Giả Định Danh</span>
                <span class="text-base" aria-hidden="true">✍️</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-white font-heading">
                {{ number_format($stats['verified_authors']) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="text-rose-400 font-semibold">{{ $stats['pending_kyc'] }} Hồ Sơ e-KYC Chờ</span>
            </div>
        </div>

        <!-- Card 4: Reports / Compliance -->
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Báo Cáo Vi Phạm</span>
                <span class="text-base" aria-hidden="true">⚖️</span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-white font-heading">
                {{ number_format($stats['pending_reports']) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <span class="text-amber-400 font-semibold">NĐ 17/2023 & NĐ 147/2024</span>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Novels & Live Audit Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Col 1: Recent Novels -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-4">
                <h2 class="text-lg font-bold font-heading text-white">Tác Phẩm Mới Cập Nhật</h2>
                <a href="/admin/tac-pham" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Xem tất cả &rarr;</a>
            </div>

            @if($recentNovels->isEmpty())
                <div class="text-center py-8 text-xs text-slate-500">
                    Chưa có tác phẩm nào trên hệ thống.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentNovels as $n)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800/60 hover:border-slate-700/60 transition-colors">
                            <div class="min-w-0 pr-3">
                                <h3 class="text-sm font-bold text-white truncate">{{ $n->title }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Tác giả: <span class="text-indigo-400">{{ $n->author?->authorProfile?->pen_name ?? $n->author?->name }}</span>
                                    <span class="text-slate-600" aria-hidden="true">•</span>
                                    <span>{{ $n->created_at->diffForHumans() }}</span>
                                </p>
                            </div>
                            <span class="shrink-0 px-2 py-0.5 rounded text-[11px] font-semibold {{ $n->status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                {{ $n->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Col 2: Live Audit Trail -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-4">
                <h2 class="text-lg font-bold font-heading text-white">Nhật Ký An Ninh Mới Nhất</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Toàn bộ log &rarr;</a>
            </div>

            @if($recentAuditLogs->isEmpty())
                <div class="text-center py-8 text-xs text-slate-500">
                    Chưa ghi nhận sự kiện bảo mật nào.
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($recentAuditLogs as $log)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/60 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-1.5 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-800 text-slate-300">
                                    {{ $log->action }}
                                </span>
                                <span class="text-slate-300 truncate">
                                    {{ $log->target_type }} #{{ $log->target_id }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 shrink-0 font-mono">
                                {{ $log->created_at->format('H:i d/m') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
