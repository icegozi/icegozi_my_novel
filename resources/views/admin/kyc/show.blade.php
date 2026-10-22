<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Định Danh: {{ $profile->pen_name }} - Quản Trị Hệ Thống</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white"
      x-data="{ rejectModal: false, rejectReason: 'Ảnh chụp thẻ CCCD bị lóa sáng, mờ số hoặc không rõ khuôn mặt.' }">

    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.kyc.index') }}" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs flex items-center gap-1.5 transition-all">
                    <span>&larr; Danh Sách e-KYC</span>
                </a>
                <span class="text-sm font-bold text-white">Thẩm Định Hồ Sơ: {{ $profile->pen_name }}</span>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('admin.kyc.approve', $profile) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Xác nhận phê duyệt hồ sơ và nâng cấp quyền tác giả? Ảnh CCCD sẽ tự động xóa vĩnh viễn theo NĐ 13/2023.')"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-1.5">
                        <span>✓ Phê Duyệt e-KYC</span>
                    </button>
                </form>

                <button type="button"
                        @click="rejectModal = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition-all">
                    ✕ Từ Chối
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">
        <!-- Privacy Notice Banner -->
        <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs flex items-start gap-3">
            <span class="text-xl">🔒</span>
            <div>
                <strong class="text-white block font-semibold mb-0.5">Bảo Vệ Dữ Liệu Cá Nhân (Nghị định 13/2023/NĐ-CP):</strong>
                Ảnh Căn Cước Công Dân được hiển thị qua Temporary Signed URL (TTL 10 phút). Hệ thống sẽ tự động tiêu hủy file ảnh vật lý khỏi Private Storage ngay sau khi có kết quả thẩm định để tránh rò rỉ dữ liệu nhạy cảm.
            </div>
        </div>

        <!-- Identity Details -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-400">1. Thông Tin Pháp Lý & Tài Khoản</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400">Họ và tên thật:</span>
                    <strong class="text-white block text-sm mt-0.5">{{ $profile->legal_full_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400">Bút danh sáng tác:</span>
                    <strong class="text-indigo-400 block text-sm mt-0.5">{{ $profile->pen_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400">Số Căn Cước Công Dân (Đã giải mã):</span>
                    <strong class="text-emerald-400 font-mono block text-sm mt-0.5 tracking-wider">{{ $decryptedIdNumber }}</strong>
                </div>
                <div>
                    <span class="text-slate-400">Ngày cấp:</span>
                    <strong class="text-slate-200 block text-sm mt-0.5">{{ $profile->identity_issued_date?->format('d/m/Y') ?? 'N/A' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400">Mã số thuế cá nhân:</span>
                    <strong class="text-slate-200 block text-sm mt-0.5">{{ $profile->tax_code ?? 'Chưa đăng ký' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400">Tài khoản thụ hưởng:</span>
                    <strong class="text-slate-200 block text-sm mt-0.5">{{ $profile->bank_account_number }} ({{ $profile->bank_name }})</strong>
                </div>
            </div>
        </div>

        <!-- Identity Documents Inspection -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-400">2. Giấy Tờ Tùy Thân (CCCD Mặt Trước & Mặt Sau)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Mặt trước -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-300">Mặt trước CCCD:</span>
                    @if($frontSignedUrl)
                        <div class="rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[16/10] flex items-center justify-center p-2">
                            <img src="{{ $frontSignedUrl }}" alt="CCCD Mặt Trước" class="max-h-full max-w-full object-contain rounded-lg">
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-800 p-8 text-center text-xs text-slate-500">
                            Không có ảnh hoặc đã bị xóa theo chính sách bảo mật
                        </div>
                    @endif
                </div>

                <!-- Mặt sau -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-300">Mặt sau CCCD:</span>
                    @if($backSignedUrl)
                        <div class="rounded-xl overflow-hidden border border-slate-800 bg-slate-950 aspect-[16/10] flex items-center justify-center p-2">
                            <img src="{{ $backSignedUrl }}" alt="CCCD Mặt Sau" class="max-h-full max-w-full object-contain rounded-lg">
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-800 p-8 text-center text-xs text-slate-500">
                            Không có ảnh hoặc đã bị xóa theo chính sách bảo mật
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Reject Modal -->
    <div x-show="rejectModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         @keydown.escape.window="rejectModal = false">
        <div class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl space-y-4"
             @click.away="rejectModal = false">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-rose-500">⚠️</span>
                    <span>Từ Chối Hồ Sơ Định Danh: {{ $profile->pen_name }}</span>
                </h3>
                <button @click="rejectModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-slate-400">
                Nhập lý do cụ thể để tác giả chỉnh sửa và gửi lại hồ sơ CCCD hợp lệ.
            </p>

            <form action="{{ route('admin.kyc.reject', $profile) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Lý do từ chối:</label>
                    <textarea name="rejection_reason"
                              x-model="rejectReason"
                              rows="4"
                              required
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-rose-500 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="rejectModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-slate-200">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/30 transition-all">
                        Xác Nhận Từ Chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
