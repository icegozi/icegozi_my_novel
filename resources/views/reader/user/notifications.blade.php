@extends('layouts.reader')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 sepia:border-sepia-border pb-6 space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Trung Tâm Tin Nhắn</span>
        <h1 class="text-3xl font-heading font-black text-slate-900 dark:text-white sepia:text-sepia-text">Thông Báo</h1>
        <p class="text-xs text-slate-500">Cập nhật chương mới của tác phẩm bạn đang theo dõi và thông báo hệ thống</p>
    </div>

    <div class="space-y-4">
        @forelse ($notifications as $notif)
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border bg-white dark:bg-slate-900/40 sepia:bg-sepia-50 shadow-sm flex items-start gap-4 hover:border-indigo-500/40 transition">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0">
                    🔔
                </div>
                <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white sepia:text-sepia-text">{{ $notif['title'] }}</h4>
                        <span class="text-[11px] text-slate-400 tabular-nums">{{ $notif['time'] }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed">
                        {{ $notif['message'] }}
                    </p>
                    @if ($notif['link'])
                        <a href="{{ $notif['link'] }}" class="inline-block mt-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Xem chi tiết →
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-16 text-center text-slate-500 text-xs">
                Không có thông báo mới nào
            </div>
        @endforelse
    </div>
</div>
@endsection
