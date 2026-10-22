<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật Ký An Ninh Mạng (Audit Trail) - Nghị Định 147/2024/NĐ-CP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="/" class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                    <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL AUDIT</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Tuân Thủ Nhật Ký An Ninh • NĐ 147/2024/NĐ-CP
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="{{ route('admin.moderation.queue') }}" class="text-slate-400 hover:text-white">
                    &larr; Hàng Đợi Kiểm Duyệt
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Đối Soát & Tra Cứu Nhật Ký An Ninh Mạng</h1>
                <p class="text-sm text-slate-400 mt-1">Lưu trữ tối thiểu 12 tháng nhật ký hoạt động hệ thống theo quy định của Nghị định 147/2024/NĐ-CP.</p>
            </div>
            <div>
                <a href="{{ route('admin.audit-logs.export', request()->all()) }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <span>⬇ Xuất Dữ Liệu Thanh Tra (JSON)</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-400 mb-1">Địa chỉ IP:</label>
                <input type="text" name="ip_address" value="{{ request('ip_address') }}" placeholder="VD: 192.168.1.1" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-400 mb-1">Loại hành động:</label>
                <select name="action" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">-- Tất cả hành động --</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-400 mb-1">Từ ngày:</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block font-semibold text-slate-400 mb-1">Đến ngày:</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl font-bold bg-indigo-600 hover:bg-indigo-500 text-white transition-all h-[38px]">
                    Lọc
                </button>
            </div>
        </form>

        <!-- Audit Table -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900 border-b border-slate-800 text-slate-400 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3">Thời gian</th>
                            <th class="px-4 py-3">Hành động</th>
                            <th class="px-4 py-3">Đối tượng</th>
                            <th class="px-4 py-3">Địa chỉ IP</th>
                            <th class="px-4 py-3">Người thực hiện</th>
                            <th class="px-4 py-3">Chi tiết thay đổi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-400">
                                    {{ $log->created_at->format('H:i:s d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-white">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $log->target_type }} #{{ $log->target_id }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-300">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $log->user?->name ?? 'System' }} (ID: {{ $log->user_id ?? 'N/A' }})
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate font-mono text-[11px] text-slate-400">
                                    @if($log->new_values)
                                        {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    Không tìm thấy bản ghi nhật ký an ninh mạng nào phù hợp với bộ lọc.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </main>
</body>
</html>
