@extends('layouts.reader', [
    'title' => 'Báo Cáo Vi Phạm Bản Quyền & Nội Dung - Novel Engine',
    'metaDescription' => 'Cổng tiếp nhận khiếu nại bản quyền và nội dung vi phạm theo Nghị định 17/2023/NĐ-CP.'
])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
    <!-- Header Hero -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 dark:bg-rose-500/20 sepia:bg-rose-900/15 text-rose-700 dark:text-rose-400 sepia:text-rose-900 border border-rose-500/25 sepia:border-rose-900/30 mb-3">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            Cổng Tiếp Nhận Khiếu Nại Bản Quyền Số • NĐ 17/2023/NĐ-CP
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
            Yêu Cầu Gỡ Bỏ Nội Dung Vi Phạm Bản Quyền
        </h1>
        <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs sm:text-sm mt-2 leading-relaxed">
            Áp dụng đối với chủ sở hữu quyền tác giả hoặc đại diện được ủy quyền hợp pháp. Người khiếu nại chịu trách nhiệm pháp lý trước pháp luật về tính trung thực của các thông tin và tài liệu cung cấp.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm flex items-start gap-3">
            <span class="text-lg">✅</span>
            <div>
                <h4 class="font-bold text-emerald-800 dark:text-emerald-300">Tiếp nhận thành công!</h4>
                <p class="mt-0.5 text-xs text-emerald-700/90 dark:text-emerald-400/90 leading-relaxed">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('legal.reports.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Block 1: Target info -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <span class="w-6 h-6 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-xs flex items-center justify-center font-bold">1</span>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 font-heading">
                    Thông Tin Đối Tượng Bị Khiếu Nại
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="reportable_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Loại đối tượng:</label>
                    <select id="reportable_type" name="reportable_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-200 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800">
                        <option value="Novel" selected>Tác phẩm / Bộ tiểu thuyết</option>
                        <option value="Chapter">Chương truyện cụ thể</option>
                    </select>
                </div>

                <div>
                    <label for="reportable_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Mã định danh (ID tác phẩm / chương):</label>
                    <input type="number" id="reportable_id" name="reportable_id" value="{{ $novel?->id ?? old('reportable_id', '') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-200 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800" placeholder="VD: 12">
                </div>
            </div>

            @if($novel)
                <div class="p-3.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-800 dark:text-indigo-300 sepia:text-amber-950 sepia:bg-sepia-100 sepia:border-sepia-border">
                    Đang khiếu nại tác phẩm: <strong class="font-bold">{{ $novel->title }}</strong> (Tác giả: {{ $novel->author?->authorProfile?->pen_name ?? $novel->author?->name }})
                </div>
            @endif
        </div>

        <!-- Block 2: Evidence and Legal grounds -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <span class="w-6 h-6 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-xs flex items-center justify-center font-bold">2</span>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 font-heading">
                    Căn Cứ Khiếu Nại & Bằng Chứng Hợp Pháp
                </h2>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Email người yêu cầu (để gửi thông báo pháp lý & biên nhận):</label>
                <input type="email" name="reporter_email" value="{{ old('reporter_email') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-200 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800" placeholder="legal@authorcompany.com">
            </div>

            <div>
                <label for="reason" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Phân loại vi phạm:</label>
                <select id="reason" name="violation_category" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-200 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800">
                    <option value="copyright_infringement" selected>Xâm phạm quyền tác giả (sao chép, đạo văn không phép)</option>
                    <option value="defamation">Bôi nhọ, xúc phạm uy tín, danh dự</option>
                    <option value="pornography">Nội dung khiêu dâm, vi phạm thuần phong mỹ tục</option>
                    <option value="violence_hate_speech">Kích động bạo lực, thù hận</option>
                    <option value="violates_national_security">Vi phạm an ninh quốc gia</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Mô tả hành vi xâm phạm và đối chiếu tác phẩm gốc:</label>
                <textarea name="description" rows="4" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-200 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 leading-relaxed" placeholder="Nêu rõ các đoạn trích, chương hồi, hoặc tài liệu chứng minh quyền tác giả…"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Liên kết tài liệu chứng cứ chứng minh quyền tác giả (Google Drive/PDF công khai):</label>
                <input type="url" name="evidence_urls[]" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs text-slate-900 dark:text-slate-200 sepia:text-sepia-text focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800" placeholder="https://example.com/chung-nhan-ban-quyen.pdf">
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('legal.terms') }}" class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text flex items-center gap-1.5 transition-colors">
                <span aria-hidden="true">&larr;</span> Xem Quy chế bản quyền
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 sepia:bg-rose-800 sepia:hover:bg-rose-700 text-white shadow-lg shadow-rose-600/25 transition-colors">
                Nộp Yêu Cầu Xử Lý Bản Quyền
            </button>
        </div>
    </form>
</div>
@endsection
