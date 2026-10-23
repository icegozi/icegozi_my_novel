<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thẩm Định Định Danh Tác Giả e-KYC - Quản Trị Hệ Thống</title>
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
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Bảo Vệ Dữ Liệu e-KYC • NĐ 13/2023/NĐ-CP
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
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Thẩm Định Hồ Sơ Định Danh Tác Giả (e-KYC)</h1>
                <p class="text-sm text-slate-400 mt-1">Tuân thủ nghiêm ngặt Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân: File ảnh CCCD sẽ tự động xóa vĩnh viễn ngay sau khi thẩm định.</p>
            </div>
            <div class="text-xs text-slate-400 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
                Hồ sơ đang chờ duyệt: <strong class="text-amber-400 font-bold">{{ $profiles->count() }}</strong>
            </div>
        </div>

        @if($profiles->isEmpty())
            <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800">
                <span class="text-4xl block mb-2">🎉</span>
                <h3 class="text-lg font-bold text-white">Không còn hồ sơ e-KYC tồn đọng!</h3>
                <p class="text-xs text-slate-400 mt-1">Tất cả đơn đăng ký định danh tác giả đều đã được xử lý.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($profiles as $p)
                    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Chờ Thẩm Định
                                </span>
                                <span class="text-xs text-slate-400">Tài khoản: {{ $p->user?->email }}</span>
                                <span class="text-slate-600">•</span>
                                <span class="text-xs text-slate-500">{{ $p->created_at->diffForHumans() }}</span>
                            </div>

                            <h3 class="text-base font-bold text-white">
                                Bút danh: <span class="text-indigo-400">{{ $p->pen_name }}</span>
                                <span class="text-slate-500 font-normal text-sm ml-2">({{ $p->legal_full_name }})</span>
                            </h3>

                            <div class="flex items-center gap-4 text-xs text-slate-400">
                                <span>Mã số thuế: <strong class="text-slate-300">{{ $p->tax_code ?? 'Chưa nộp' }}</strong></span>
                                <span>Ngân hàng: <strong class="text-slate-300">{{ $p->bank_name ?? 'N/A' }}</strong></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end md:self-center">
                            <a href="{{ route('admin.kyc.show', $p) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                                <span>Thẩm Định Hồ Sơ &rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</body>
</html>
