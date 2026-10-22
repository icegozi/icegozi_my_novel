<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $novel->title }} - Thư Viện Tiểu Thuyết 3D</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen pb-24">
    <!-- Top Nav -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-xl tracking-wider text-white">NOVEL ENGINE</span>
            </a>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="{{ route('reader.novels.exhibition', $novel->slug) }}" class="px-4 py-2 rounded-xl bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600/30 transition border border-indigo-500/30 flex items-center gap-2">
                    <span>✨</span> Phòng Trưng Bày 3D
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 pt-10 space-y-12">
        <!-- Novel Hero Section -->
        <div class="bg-slate-900/40 border border-slate-800/80 rounded-3xl p-8 backdrop-blur grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            <div class="md:col-span-4 lg:col-span-3">
                <div class="w-full aspect-[3/4] rounded-2xl bg-gradient-to-tr from-indigo-900/40 to-purple-900/40 border border-slate-800 flex items-center justify-center overflow-hidden shadow-2xl relative group">
                    @if ($novel->cover_image)
                        <img src="{{ $novel->cover_image }}" alt="{{ $novel->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-6">
                            <span class="font-heading text-4xl text-indigo-400 font-bold block mb-2">3D</span>
                            <span class="text-xs text-slate-400 font-medium">Tiểu Thuyết Tương Tác</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="md:col-span-8 lg:col-span-9 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($novel->genres as $genre)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            {{ $genre->name }}
                        </span>
                    @endforeach

                    <!-- Age Rating Badge -->
                    @php
                        $ageMap = [
                            '13_plus' => '13+',
                            '16_plus' => '16+',
                            '18_plus' => '18+',
                            'all' => 'Mọi lứa tuổi',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        {{ $ageMap[$novel->age_rating] ?? $novel->age_rating }}
                    </span>

                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Bản quyền: {{ $novel->copyright_type }}
                    </span>
                </div>

                <h1 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight">{{ $novel->title }}</h1>

                <div class="text-sm text-slate-400 flex items-center gap-2">
                    <span>Tác giả:</span>
                    <span class="font-bold text-slate-200">{{ $novel->author->authorProfile->pen_name ?? $novel->author->name }}</span>
                    <span>•</span>
                    <span>{{ $novel->total_chapters }} chương</span>
                    <span>•</span>
                    <span>{{ number_format($novel->total_words) }} chữ</span>
                </div>

                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h3>
                    <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">{{ $novel->synopsis }}</p>
                </div>

                @if ($novel->chapters->isNotEmpty())
                    <div class="pt-4 flex gap-4">
                        <a href="{{ route('reader.chapters.read', [$novel->slug, $novel->chapters->first()->chapter_number]) }}"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-indigo-500/25 transition flex items-center gap-2">
                            <span>📖</span> Đọc Từ Đầu ({{ $novel->chapters->first()->title }})
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3D Assets Showcase in this Novel -->
        @if ($novel->assets3d->isNotEmpty())
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-indigo-400 font-bold text-lg">✨</span>
                        <h2 class="text-lg font-bold text-white tracking-tight">Kho Mô Hình 3D Cụ Hóa Trong Tác Phẩm</h2>
                    </div>
                    <a href="{{ route('reader.novels.exhibition', $novel->slug) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                        Xem Phòng Trưng Bày 360° &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($novel->assets3d as $asset)
                        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5 hover:border-indigo-500/40 transition">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 capitalize">
                                    {{ $asset->asset_type }}
                                </span>
                                @if ($asset->particle_effect !== 'none')
                                    <span class="text-xs text-amber-300 font-mono">✨ {{ $asset->particle_effect }}</span>
                                @endif
                            </div>
                            <h3 class="font-bold text-white text-sm mb-1">{{ $asset->name }}</h3>
                            <p class="text-xs text-slate-400 line-clamp-2">{{ $asset->description ?? 'Mô hình bảo vật 3D tương tác.' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Table of Contents -->
        <div class="space-y-4">
            <div class="border-b border-slate-800/80 pb-3">
                <h2 class="text-lg font-bold text-white tracking-tight">Mục Lục Các Chương ({{ $novel->chapters->count() }})</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @forelse ($novel->chapters as $chapter)
                    <a href="{{ route('reader.chapters.read', [$novel->slug, $chapter->chapter_number]) }}"
                        class="p-4 rounded-2xl bg-slate-900/30 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-900/60 transition flex items-center justify-between text-xs">
                        <span class="font-medium text-slate-200 truncate pr-4">{{ $chapter->title }}</span>
                        <span class="text-slate-500 shrink-0">{{ number_format($chapter->word_count) }} chữ</span>
                    </a>
                @empty
                    <div class="col-span-2 text-center py-8 text-slate-500 text-sm">
                        Chưa có chương nào được phát hành.
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</body>
</html>
