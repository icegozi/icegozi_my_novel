<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tác Phẩm - Author Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen">
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-xl tracking-wider text-white">AUTHOR STUDIO</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/tac-gia/truyen/create" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <span>+</span> Tạo Tác Phẩm Mới
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-10">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white">Danh Sách Tác Phẩm Của Bạn</h1>
                <p class="text-slate-400 text-sm mt-1">Quản lý nội dung, xuất bản chương và gắn hiệu ứng 3D cụ hóa trực quan.</p>
            </div>
        </div>

        @if($novels->isEmpty())
            <div class="text-center py-16 bg-slate-900/40 rounded-2xl border border-slate-800">
                <span class="text-4xl">📚</span>
                <h3 class="text-base font-semibold text-white mt-3">Chưa có tác phẩm nào</h3>
                <p class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">Bắt đầu hành trình sáng tác số ngay hôm nay với công cụ soạn thảo siêu mượt.</p>
                <a href="/tac-gia/truyen/create" class="mt-4 inline-block px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-all">
                    Tạo Tác Phẩm Mới &rarr;
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($novels as $novel)
                    <div class="bg-slate-900/40 rounded-2xl border border-slate-800 p-5 hover:border-slate-700 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-start gap-4 mb-4">
                                @if($novel->cover_image)
                                    <img src="{{ Storage::url($novel->cover_image) }}" alt="{{ $novel->title }}" class="w-16 h-22 object-cover rounded-lg border border-slate-700 shadow-md">
                                @else
                                    <div class="w-16 h-22 bg-slate-800 rounded-lg flex items-center justify-center text-2xl border border-slate-700">📖</div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-1">
                                        {{ $novel->age_rating }}
                                    </span>
                                    <h3 class="font-bold text-white text-base truncate">{{ $novel->title }}</h3>
                                    <p class="text-xs text-slate-400 line-clamp-2 mt-1">{{ $novel->synopsis }}</p>
                                </div>
                            </div>
                            <div class="text-xs text-slate-400 space-y-1 py-3 border-t border-slate-800/80">
                                <div class="flex justify-between">
                                    <span>Số chương:</span>
                                    <strong class="text-slate-200">{{ $novel->chapters->count() }}</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>Bản quyền:</span>
                                    <span class="text-emerald-400 uppercase font-mono text-[11px]">{{ $novel->copyright_type }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between gap-2">
                            <a href="/tac-gia/truyen/{{ $novel->id }}/edit" class="flex-1 text-center py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 font-medium transition-all">
                                Chỉnh Sửa
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $novels->links() }}
            </div>
        @endif
    </main>
</body>
</html>
