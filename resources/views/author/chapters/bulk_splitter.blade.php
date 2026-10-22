<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tách Chương Tự Động Hàng Loạt: ') }} {{ $novel->title }}
            </h2>
            <a href="{{ route('author.novels.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; Quay lại danh sách truyện
            </a>
        </div>
    </x-slot>

    <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
            <div class="mb-6 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-bold text-gray-900">Công cụ bóc tách chương thông minh</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Dán toàn bộ văn bản tiểu thuyết gồm hàng chục hoặc hàng trăm chương. Hệ thống sẽ nhận diện tự động tiêu đề theo định dạng <code>Chương X: [Tên]</code>, <code>Hồi X - [Tên]</code> hoặc <code>Chapter X</code> và bóc tách thành các bản nháp riêng biệt.
                </p>
            </div>

            <form action="{{ route('author.novels.bulk-chapters.store', $novel) }}" method="POST" id="bulkForm">
                @csrf

                <div class="mb-6">
                    <label for="raw_text" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nội dung văn bản gộp:
                    </label>
                    <textarea id="raw_text" name="raw_text" rows="16" required
                        class="w-full font-mono text-sm rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-4"
                        placeholder="Dán toàn bộ văn bản tại đây...&#10;&#10;Chương 1: Khởi đầu thế giới mới&#10;Nội dung chương 1...&#10;&#10;Chương 2: Thức tỉnh huyết mạch&#10;Nội dung chương 2...">{{ old('raw_text') }}</textarea>
                    @error('raw_text')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <button type="button" id="btnPreview"
                        class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-xl transition duration-150 ease-in-out shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Xem trước phân tách
                    </button>

                    <button type="submit"
                        class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition duration-150 ease-in-out">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Lưu tất cả vào bản nháp
                    </button>
                </div>
            </form>

            <!-- Preview Container -->
            <div id="previewContainer" class="mt-8 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-bold text-gray-900">
                        Kết quả nhận diện: <span id="previewCount" class="text-indigo-600">0</span> chương
                    </h4>
                </div>
                <div id="previewList" class="space-y-4 max-h-96 overflow-y-auto p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <!-- Dynamic preview cards injected here -->
                </div>
            </div>
        </div>
    </div>

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
                card.className = 'p-4 bg-white rounded-lg shadow-sm border border-gray-200';
                card.innerHTML = `
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-gray-800 text-sm">${ch.title}</span>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">${ch.word_count} từ</span>
                    </div>
                    <p class="text-xs text-gray-600 line-clamp-2">${ch.content.substring(0, 180)}...</p>
                `;
                list.appendChild(card);
            });

            container.classList.remove('hidden');
            container.scrollIntoView({ behavior: 'smooth' });
        });
    </script>
</x-app-layout>
