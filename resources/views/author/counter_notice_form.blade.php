@extends('layouts.reader', [
    'title' => 'Thông Báo Phản Đối Khiếu Nại Bản Quyền (Counter-Notice) - Novel Engine',
    'metaDescription' => 'Nộp thông báo phản đối giải trình quyền tác giả hợp pháp theo Nghị định 17/2023/NĐ-CP.'
])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
    <!-- Header Banner -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 dark:bg-amber-500/20 sepia:bg-amber-900/15 text-amber-800 dark:text-amber-400 sepia:text-amber-900 border border-amber-500/25 sepia:border-amber-800/30 mb-3">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            Cơ Chế Bảo Vệ Tác Giả • NĐ 17/2023/NĐ-CP (Điều 114)
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
            Nộp Thông Báo Phản Đối (Counter-Notice)
        </h1>
        <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs sm:text-sm mt-2 leading-relaxed">
            Tác phẩm của bạn đang tạm thời bị ẩn do có đơn khiếu nại bản quyền. Theo quy định pháp luật Việt Nam, bạn có thời hạn 10 ngày làm việc để gửi bản giải trình và bằng chứng chứng minh quyền tác giả hợp pháp.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm flex items-start gap-3">
            <span class="text-lg">✅</span>
            <div>
                <h4 class="font-bold text-emerald-800 dark:text-emerald-300">Đã nộp thành công!</h4>
                <p class="mt-0.5 text-xs text-emerald-700/90 dark:text-emerald-400/90 leading-relaxed">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Tác phẩm bị khiếu nại -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 mb-6 space-y-2 transition-colors">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 sepia:text-sepia-muted">
            Tác phẩm bị khiếu nại
        </h2>
        <div class="text-base sm:text-lg font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text">
            {{ $novel?->title ?? 'Tác phẩm #' . $report->reportable_id }}
        </div>
        <div class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted leading-relaxed">
            Lý do khiếu nại từ bên thứ ba: <em class="text-slate-800 dark:text-slate-200 sepia:text-sepia-text font-medium">"{{ $report->description }}"</em>
        </div>
    </div>

    <form action="{{ route('author.counter-notice.store', $report) }}" method="POST" class="space-y-6">
        @csrf

        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <span class="w-6 h-6 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-xs flex items-center justify-center font-bold">1</span>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 font-heading">
                    Giải Trình & Căn Cứ Hợp Pháp
                </h2>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                    Nội dung văn bản phản đối & giải trình: <span class="text-rose-500">*</span>
                </label>
                <textarea name="counter_notice_text" rows="5" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 leading-relaxed" placeholder="Tôi cam đoan đây là tác phẩm gốc do tôi sáng tác, hoặc tôi có giấy phép phát hành độc quyền..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                    Liên kết tài liệu chứng cứ (Giấy đăng ký QTG, hợp đồng ủy quyền, bản thảo gốc):
                </label>
                <input type="url" name="counter_notice_evidence_urls[]" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-100 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800" placeholder="https://drive.google.com/file/d/your-evidence/view">
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('author.novels.index') }}" class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text flex items-center gap-1.5 transition-colors">
                <span aria-hidden="true">&larr;</span> Bỏ qua lúc này
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs sm:text-sm font-bold bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white shadow-md sepia:shadow-amber-900/20 transition-all">
                Gửi Thông Báo Phản Đối (Counter-Notice)
            </button>
        </div>
    </form>
</div>
@endsection
