@extends('layouts.admin', [
    'title' => 'Hồ Sơ Tác Giả - ' . $author->pen_name,
    'bodyAttributes' => 'x-data="{ statusModal: false, selectedStatus: \'' . ($author->user?->status ?? 'active') . '\' }"'
])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Back & Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.authors.index') }}"
               class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
               aria-label="Quay lại danh sách tác giả">
                <span aria-hidden="true">&larr;</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold font-heading text-white">{{ $author->pen_name }}</h1>
                <p class="text-xs text-slate-400 mt-0.5">Tên pháp lý: <strong class="text-slate-200">{{ $author->legal_full_name }}</strong></p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $author)
                <a href="{{ route('admin.authors.edit', $author) }}"
                   class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors flex items-center gap-2 min-h-[44px]">
                    <span aria-hidden="true">✏️</span> Sửa Thông Tin
                </a>
            @endcan

            @can('changeStatus', [App\Models\User::class, $author->user])
                <button type="button"
                        @click="statusModal = true"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 transition-colors flex items-center gap-2 min-h-[44px]">
                    <span aria-hidden="true">🛡️</span> Quản Lý Trạng Thái
                </button>
            @endcan
        </div>
    </div>

    <!-- Author Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1: Identity & Legal Info -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-4">
            <h2 class="text-base font-bold font-heading text-white pb-3 border-b border-slate-800">Thông Tin Định Danh</h2>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block">Bút danh:</span>
                    <strong class="text-slate-200 text-sm">{{ $author->pen_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Tên thật (CCCD):</span>
                    <strong class="text-slate-200">{{ $author->legal_full_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Tài khoản Email:</span>
                    <span class="font-mono text-slate-300">{{ $author->user?->email ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Mã số thuế cá nhân:</span>
                    <span class="font-mono text-slate-300">{{ $author->tax_code ?? 'Chưa đăng ký' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Ngân hàng thanh toán:</span>
                    <span class="text-slate-200">{{ $author->bank_name ?? 'Chưa thiết lập' }}</span>
                    @if($author->bank_account_number)
                        <div class="font-mono text-slate-400 text-[11px]">STK: {{ $author->bank_account_number }}</div>
                    @endif
                </div>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-slate-400">Trạng thái e-KYC:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $author->verification_status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                        {{ $author->verification_status }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Tài khoản User:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($author->user?->status ?? 'active') === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                        {{ $author->user?->status ?? 'active' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Col 2 & 3: Novels Portfolio -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h2 class="text-base font-bold font-heading text-white">Danh Mục Tác Phẩm Của Tác Giả ({{ $author->user?->novels?->count() ?? 0 }})</h2>
                <span class="text-xs text-slate-400">Tự động duy trì khi tác giả bị khóa</span>
            </div>

            @if(!$author->user || $author->user->novels->isEmpty())
                <div class="p-8 text-center text-xs text-slate-500">
                    Tác giả này chưa phát hành tác phẩm nào.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($author->user->novels as $novel)
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-white">{{ $novel->title }}</h3>
                                <div class="flex items-center gap-3 text-xs text-slate-400 mt-1">
                                    <span>{{ $novel->chapters_count }} chương</span>
                                    <span class="text-slate-600" aria-hidden="true">•</span>
                                    <span>{{ number_format($novel->view_count) }} lượt đọc</span>
                                    <span class="text-slate-600" aria-hidden="true">•</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $novel->status === 'published' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $novel->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="/stories/{{ $novel->slug }}" target="_blank"
                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors min-h-[44px] flex items-center">
                                    Xem Trang Đọc &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Status Change Modal -->
    <x-modal name="statusModal" title="Quản Lý Trạng Thái Hoạt Động Tác Giả">
        <p class="text-xs text-slate-400 mb-4 leading-relaxed">
            Thay đổi trạng thái tài khoản người dùng của tác giả. Khi bị <strong class="text-rose-400">Tạm Ngưng</strong> hoặc <strong class="text-rose-400">Khóa Vĩnh Viễn</strong>, tác giả không thể đăng nhập hoặc xuất bản bài viết mới, nhưng các tác phẩm đã xuất bản hợp lệ vẫn được giữ an toàn trên hệ thống.
        </p>

        <form action="{{ route('admin.authors.status', $author) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="status-select" class="block text-xs font-semibold text-slate-300 mb-1">Chọn trạng thái tài khoản:</label>
                <select id="status-select"
                        name="status"
                        x-model="selectedStatus"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-rose-500">
                    <option value="active">Hoạt động bình thường (Active)</option>
                    <option value="suspended">Tạm ngưng quyền xuất bản (Suspended)</option>
                    <option value="banned">Khóa vĩnh viễn tài khoản (Banned)</option>
                </select>
            </div>

            <div>
                <label for="status-reason" class="block text-xs font-semibold text-slate-300 mb-1">Lý do điều chỉnh (lưu vết Audit Log):</label>
                <textarea id="status-reason"
                          name="reason"
                          rows="3"
                          placeholder="Nhập lý do khóa hoặc phục hồi tài khoản..."
                          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-rose-500 leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button type="button" @click="statusModal = false" variant="ghost" size="sm">
                    Hủy bỏ
                </x-button>
                <x-button type="submit" variant="danger" size="sm">
                    Lưu Thay Đổi
                </x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
