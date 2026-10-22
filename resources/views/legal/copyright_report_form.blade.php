<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo Vi Phạm Quyền Tác Giả & Nội Dung - Nghị Định 17/2023/NĐ-CP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL LEGAL</span>
            </a>
            <a href="/dieu-khoan-va-ban-quyen" class="text-xs text-slate-400 hover:text-indigo-400 transition-colors">
                Xem Quy Chế Bản Quyền &rarr;
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20 mb-3">
                Cổng Tiếp Nhận Khiếu Nại Bản Quyền Số • NĐ 17/2023/NĐ-CP
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white">Yêu Cầu Gỡ Bỏ Nội Dung Vi Phạm Bản Quyền</h1>
            <p class="text-slate-400 text-sm mt-1 leading-relaxed">
                Áp dụng đối với chủ sở hữu quyền tác giả hoặc đại diện được ủy quyền hợp pháp. Người khiếu nại phải chịu trách nhiệm pháp lý trước pháp luật về tính trung thực của các thông tin, tài liệu cung cấp.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <h4 class="font-semibold text-emerald-300">Tiếp nhận thành công!</h4>
                    <p class="mt-0.5 text-xs text-emerald-400/90">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('legal.reports.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400">1. Thông Tin Đối Tượng Bị Khiếu Nại</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Loại đối tượng:</label>
                        <select name="reportable_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                            <option value="Novel" selected>Tác phẩm / Bộ tiểu thuyết</option>
                            <option value="Chapter">Chương truyện cụ thể</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Mã định danh (ID tác phẩm / chương):</label>
                        <input type="number" name="reportable_id" value="{{ $novel?->id ?? old('reportable_id', '') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500" placeholder="VD: 12">
                    </div>
                </div>

                @if($novel)
                    <div class="p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-300">
                        Đang khiếu nại tác phẩm: <strong>{{ $novel->title }}</strong> (Tác giả: {{ $novel->author?->authorProfile?->pen_name ?? $novel->author?->name }})
                    </div>
                @endif
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400">2. Căn Cứ Khiếu Nại & Bằng Chứng</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email người yêu cầu (để gửi thông báo pháp lý):</label>
                    <input type="email" name="reporter_email" value="{{ old('reporter_email') }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500" placeholder="legal@authorcompany.com">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Phân loại vi phạm:</label>
                    <select name="violation_category" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="copyright_infringement" selected>Xâm phạm quyền tác giả (sao chép, đạo văn không phép)</option>
                        <option value="defamation">Bôi nhọ, xúc phạm uy tín, danh dự</option>
                        <option value="pornography">Nội dung khiêu dâm, vi phạm thuần phong mỹ tục</option>
                        <option value="violence_hate_speech">Kích động bạo lực, thù hận</option>
                        <option value="violates_national_security">Vi phạm an ninh quốc gia</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Mô tả hành vi xâm phạm và đối chiếu tác phẩm gốc:</label>
                    <textarea name="description" rows="4" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 leading-relaxed" placeholder="Nêu rõ các đoạn trích, chương hồi, hoặc tài liệu chứng minh quyền tác giả..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Liên kết tài liệu chứng cứ chứng minh quyền tác giả (Google Drive/PDF công khai):</label>
                    <input type="url" name="evidence_urls[]" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500" placeholder="https://example.com/chung-nhan-ban-quyen.pdf">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="/" class="text-xs text-slate-400 hover:text-white">&larr; Quay lại trang chủ</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/30 transition-all">
                    Nộp Yêu Cầu Xử Lý Bản Quyền
                </button>
            </div>
        </form>
    </main>
</body>
</html>
