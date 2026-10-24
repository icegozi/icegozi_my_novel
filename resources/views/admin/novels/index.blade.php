@extends('layouts.admin')

@section('title', 'Quản lý tác phẩm - Admin CMS')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Quản lý tác phẩm</h1>
            <p class="text-sm text-slate-400">Danh mục toàn bộ truyện và tiểu thuyết trên hệ thống</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ Route::has('admin.export.index') ? route('admin.export.index') : '#' }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Xuất danh sách
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.novels.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Keyword Search -->
            <div>
                <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Tìm kiếm</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ request('search') }}" 
                           placeholder="Tiêu đề, slug, bút danh..." 
                           class="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-800/80 border border-slate-700 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Trạng thái xuất bản</label>
                <select name="status" 
                        id="status" 
                        class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                    <option value="">Tất cả trạng thái</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp (draft)</option>
                    <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Chờ duyệt (pending)</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Đã xuất bản (published)</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Tạm dừng (paused)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành (completed)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối (rejected)</option>
                    <option value="takedown" {{ request('status') === 'takedown' ? 'selected' : '' }}>Gỡ bỏ (takedown)</option>
                    <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Thùng rác (Đã xóa mềm)</option>
                </select>
            </div>

            <!-- Genre Filter -->
            <div>
                <label for="genre_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Thể loại</label>
                <select name="genre_id" 
                        id="genre_id" 
                        class="w-full px-3 py-2 rounded-lg bg-slate-800/80 border border-slate-700 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-colors">
                    <option value="">Tất cả thể loại</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre->id }}" {{ request('genre_id') == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    Lọc dữ liệu
                </button>
                @if (request()->hasAny(['search', 'status', 'genre_id']))
                    <a href="{{ route('admin.novels.index') }}" 
                       class="px-3 py-2 border border-slate-700 text-slate-400 hover:text-white rounded-lg text-sm hover:bg-slate-800 transition-colors"
                       title="Đặt lại bộ lọc">
                        Đặt lại
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Novels Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Tác phẩm</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Tác giả</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Thể loại</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold">Trạng thái</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-center">Số chương</th>
                        <th scope="col" class="px-6 py-3.5 font-semibold text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($novels as $novel)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Cover & Title -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-14 rounded bg-slate-800 overflow-hidden flex-shrink-0 border border-slate-700">
                                        @if ($novel->cover_image)
                                            <img src="{{ Str::startsWith($novel->cover_image, ['http://', 'https://']) ? $novel->cover_image : asset($novel->cover_image) }}" 
                                                 alt="{{ $novel->title }}" 
                                                 width="40" 
                                                 height="56" 
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-600">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.novels.edit', $novel) }}" class="font-semibold text-white hover:text-indigo-400 transition-colors line-clamp-1">
                                            {{ $novel->title }}
                                        </a>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">/{{ $novel->slug }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Author -->
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-200">
                                    {{ $novel->author?->authorProfile?->pen_name ?? $novel->author?->name ?? 'N/A' }}
                                </span>
                            </td>

                            <!-- Genres -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse ($novel->genres as $genre)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ $genre->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500">Chưa gán</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-4">
                                @if ($novel->status === 'published')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        published
                                    </span>
                                @elseif ($novel->status === 'pending_approval')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        pending
                                    </span>
                                @elseif ($novel->status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-sky-500/10 text-sky-400 border border-sky-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                        completed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-700/50 text-slate-400 border border-slate-600/30">
                                        {{ $novel->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Chapters Count -->
                            <td class="px-6 py-4 text-center font-medium text-slate-300">
                                {{ $novel->chapters_count ?? 0 }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.novels.chapters.index', $novel) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 text-indigo-400 hover:text-white hover:bg-indigo-600 text-xs font-medium transition-colors"
                                       title="Quản lý chương">
                                        Chương
                                    </a>
                                    @if($novel->trashed())
                                        @can('deleteAdmin', $novel)
                                            <form action="{{ route('admin.novels.restore', $novel->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn có muốn khôi phục tác phẩm này không?');"
                                                  class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 transition-colors"
                                                        aria-label="Khôi phục tác phẩm">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <a href="{{ route('admin.novels.edit', $novel) }}" 
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                           aria-label="Chỉnh sửa tác phẩm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        @can('deleteAdmin', $novel)
                                            <form action="{{ route('admin.novels.destroy', $novel) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa mềm tác phẩm này không?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                                                        aria-label="Xóa mềm tác phẩm">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Không tìm thấy tác phẩm nào phù hợp với điều kiện tìm kiếm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($novels->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $novels->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
