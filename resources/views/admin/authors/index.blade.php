@extends('layouts.admin', ['title' => 'Quản Lý Tác Giả - Novel Engine'])

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold font-heading text-white">Quản Lý Tác Giả</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Danh sách tác giả toàn sàn, kiểm soát định danh e-KYC và trạng thái hoạt động.</p>
        </div>
        <div>
            <a href="{{ route('admin.kyc.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition-colors flex items-center gap-2 min-h-[44px]">
                <span aria-hidden="true">🪪</span>
                <span>Duyệt Hồ Sơ e-KYC Chờ</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80">
        <form method="GET" action="{{ route('admin.authors.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-300 mb-1">Tìm kiếm:</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Bút danh, tên thật, email..."
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors min-h-[44px]">
            </div>

            <div>
                <label for="verification_status" class="block text-xs font-semibold text-slate-300 mb-1">Trạng thái e-KYC:</label>
                <select id="verification_status"
                        name="verification_status"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 min-h-[44px]">
                    <option value="">Tất cả trạng thái e-KYC</option>
                    <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Đã xác thực (Verified)</option>
                    <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                    <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Bị từ chối (Rejected)</option>
                </select>
            </div>

            <div>
                <label for="user_status" class="block text-xs font-semibold text-slate-300 mb-1">Tài khoản:</label>
                <select id="user_status"
                        name="user_status"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 min-h-[44px]">
                    <option value="">Tất cả tài khoản</option>
                    <option value="active" {{ request('user_status') === 'active' ? 'selected' : '' }}>Hoạt động (Active)</option>
                    <option value="suspended" {{ request('user_status') === 'suspended' ? 'selected' : '' }}>Tạm ngưng (Suspended)</option>
                    <option value="banned" {{ request('user_status') === 'banned' ? 'selected' : '' }}>Khóa vĩnh viễn (Banned)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-colors min-h-[44px] flex items-center justify-center gap-1.5">
                    <span aria-hidden="true">🔍</span> Lọc
                </button>
                @if(request()->anyFilled(['search', 'verification_status', 'user_status']))
                    <a href="{{ route('admin.authors.index') }}"
                       class="px-3 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors min-h-[44px] flex items-center justify-center"
                       aria-label="Xóa bộ lọc">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Authors Table -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 overflow-hidden">
        @if($authors->isEmpty())
            <div class="p-12 text-center">
                <span class="text-4xl block mb-2" aria-hidden="true">🔍</span>
                <h2 class="text-base font-bold text-white">Không tìm thấy tác giả nào</h2>
                <p class="text-xs text-slate-400 mt-1">Hãy thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase text-[11px]">
                            <th class="py-3 px-4">Tác Giả</th>
                            <th class="py-3 px-4">Tài Khoản</th>
                            <th class="py-3 px-4">e-KYC</th>
                            <th class="py-3 px-4">Trạng Thái</th>
                            <th class="py-3 px-4">Số Truyện</th>
                            <th class="py-3 px-4 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($authors as $a)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-sm">{{ $a->pen_name }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $a->legal_full_name }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-300 font-mono text-[11px]">
                                    {{ $a->user?->email ?? 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $a->verification_status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($a->verification_status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20') }}">
                                        {{ $a->verification_status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($a->user?->status ?? 'active') === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                        {{ $a->user?->status ?? 'active' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-200 font-semibold font-mono">
                                    {{ $a->user?->novels_count ?? 0 }}
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="{{ route('admin.authors.show', $a) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors min-h-[44px]">
                                        Hồ Sơ
                                    </a>
                                    @can('update', $a)
                                        <a href="{{ route('admin.authors.edit', $a) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 transition-colors min-h-[44px]">
                                            Sửa
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-800/80">
                {{ $authors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
