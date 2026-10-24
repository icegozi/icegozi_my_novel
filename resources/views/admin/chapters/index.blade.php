@extends('layouts.admin')

@section('title', 'Quản lý chương - ' . $novel->title)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.novels.index') }}" 
               class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
               aria-label="Quay lại danh sách tác phẩm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Quản lý chương truyện</h1>
                <p class="text-sm text-slate-400">Tác phẩm: <span class="text-indigo-400 font-semibold">{{ $novel->title }}</span> ({{ $chapters->total() }} chương)</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.novels.edit', $novel) }}" 
               class="px-4 py-2 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 text-sm font-medium transition-colors">
                Sửa thông tin tác phẩm
            </a>
        </div>
    </div>

    <!-- Chapters Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-center w-20">Số TT</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Tiêu đề chương</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-center">Số chữ</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-center">Phiên bản</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Trạng thái</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Thời gian</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($chapters as $ch)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Number -->
                            <td class="px-6 py-4 text-center font-mono font-semibold text-indigo-400">
                                #{{ number_format($ch->chapter_number, 0) }}
                            </td>

                            <!-- Title & Slug -->
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.chapters.edit', $ch) }}" class="font-semibold text-white hover:text-indigo-400 transition-colors">
                                    {{ $ch->title }}
                                </a>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">/{{ $ch->slug }}</p>
                            </td>

                            <!-- Word Count -->
                            <td class="px-6 py-4 text-center font-mono text-slate-300">
                                {{ number_format($ch->word_count) }}
                            </td>

                            <!-- Revision Badge -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                    rev #{{ $ch->revision }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if ($ch->status === 'published')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        published
                                    </span>
                                @elseif ($ch->status === 'scheduled')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        scheduled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-700/50 text-slate-400 border border-slate-600/30">
                                        {{ $ch->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Time -->
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $ch->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ Route::has('admin.chapters.history') ? route('admin.chapters.history', $ch) : '#' }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 text-xs font-medium transition-colors"
                                       title="Xem lịch sử phiên bản & Diff">
                                        Lịch sử
                                    </a>
                                    <a href="{{ route('admin.chapters.edit', $ch) }}" 
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                       aria-label="Chỉnh sửa chương">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Tác phẩm này hiện chưa có chương nào được đăng tải.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($chapters->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $chapters->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
