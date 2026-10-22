<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tách Chương Tự Động Hàng Loạt - {{ $novel->title }} - Author Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen pb-16">
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-xl tracking-wider text-white">BULK SPLITTER</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('author.novels.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    &larr; Quay lại danh sách truyện
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 pt-10 space-y-8">
        <div class="bg-slate-900/50 backdrop-blur border border-slate-800/80 rounded-3xl p-8">
            <div class="mb-6 border-b border-slate-800 pb-4">
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Tách Chương Tự Động Hàng Loạt: {{ $novel->title }}</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Dán toàn bộ văn bản tiểu thuyết gồm hàng chục hoặc hàng trăm chương. Hệ thống sẽ nhận diện tự động tiêu đề theo định dạng <code>Chương X: [Tên]</code>, <code>Hồi X - [Tên]</code> hoặc <code>Chapter X</code> và bóc tách thành các bản nháp riêng biệt.
                </p>
            </div>

            <form action="{{ route('author.novels.bulk-chapters.store', $novel) }}" method="POST" id="bulkForm">
                @csrf

                <div class="mb-6">
                    <label for="raw_text" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Nội dung văn bản gộp:
                    </label>
                    <textarea id="raw_text" name="raw_text" rows="16" required
                        class="w-full bg-slate-950 font-mono text-sm text-slate-200 rounded-2xl border border-slate-800 focus:border-indigo-500 focus:outline-none p-4"
                        placeholder="Dán toàn bộ văn bản tại đây...&#10;&#10;Chương 1: Khởi đầu thế giới mới&#10;Nội dung chương 1...&#10;&#10;Chương 2: Thức tỉnh huyết mạch&#10;Nội dung chương 2...">{{ old('raw_text') }}</textarea>
                    @error('raw_text')
                        <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <button type="button" id="btnPreview"
                        class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-2xl transition shadow-sm">
                        Xem trước phân tách
                    </button>

                    <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold rounded-2xl shadow-lg shadow-indigo-500/25 transition">
                        Lưu tất cả vào bản nháp
                    </button>
                </div>
            </form>

            <!-- Preview Container -->
            <div id="previewContainer" class="mt-8 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-white">
                        Kết quả nhận diện: <span id="previewCount" class="text-indigo-400">0</span> chương
                    </h4>
                </div>
                <div id="previewList" class="space-y-4 max-h-96 overflow-y-auto p-4 bg-slate-950/80 rounded-2xl border border-slate-800">
                    <!-- Dynamic preview cards injected here -->
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('btnPreview').addEventListener('click', async () => {
            const rawText = document.getElementById('raw_text').value;
            if (!rawText.trim()) {
                alert('Vui lòng nhập hoặc dán nội dung trước khi xem trước.');
                return;
            }

            const res = await fetch("{{ route('author.novels.bulk-chapters.preview', $novel) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ raw_text: rawText })
            });

            if (!res.ok) {
                alert('Có lỗi xảy ra khi xem trước phân tách.');
                return;
            }

            const data = await res.json();
            const container = document.getElementById('previewContainer');
            const countSpan = document.getElementById('previewCount');
            const list = document.getElementById('previewList');

            countSpan.textContent = data.total;
            list.innerHTML = '';

            data.chapters.forEach(ch => {
                const card = document.createElement('div');
                card.className = 'p-4 bg-slate-900 rounded-xl border border-slate-800 text-xs';
                card.innerHTML = `
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-white">${ch.title}</span>
                        <span class="text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded">${ch.word_count} từ</span>
                    </div>
                    <p class="text-slate-400 line-clamp-2">${ch.content.substring(0, 180)}...</p>
                `;
                list.appendChild(card);
            });

            container.classList.remove('hidden');
            container.scrollIntoView({ behavior: 'smooth' });
        });
    </script>
</body>
</html>
