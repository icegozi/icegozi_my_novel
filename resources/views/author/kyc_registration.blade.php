<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Định Danh Tác Giả & Cam Kết Bản Quyền (e-KYC) - 3D Novel Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL PLATFORM</span>
            </a>
            <a href="/dieu-khoan-va-ban-quyen" target="_blank" class="text-xs text-slate-400 hover:text-indigo-400 transition-colors">
                Xem Quy Chế Bản Quyền Số &rarr;
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-10">
        <!-- Header Banner -->
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-3">
                Hồ Sơ Tác Giả Xuất Bản • Tuân Thủ NĐ 147/2024/NĐ-CP
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white">Xác Thực Định Danh & Cam Kết Bản Quyền Tác Giả</h1>
            <p class="text-slate-400 text-sm mt-1">Để bảo vệ quyền tác giả, nhận doanh thu và đăng tải nội dung 3D, tác giả cần hoàn thành thẩm định Căn Cước Công Dân.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <h4 class="font-semibold text-emerald-300">Đã nộp hồ sơ thành công!</h4>
                    <p class="mt-0.5 text-xs text-emerald-400/90">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm flex items-start gap-3">
                <span class="text-xl">⚠️</span>
                <div>
                    <h4 class="font-semibold text-amber-300">Yêu cầu hoàn tất định danh</h4>
                    <p class="mt-0.5 text-xs text-amber-400/90">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm">
                <h4 class="font-semibold text-rose-300 flex items-center gap-2">
                    <span>❌</span> Vui lòng kiểm tra lại các thông tin sau:
                </h4>
                <ul class="list-disc list-inside mt-2 space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($profile)
            <div class="mb-8 p-6 rounded-2xl bg-slate-900/60 border border-slate-800">
                <h3 class="text-sm font-semibold text-slate-300 mb-2">Trạng thái hồ sơ định danh hiện tại:</h3>
                @if($profile->verification_status === 'pending')
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 text-xs font-semibold border border-amber-500/30">
                        ⏳ Đang chờ thẩm định e-KYC (Dự kiến trong 24 giờ)
                    </span>
                @elseif($profile->verification_status === 'verified')
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">
                        🛡️ Đã xác thực định danh tác giả (Hồ sơ hợp lệ)
                    </span>
                @elseif($profile->verification_status === 'rejected')
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-rose-500/20 text-rose-300 text-xs font-semibold border border-rose-500/30">
                        ❌ Hồ sơ bị từ chối: {{ $profile->rejection_reason ?? 'Vui lòng kiểm tra lại ảnh CCCD' }}
                    </span>
                @endif
            </div>
        @endif

        <!-- Form -->
        <form action="/tac-gia/dang-ky-dinh-danh" method="POST" enctype="multipart/form-data" class="bg-slate-900/40 p-8 rounded-2xl border border-slate-800/80 shadow-2xl space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bút danh hiển thị <span class="text-rose-400">*</span></label>
                    <input type="text" name="pen_name" value="{{ old('pen_name', $profile?->pen_name ?? '') }}" required placeholder="VD: Lão Trư, Cổ Long" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Họ và tên thật (trên CCCD) <span class="text-rose-400">*</span></label>
                    <input type="text" name="legal_full_name" value="{{ old('legal_full_name', $profile?->legal_full_name ?? '') }}" required placeholder="VD: NGUYỄN VĂN AN" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Số Căn Cước Công Dân (12 số) <span class="text-rose-400">*</span></label>
                    <input type="text" name="identity_number" value="{{ old('identity_number') }}" required maxlength="12" placeholder="001200001234" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Số điện thoại chính chủ (NĐ 147) <span class="text-rose-400">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $user?->phone ?? '') }}" required placeholder="0987654321" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ngày cấp CCCD</label>
                    <input type="date" name="identity_issued_date" value="{{ old('identity_issued_date', $profile?->identity_issued_date?->format('Y-m-d') ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ảnh mặt trước Căn Cước Công Dân <span class="text-rose-400">*</span></label>
                    <input type="file" name="identity_front_image" accept="image/*" required class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-400 hover:file:bg-indigo-600 hover:file:text-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ảnh mặt sau Căn Cước Công Dân <span class="text-rose-400">*</span></label>
                    <input type="file" name="identity_back_image" accept="image/*" required class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-400 hover:file:bg-indigo-600 hover:file:text-white transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Mã số thuế cá nhân (nếu có)</label>
                    <input type="text" name="tax_code" value="{{ old('tax_code', $profile?->tax_code ?? '') }}" placeholder="MST nhận nhuận bút" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Số tài khoản ngân hàng</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $profile?->bank_account_number ?? '') }}" placeholder="Số TK nhận nhuận bút" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tên ngân hàng</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $profile?->bank_name ?? '') }}" placeholder="VD: Vietcombank, MBBank" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <!-- Cam Kết Pháp Lý -->
            <div class="pt-4 border-t border-slate-800/80 space-y-3">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="agree_copyright" value="1" {{ old('agree_copyright') ? 'checked' : '' }} required class="mt-1 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700">
                    <span class="text-xs text-slate-300 leading-relaxed">
                        Tôi đọc, hiểu rõ và ký điện tử vào <strong>Cam Kết Bản Quyền Tác Giả Số</strong> (theo Nghị định 17/2023/NĐ-CP). Tôi cam đoan chịu trách nhiệm pháp lý cao nhất về tính nguyên bản của mọi tác phẩm đăng tải trên nền tảng.
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="agree_terms" value="1" {{ old('agree_terms') ? 'checked' : '' }} required class="mt-1 w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700">
                    <span class="text-xs text-slate-300 leading-relaxed">
                        Tôi đồng ý với <strong>Điều khoản Dịch vụ</strong> và cho phép nền tảng xử lý dữ liệu cá nhân theo đúng Nghị định 13/2023/NĐ-CP phục vụ mục đích xác minh tư cách tác giả.
                    </span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <span class="text-xs text-slate-400">Dữ liệu định danh được mã hóa AES-256 an toàn.</span>
                <button type="submit" class="px-6 py-3 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-500/25 transition-all">
                    Ký Số & Nộp Hồ Sơ Định Danh &rarr;
                </button>
            </div>
        </form>
    </main>
</body>
</html>
