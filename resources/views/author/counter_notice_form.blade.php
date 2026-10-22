<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo Phản Đối Khiếu Nại Bản Quyền (Counter-Notice) - Nghị Định 17/2023/NĐ-CP</title>
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
                <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">AUTHOR STUDIO</span>
            </a>
            <a href="{{ route('author.novels.index') }}" class="text-xs text-slate-400 hover:text-indigo-400 transition-colors">
                &larr; Quay lại danh sách truyện
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-3">
                Cơ Chế Bảo Vệ Tác Giả • NĐ 17/2023/NĐ-CP (Điều 114)
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white">Nộp Thông Báo Phản Đối (Counter-Notice)</h1>
            <p class="text-slate-400 text-sm mt-1 leading-relaxed">
                Tác phẩm của bạn đang tạm thời bị ẩn do có đơn khiếu nại bản quyền. Theo quy định pháp luật Việt Nam, bạn có thời hạn 10 ngày làm việc để gửi bản giải trình và bằng chứng chứng minh quyền tác giả hợp pháp.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <h4 class="font-semibold text-emerald-300">Đã nộp thành công!</h4>
                    <p class="mt-0.5 text-xs text-emerald-400/90">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800 mb-6 space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tác phẩm bị khiếu nại</h4>
            <div class="text-base font-bold text-white">{{ $novel?->title ?? 'Tác phẩm #' . $report->reportable_id }}</div>
            <div class="text-xs text-slate-400">
                Lý do khiếu nại từ bên thứ ba: <em class="text-slate-300">"{{ $report->description }}"</em>
            </div>
        </div>

        <form action="{{ route('author.counter-notice.store', $report) }}" method="POST" class="space-y-6">
            @csrf

            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-indigo-400">Giải Trình & Căn Cứ Hợp Pháp</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nội dung văn bản phản đối & giải trình:</label>
                    <textarea name="counter_notice_text" rows="5" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 leading-relaxed" placeholder="Tôi cam đoan đây là tác phẩm gốc do tôi sáng tác, hoặc tôi có giấy phép phát hành độc quyền..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Liên kết tài liệu chứng cứ (Giấy đăng ký QTG, hợp đồng ủy quyền, bản thảo gốc):</label>
                    <input type="url" name="counter_notice_evidence_urls[]" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500" placeholder="https://drive.google.com/file/d/your-evidence/view">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('author.novels.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Bỏ qua lúc này</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition-all">
                    Gửi Thông Báo Phản Đối (Counter-Notice)
                </button>
            </div>
        </form>
    </main>
</body>
</html>
