<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Khiếu Nại Bản Quyền #{{ $report->id }} - Quản Trị Hệ Thống</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.copyright.index') }}" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs flex items-center gap-1.5 transition-all">
                    <span>&larr; Danh Sách Khiếu Nại</span>
                </a>
                <span class="text-sm font-bold text-white">Hồ Sơ Đối Soát Bản Quyền #{{ $report->id }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold 
                    @if($report->status === 'received') bg-blue-500/10 text-blue-400 border border-blue-500/20
                    @elseif($report->status === 'temporary_takedown') bg-amber-500/10 text-amber-400 border border-amber-500/20
                    @elseif($report->status === 'under_review') bg-purple-500/10 text-purple-400 border border-purple-500/20
                    @elseif($report->status === 'resolved') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                    @else bg-slate-500/10 text-slate-400 border border-slate-500/20 @endif">
                    {{ strtoupper($report->status) }}
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('warning') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 text-sm flex items-center gap-3">
                <span class="text-xl">ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- Accused Novel Box -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-400">Tác Phẩm Bị Khiếu Nại</h3>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $targetNovel?->title ?? 'Tác phẩm không tìm thấy' }}</h2>
                    <div class="flex items-center gap-4 text-xs text-slate-400 mt-1">
                        <span>Tác giả: <strong class="text-slate-200">{{ $targetNovel?->author?->authorProfile?->pen_name ?? $targetNovel?->author?->name }}</strong></span>
                        <span>Trạng thái xuất bản: <strong class="text-amber-400">{{ $targetNovel?->status }}</strong></span>
                        <span>Loại bản quyền: <strong class="text-purple-400">{{ $targetNovel?->copyright_type }}</strong></span>
                    </div>
                </div>
                @if($targetNovel)
                    <a href="{{ route('reader.novels.show', $targetNovel->slug) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs self-start md:self-auto">
                        Mở Xem Công Khai &rarr;
                    </a>
                @endif
            </div>
        </div>

        <!-- Complainant Claims & Evidence -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-400">1. Nội Dung Khiếu Nại Bản Quyền (Từ Bên Thứ Ba)</h3>
            <div class="space-y-2 text-xs">
                <div>Email người khiếu nại: <strong class="text-slate-200">{{ $report->reporter_email }}</strong></div>
                <div>Phân loại: <strong class="text-slate-300">{{ $report->violation_category }}</strong></div>
                <div>Thời gian tiếp nhận: <span class="text-slate-400">{{ $report->created_at->format('H:i d/m/Y') }}</span></div>
            </div>
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 text-sm text-slate-300 leading-relaxed">
                {{ $report->description }}
            </div>
            @if(!empty($report->evidence_urls))
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 mb-1.5">Tài liệu bằng chứng nộp kèm:</h4>
                    <ul class="list-disc list-inside text-xs text-indigo-400 space-y-1">
                        @foreach($report->evidence_urls as $url)
                            <li><a href="{{ $url }}" target="_blank" class="underline hover:text-indigo-300">{{ $url }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Author's Counter-Notice (if submitted) -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-400">2. Thông Báo Phản Đối Của Tác Giả (Counter-Notice)</h3>
            @if($report->counter_notice_text)
                <div class="space-y-2 text-xs text-slate-400">
                    <div>Thời điểm nộp phản đối: <strong class="text-slate-200">{{ $report->counter_submitted_at?->format('H:i d/m/Y') }}</strong></div>
                </div>
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 text-sm text-slate-300 leading-relaxed">
                    {{ $report->counter_notice_text }}
                </div>
                @if(!empty($report->counter_notice_evidence_urls))
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 mb-1.5">Chứng cứ phản biện của tác giả:</h4>
                        <ul class="list-disc list-inside text-xs text-emerald-400 space-y-1">
                            @foreach($report->counter_notice_evidence_urls as $url)
                                <li><a href="{{ $url }}" target="_blank" class="underline hover:text-emerald-300">{{ $url }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @else
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-500 italic">
                    Tác giả chưa nộp văn bản phản đối (Thời hạn luật định: 10 ngày làm việc kể từ thời điểm tạm ẩn tác phẩm).
                </div>
            @endif
        </div>

        <!-- Action Bar under NĐ 17/2023 -->
        <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-white">Quyết Định Xử Lý Của Kiểm Duyệt Viên</h4>
                <p class="text-xs text-slate-400 mt-0.5">Thao tác sẽ được lưu vào Nhật Ký An Ninh Mạng (Audit Log) theo NĐ 147/2024.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Tạm ẩn -->
                <form action="{{ route('admin.copyright.takedown', $report) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Xác nhận tạm ẩn tác phẩm theo quy định NĐ 17/2023?')" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-lg shadow-amber-600/20 transition-all">
                        Tạm Ẩn Tác Phẩm
                    </button>
                </form>

                <!-- Khôi phục -->
                <form action="{{ route('admin.copyright.restore', $report) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Xác nhận khôi phục tác phẩm công khai?')" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition-all">
                        Khôi Phục Tác Phẩm
                    </button>
                </form>

                <!-- Bác đơn -->
                <form action="{{ route('admin.copyright.dismiss', $report) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Xác nhận bác đơn khiếu nại do thiếu căn cứ pháp lý?')" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-all">
                        Bác Đơn Khiếu Nại
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
