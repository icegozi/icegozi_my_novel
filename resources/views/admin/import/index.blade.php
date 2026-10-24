@extends('layouts.admin')

@section('title', 'Nhập dữ liệu hàng loạt - Admin CMS')

@section('content')
<div class="space-y-8 max-w-5xl">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Nhập dữ liệu hàng loạt (Import Wizard)</h1>
        <p class="text-sm text-slate-400">Quy trình 4 bước nạp tác phẩm và chương từ tệp CSV chuẩn hóa, an toàn với chế độ chạy thử nghiệm (Dry-Run)</p>
    </div>

    <!-- 4-Step Progress Indicator -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Step 1 -->
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0">1</span>
                <div>
                    <p class="text-xs font-semibold uppercase text-indigo-400">Bước 1</p>
                    <p class="text-sm font-medium text-white">Tải mẫu & Chọn tệp</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="flex items-center gap-3 opacity-60">
                <span class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 font-bold text-sm flex items-center justify-center flex-shrink-0 border border-slate-700">2</span>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">Bước 2</p>
                    <p class="text-sm font-medium text-slate-300">Quét cấu trúc cột</p>
                </div>
            </div>
            <!-- Step 3 -->
            <div class="flex items-center gap-3 opacity-60">
                <span class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 font-bold text-sm flex items-center justify-center flex-shrink-0 border border-slate-700">3</span>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">Bước 3</p>
                    <p class="text-sm font-medium text-slate-300">Chạy thử Dry-Run</p>
                </div>
            </div>
            <!-- Step 4 -->
            <div class="flex items-center gap-3 opacity-60">
                <span class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 font-bold text-sm flex items-center justify-center flex-shrink-0 border border-slate-700">4</span>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400">Bước 4</p>
                    <p class="text-sm font-medium text-slate-300">Nạp vào bản nháp</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Template Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Novel Template Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-center justify-between shadow-sm">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-base font-semibold text-white">Mẫu CSV Tác phẩm</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">UTF-8 BOM</span>
                </div>
                <p class="text-xs text-slate-400">Bao gồm tiêu đề, bút danh tác giả, thể loại, tóm tắt, SEO metadata</p>
            </div>
            <a href="{{ route('admin.import.template', 'novels') }}" 
               class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-white border border-slate-700 text-xs font-medium transition-colors flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Tải mẫu CSV
            </a>
        </div>

        <!-- Chapter Template Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-center justify-between shadow-sm">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-base font-semibold text-white">Mẫu CSV Chương truyện</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">UTF-8 BOM</span>
                </div>
                <p class="text-xs text-slate-400">Bao gồm slug truyện, số thứ tự chương, tiêu đề chương và nội dung</p>
            </div>
            <a href="{{ route('admin.import.template', 'chapters') }}" 
               class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-white border border-slate-700 text-xs font-medium transition-colors flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Tải mẫu CSV
            </a>
        </div>
    </div>

    <!-- Upload Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
        <h2 class="text-lg font-bold text-white tracking-tight mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            Tải lên tệp dữ liệu để kiểm tra (Dry-Run)
        </h2>

        <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Type Selection -->
                <div>
                    <label for="type" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Loại dữ liệu nhập <span class="text-rose-400">*</span>
                    </label>
                    <select id="type" 
                            name="type" 
                            required 
                            class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors">
                        <option value="novels">Tác phẩm / Tiểu thuyết</option>
                        <option value="chapters">Chương truyện</option>
                    </select>
                </div>

                <!-- File Selection -->
                <div class="md:col-span-2">
                    <label for="file" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tệp CSV chuẩn hóa <span class="text-rose-400">*</span> (Tối đa 10MB)
                    </label>
                    <input type="file" 
                           id="file" 
                           name="file" 
                           accept=".csv,text/csv,text/plain" 
                           required 
                           class="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer transition-colors">
                </div>
            </div>

            <div class="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs leading-relaxed">
                <p class="font-semibold mb-0.5">Lưu ý quan trọng về an toàn dữ liệu:</p>
                Tệp tải lên sẽ được quét cấu trúc cột và kiểm tra tính toàn vẹn (Dry-Run) trước. Dữ liệu chưa được ghi vào hệ thống chính thức cho đến khi bạn xác nhận ở Bước 4.
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" 
                        class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition-colors shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Bắt đầu phân tích & Chạy thử nghiệm
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Import Jobs -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="bg-slate-800/40 px-6 py-3.5 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Lịch sử các tác vụ nhập gần đây</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Tên tệp</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Loại</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Tổng dòng</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Thành công / Lỗi</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Thời điểm</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($recentJobs as $job)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-white max-w-xs truncate">
                                {{ $job->original_filename }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-300">
                                {{ $job->type === 'novels' ? 'Tác phẩm' : 'Chương' }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if ($job->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">completed</span>
                                @elseif ($job->status === 'validated')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">validated</span>
                                @elseif ($job->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-rose-500/10 text-rose-400 border border-rose-500/20">failed</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-400 border border-slate-700">{{ $job->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-center font-mono text-slate-300">
                                {{ $job->total_rows }}
                            </td>
                            <td class="px-6 py-3.5 text-center font-mono text-xs">
                                <span class="text-emerald-400">{{ $job->successful_rows }}</span> / 
                                <span class="text-rose-400">{{ $job->failed_rows }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-400">
                                {{ $job->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('admin.import.preview', $job) }}" class="text-indigo-400 hover:text-white text-xs font-medium transition-colors">
                                    Xem kết quả
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                Chưa có tác vụ nhập dữ liệu nào được thực hiện.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
