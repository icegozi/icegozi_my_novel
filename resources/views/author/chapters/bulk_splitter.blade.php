@extends('layouts.reader', [
    'title' => 'Tách Chương Tự Động Hàng Loạt - ' . $novel->title . ' - Novel Engine',
    'metaDescription' => 'Công cụ nhận diện và tách chương tự động hàng loạt từ văn bản thô.'
])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 space-y-8">
    <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 transition-colors">
        <div class="mb-6 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60 pb-5">
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('author.novels.index') }}" class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted hover:text-indigo-600 dark:hover:text-white sepia:hover:text-sepia-text flex items-center gap-1 transition-colors">
                    <span aria-hidden="true">&larr;</span> Quay lại danh sách truyện
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
                Tách Chương Tự Động Hàng Loạt: {{ $novel->title }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 sepia:text-sepia-muted mt-1 leading-relaxed">
                Dán toàn bộ văn bản tiểu thuyết gồm hàng chục hoặc hàng trăm chương. Hệ thống sẽ nhận diện tự động tiêu đề theo định dạng <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 font-mono text-xs">Chương X: [Tên]</code>, <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 font-mono text-xs">Hồi X - [Tên]</code> hoặc <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 font-mono text-xs">Chapter X</code> và bóc tách thành các bản nháp riêng biệt.
            </p>
        </div>

        <form action="{{ route('author.novels.bulk-chapters.store', $novel) }}" method="POST" id="bulkForm">
            @csrf

            <div class="mb-6">
                <label for="raw_text" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text uppercase tracking-wider mb-2">
                    Nội dung văn bản gộp: <span class="text-rose-500">*</span>
                </label>
                <textarea id="raw_text" name="raw_text" rows="16" required
                    class="w-full bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input font-mono text-xs sm:text-sm text-slate-900 dark:text-slate-100 sepia:text-sepia-text rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 focus:outline-none p-4 leading-relaxed"
                    placeholder="Dán toàn bộ văn bản tại đây...&#10;&#10;Chương 1: Khởi đầu thế giới mới&#10;Nội dung chương 1...&#10;&#10;Chương 2: Thức tỉnh huyết mạch&#10;Nội dung chương 2...">{{ old('raw_text') }}</textarea>
                @error('raw_text')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <button type="button" id="btnPreview"
                    class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 dark:bg-slate-800 sepia:bg-sepia-100 hover:bg-slate-200 dark:hover:bg-slate-700 sepia:hover:bg-sepia-200 text-slate-800 dark:text-slate-200 sepia:text-sepia-text text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 sepia:border-sepia-border transition shadow-sm">
                    Xem trước phân tách
                </button>

                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md sepia:shadow-amber-900/20 transition">
                    Lưu tất cả vào bản nháp
                </button>
            </div>
        </form>

        <!-- Preview Container -->
        <div id="previewContainer" class="mt-8 hidden">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 sepia:text-sepia-text">
                    Kết quả nhận diện: <span id="previewCount" class="text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 font-bold">0</span> chương
                </h2>
            </div>
            <div id="previewList" class="space-y-3 max-h-96 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-950/80 sepia:bg-sepia-input/70 rounded-2xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border">
                <!-- Dynamic preview cards injected here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
            card.className = 'p-4 bg-white dark:bg-slate-900 sepia:bg-sepia-card rounded-xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-xs';
            card.innerHTML = `
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-slate-900 dark:text-white sepia:text-sepia-text">${ch.title}</span>
                    <span class="text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 px-2 py-0.5 rounded font-semibold">${ch.word_count} từ</span>
                </div>
                <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted line-clamp-2">${ch.content.substring(0, 180)}...</p>
            `;
            list.appendChild(card);
        });

        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth' });
    });
</script>
@endpush
