@extends('layouts.admin')

@section('title', 'Kết quả kiểm tra Dry-Run - Admin CMS')

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.import.index') }}" 
               class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
               aria-label="Quay lại bàn nhập dữ liệu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Kết quả kiểm tra dữ liệu (Dry-Run Preview)</h1>
                <p class="text-sm text-slate-400">Tệp: <span class="font-mono text-indigo-400">{{ $job->original_filename }}</span> (Loại: {{ $job->type === 'novels' ? 'Tác phẩm' : 'Chương' }})</p>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Rows -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Tổng số dòng quét</p>
            <p class="text-2xl font-bold font-mono text-white">{{ number_format($job->total_rows) }}</p>
        </div>

        <!-- Valid Rows -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-1">Dòng hợp lệ (Sẵn sàng nạp)</p>
            <p class="text-2xl font-bold font-mono text-emerald-400">{{ number_format($job->successful_rows) }}</p>
        </div>

        <!-- Failed Rows -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-400 mb-1">Dòng phát hiện lỗi</p>
            <p class="text-2xl font-bold font-mono text-rose-400">{{ number_format($job->failed_rows) }}</p>
        </div>
    </div>

    <!-- Errors Section -->
    @if (!empty($job->error_summary))
        <div class="bg-slate-900 border border-rose-500/30 rounded-xl overflow-hidden shadow-sm">
            <div class="bg-rose-500/10 px-6 py-4 border-b border-rose-500/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h2 class="text-sm font-semibold text-rose-300">Danh sách lỗi chi tiết theo từng dòng và cột</h2>
                </div>
                <a href="{{ route('admin.import.errors', $job) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-medium transition-colors shadow-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Tải file báo cáo lỗi (.csv)
                </a>
            </div>

            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-left text-xs font-mono text-slate-300">
                    <thead class="bg-slate-800/80 uppercase tracking-wider text-slate-400 border-b border-slate-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-24">Dòng số</th>
                            <th scope="col" class="px-6 py-3 w-44">Tên cột lỗi</th>
                            <th scope="col" class="px-6 py-3 font-sans">Nguyên nhân chi tiết & Hướng khắc phục</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @foreach ($job->error_summary as $error)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-6 py-3 font-mono font-semibold text-rose-400">
                                    #{{ $error['row'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-3 font-mono text-slate-300">
                                    {{ $error['column'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-3 text-slate-300">
                                    {{ $error['message'] ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Valid / Commit Section -->
    @if ($job->status === 'validated' && empty($job->error_summary))
        <div class="bg-slate-900 border border-emerald-500/30 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 text-emerald-400">
                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Tệp dữ liệu đã vượt qua 100% kiểm tra an toàn!</h2>
                    <p class="text-xs text-slate-400">Toàn bộ {{ number_format($job->successful_rows) }} bản ghi đã sẵn sàng để lưu vào cơ sở dữ liệu.</p>
                </div>
            </div>

            <div class="p-4 rounded-lg bg-slate-800/60 border border-slate-700/60 text-xs text-slate-300 leading-relaxed">
                <p class="font-semibold text-indigo-400 mb-1">Cơ chế bảo vệ dữ liệu:</p>
                Tất cả tác phẩm và chương truyện sau khi nạp sẽ được lưu mặc định ở trạng thái <strong class="text-white">Bản nháp (draft)</strong>, không tự động công khai ra trang chủ cho đến khi có phê duyệt từ Quản trị viên.
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.import.index') }}" 
                   class="px-5 py-2.5 rounded-lg border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 text-sm font-medium transition-colors">
                    Hủy bỏ
                </a>
                <form action="{{ route('admin.import.commit', $job) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm transition-colors shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Xác nhận nạp dữ liệu vào hệ thống (Draft)
                    </button>
                </form>
            </div>
        </div>
    @elseif ($job->status === 'completed')
        <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 text-center space-y-3">
            <p class="text-emerald-400 font-semibold">Tác vụ nạp dữ liệu này đã được hoàn tất thành công.</p>
            <a href="{{ route('admin.import.index') }}" class="inline-block px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium transition-colors">
                Trở về bàn nhập dữ liệu
            </a>
        </div>
    @endif
</div>
@endsection
