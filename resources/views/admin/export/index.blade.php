@extends('layouts.admin')

@section('title', 'Xuất dữ liệu báo cáo - Admin CMS')

@section('content')
<div class="space-y-6 max-w-5xl" x-data="{ exportType: 'novels' }">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Xuất dữ liệu & Báo cáo thống kê</h1>
        <p class="text-sm text-slate-400">Xuất dữ liệu streaming tốc độ cao với mã hóa UTF-8 BOM và cơ chế phòng vệ chống Formula Injection</p>
    </div>

    <!-- Security Compliance Banner -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-3">
        <div class="flex items-center gap-2 text-indigo-400">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <h2 class="text-sm font-semibold text-white">Tiêu chuẩn An toàn Dữ liệu & Tuân thủ An ninh mạng (NĐ 147/2024)</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-300">
            <div class="p-3 rounded-lg bg-slate-800/40 border border-slate-800">
                <p class="font-semibold text-emerald-400 mb-1">Formula Injection Defense</p>
                Tự động phát hiện và tiếp đầu ngữ ký tự nháy đơn (<code class="text-white font-mono">'</code>) cho các ô bắt đầu bằng <code class="text-white font-mono">=, +, -, @, \t, \r</code> theo chuẩn OWASP.
            </div>
            <div class="p-3 rounded-lg bg-slate-800/40 border border-slate-800">
                <p class="font-semibold text-sky-400 mb-1">Mã hóa UTF-8 with BOM</p>
                Thêm tiền tố Byte Order Mark để các file CSV hiển thị hoàn hảo tiếng Việt có dấu trên cả Microsoft Excel Windows và macOS.
            </div>
            <div class="p-3 rounded-lg bg-slate-800/40 border border-slate-800">
                <p class="font-semibold text-amber-400 mb-1">Masking Dữ liệu Nhạy cảm</p>
                Loại trừ triệt để mật khẩu, số định danh CCCD đã mã hóa, chuỗi băm danh tính và token phiên khỏi tệp xuất.
            </div>
        </div>
    </div>

    <!-- Export Configuration Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
        <form action="{{ route('admin.export.download') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Entity Selection -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-3">Chọn đối tượng cần xuất</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
                           :class="exportType === 'novels' ? 'bg-indigo-600/10 border-indigo-500/50 text-white' : 'bg-slate-800/40 border-slate-800 text-slate-400 hover:text-white'">
                        <input type="radio" name="type" value="novels" x-model="exportType" class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <p class="text-sm font-semibold">Danh sách tác phẩm & tiểu thuyết</p>
                            <p class="text-xs text-slate-400">Tiêu đề, bút danh, thể loại, số chương, lượt xem</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-colors"
                           :class="exportType === 'authors' ? 'bg-indigo-600/10 border-indigo-500/50 text-white' : 'bg-slate-800/40 border-slate-800 text-slate-400 hover:text-white'">
                        <input type="radio" name="type" value="authors" x-model="exportType" class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <p class="text-sm font-semibold">Danh sách hồ sơ tác giả</p>
                            <p class="text-xs text-slate-400">Bút danh, họ tên, email, trạng thái KYC, ngân hàng</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Filters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2 border-t border-slate-800/80">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Trạng thái</label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                        <option value="">Tất cả trạng thái</option>
                        <option value="published">Đã xuất bản (published)</option>
                        <option value="pending_approval">Chờ duyệt (pending_approval)</option>
                        <option value="draft">Bản nháp (draft)</option>
                        <option value="completed">Đã hoàn thành (completed)</option>
                        <option value="paused">Tạm dừng (paused)</option>
                    </select>
                </div>

                <!-- From Date -->
                <div>
                    <label for="from_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Từ ngày tạo</label>
                    <input type="date" 
                           id="from_date" 
                           name="from_date" 
                           class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                </div>

                <!-- To Date -->
                <div>
                    <label for="to_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Đến ngày tạo</label>
                    <input type="date" 
                           id="to_date" 
                           name="to_date" 
                           class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                </div>
            </div>

            <!-- Column Selection -->
            <div class="pt-2 border-t border-slate-800/80">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Chọn các cột xuất bản</label>
                
                <!-- Novel Columns -->
                <div x-show="exportType === 'novels'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    @foreach ($novelColumns as $key => $label)
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-800/40 text-xs text-slate-300 hover:text-white cursor-pointer border border-slate-800">
                            <input type="checkbox" name="columns[]" value="{{ $key }}" checked class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <!-- Author Columns -->
                <div x-show="exportType === 'authors'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3" style="display: none;">
                    @foreach ($authorColumns as $key => $label)
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-800/40 text-xs text-slate-300 hover:text-white cursor-pointer border border-slate-800">
                            <input type="checkbox" name="columns[]" value="{{ $key }}" checked class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end">
                <button type="submit" 
                        class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition-colors shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Tải tệp CSV (Streaming Export)
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Export Jobs -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="bg-slate-800/40 px-6 py-3.5 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Lịch sử xuất báo cáo gần đây</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Mã tác vụ</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Loại dữ liệu</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Người xuất</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Thời điểm</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($recentJobs as $job)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3.5 font-mono text-xs text-indigo-400 font-semibold">
                                #EXP-{{ $job->id }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-300">
                                {{ $job->type === 'novels' ? 'Tác phẩm' : 'Tác giả' }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-300">
                                {{ $job->user?->name ?? 'Hệ thống' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $job->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-400">
                                {{ $job->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                Chưa có tác vụ xuất dữ liệu nào được ghi nhận.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
