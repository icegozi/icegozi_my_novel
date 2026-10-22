@inject('highlighter', 'App\Services\WordHighlighterService')

@php
    $highlightedContent = $highlighter->highlight($content ?? '');
    $warningCount = substr_count($highlightedContent, '<mark');
@endphp

<div class="space-y-4">
    <!-- Screening Bar -->
    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
            @if($warningCount > 0)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-300 border border-rose-500/30 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    Phát hiện {{ $warningCount }} vị trí chứa từ ngữ nhạy cảm
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                    ✓ Không phát hiện từ khóa trong bộ lọc cấm
                </span>
            @endif
        </div>
        <div class="text-slate-400 text-[11px] flex items-center gap-2">
            <span>Chú giải:</span>
            <span class="bg-rose-500/30 text-rose-300 font-semibold px-1 rounded border border-rose-500/50">Từ nhạy cảm</span>
        </div>
    </div>

    <!-- Rendered content -->
    <div class="prose prose-invert prose-slate max-w-none font-reader text-base md:text-lg text-slate-300 leading-relaxed space-y-4">
        {!! $highlightedContent !!}
    </div>
</div>
