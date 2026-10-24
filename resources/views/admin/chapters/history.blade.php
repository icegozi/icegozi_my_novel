@extends('layouts.admin')

@section('title', 'Lịch sử phiên bản chương - ' . $chapter->title)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.novels.chapters.index', $chapter->novel_id) }}" 
               class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
               aria-label="Quay lại danh sách chương">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Lịch sử sửa đổi & So sánh phiên bản</h1>
                <p class="text-sm text-slate-400">
                    Chương: <span class="text-indigo-400 font-semibold">{{ $chapter->title }}</span> 
                    | Phiên bản đang chạy: <span class="font-mono text-emerald-400 font-semibold">rev #{{ $chapter->revision }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.chapters.edit', $chapter) }}" 
               class="px-4 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 text-sm font-medium transition-colors">
                Quay lại soạn thảo
            </a>
        </div>
    </div>

    <!-- Comparison Toolbar -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.chapters.history', $chapter) }}" class="flex flex-col sm:flex-row items-center gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label for="compare_from" class="text-xs font-semibold uppercase text-slate-400 whitespace-nowrap">Bản gốc:</label>
                <select name="compare_from" 
                        id="compare_from" 
                        class="px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    @foreach ($versions as $v)
                        <option value="{{ $v->id }}" {{ request('compare_from') == $v->id ? 'selected' : '' }}>
                            rev #{{ $v->revision }} - {{ Str::limit($v->change_summary, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <span class="text-slate-500 hidden sm:inline">⟶</span>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label for="compare_to" class="text-xs font-semibold uppercase text-slate-400 whitespace-nowrap">Bản so sánh:</label>
                <select name="compare_to" 
                        id="compare_to" 
                        class="px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    @foreach ($versions as $v)
                        <option value="{{ $v->id }}" {{ request('compare_to') == $v->id ? 'selected' : '' }}>
                            rev #{{ $v->revision }} - {{ Str::limit($v->change_summary, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" 
                    class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm rounded-lg transition-colors shadow-sm">
                So sánh Diff
            </button>
        </form>
    </div>

    <!-- Diff Viewer Panel -->
    @if ($diff)
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="bg-slate-800/80 px-6 py-3 border-b border-slate-700/60 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                    <h2 class="text-sm font-semibold text-white">Kết quả so sánh sai khác (Unified Diff)</h2>
                </div>
                <div class="flex items-center gap-4 text-xs font-mono">
                    <span class="inline-flex items-center gap-1 text-emerald-400"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> + Thêm mới</span>
                    <span class="inline-flex items-center gap-1 text-rose-400"><span class="w-2 h-2 rounded-full bg-rose-400"></span> - Xóa bỏ</span>
                </div>
            </div>
            <div class="p-4 font-mono text-xs leading-relaxed max-h-[500px] overflow-y-auto divide-y divide-slate-800/40">
                @foreach ($diff as $idx => $line)
                    @if ($line['type'] === 'added')
                        <div class="flex items-start gap-3 py-1 px-3 bg-emerald-500/10 text-emerald-300 border-l-2 border-emerald-500 rounded-r">
                            <span class="text-emerald-500 font-bold select-none w-6 text-right">+</span>
                            <div class="flex-1 break-words">{!! htmlspecialchars($line['text']) !!}</div>
                        </div>
                    @elseif ($line['type'] === 'deleted')
                        <div class="flex items-start gap-3 py-1 px-3 bg-rose-500/10 text-rose-300 border-l-2 border-rose-500 rounded-r line-through opacity-80">
                            <span class="text-rose-500 font-bold select-none w-6 text-right">-</span>
                            <div class="flex-1 break-words">{!! htmlspecialchars($line['text']) !!}</div>
                        </div>
                    @else
                        <div class="flex items-start gap-3 py-1 px-3 text-slate-400">
                            <span class="text-slate-600 select-none w-6 text-right"> </span>
                            <div class="flex-1 break-words">{!! htmlspecialchars($line['text']) !!}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Revision Snapshots Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="bg-slate-800/40 px-6 py-3.5 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Danh sách các phiên bản đã lưu</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-center w-24">Phiên bản</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Tiêu đề chương</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-center">Số từ</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Người sửa</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Ghi chú sửa đổi</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Thời điểm</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($versions as $version)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-800 text-indigo-400 border border-indigo-500/30">
                                    rev #{{ $version->revision }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-white">
                                {{ $version->title }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-slate-300">
                                {{ number_format($version->word_count) }}
                            </td>
                            <td class="px-6 py-4 text-slate-300 text-xs">
                                {{ $version->editor?->name ?? 'Hệ thống' }}
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs max-w-xs">
                                {{ $version->change_summary ?? 'Không có ghi chú' }}
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs">
                                {{ $version->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.chapters.history', ['chapter' => $chapter, 'compare_version' => $version->id]) }}" 
                                       class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition-colors">
                                        So với hiện tại
                                    </a>
                                    @can('restoreHistory', $chapter)
                                        <form action="{{ route('admin.chapters.restore', ['chapter' => $chapter, 'version' => $version]) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục nội dung về rev #{{ $version->revision }}? Bản hiện tại sẽ được tự động lưu dự phòng.');"
                                              class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1 rounded bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-medium transition-colors">
                                                Khôi phục
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Chưa có phiên bản lịch sử nào được ghi nhận cho chương này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
