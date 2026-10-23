@extends('layouts.reader', [
    'title' => 'Định Danh Tác Giả & Cam Kết Bản Quyền (e-KYC) - Novel Engine',
    'metaDescription' => 'Xác thực định danh tác giả e-KYC và cam kết bản quyền số theo Nghị định 147/2024/NĐ-CP và Nghị định 17/2023/NĐ-CP.'
])

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
    <!-- Header Banner -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 border border-indigo-500/25 sepia:border-amber-800/30 mb-3">
            <span class="w-2 h-2 rounded-full bg-indigo-500 sepia:bg-amber-800 animate-pulse"></span>
            Hồ Sơ Tác Giả Xuất Bản • Tuân Thủ NĐ 147/2024/NĐ-CP
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
            Xác Thực Định Danh & Cam Kết Bản Quyền Tác Giả
        </h1>
        <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted text-xs sm:text-sm mt-2 leading-relaxed">
            Để bảo vệ quyền tác giả, nhận doanh thu xuất bản và đăng tải tiểu thuyết số, tác giả cần hoàn thành thẩm định Căn Cước Công Dân (e-KYC) và xác nhận bản quyền theo quy định của pháp luật.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm flex items-start gap-3">
            <span class="text-lg">✅</span>
            <div>
                <h4 class="font-bold text-emerald-800 dark:text-emerald-300">Đã nộp hồ sơ thành công!</h4>
                <p class="mt-0.5 text-xs text-emerald-700/90 dark:text-emerald-400/90 leading-relaxed">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs sm:text-sm flex items-start gap-3">
            <span class="text-lg">⚠️</span>
            <div>
                <h4 class="font-bold text-amber-900 dark:text-amber-200">Yêu cầu hoàn tất định danh</h4>
                <p class="mt-0.5 text-xs text-amber-800/90 dark:text-amber-300/90 leading-relaxed">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 text-xs sm:text-sm">
            <h4 class="font-bold text-rose-900 dark:text-rose-300 flex items-center gap-2">
                <span aria-hidden="true">❌</span> Vui lòng kiểm tra lại các thông tin sau:
            </h4>
            <ul class="list-disc list-inside mt-2 space-y-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($profile)
        <div class="mb-8 p-6 rounded-3xl bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 transition-colors">
            <h3 class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-3">
                Trạng thái hồ sơ định danh hiện tại:
            </h3>
            @if($profile->verification_status === 'pending')
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-500/15 text-amber-800 dark:text-amber-300 sepia:text-amber-950 text-xs font-semibold border border-amber-500/30">
                    <span aria-hidden="true">⏳</span> Đang chờ thẩm định e-KYC (Dự kiến trong 24 giờ làm việc)
                </span>
            @elseif($profile->verification_status === 'verified')
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 sepia:text-emerald-950 text-xs font-semibold border border-emerald-500/30">
                    <span aria-hidden="true">🛡️</span> Đã xác thực định danh tác giả (Hồ sơ hợp lệ)
                </span>
            @elseif($profile->verification_status === 'rejected')
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-rose-500/15 text-rose-800 dark:text-rose-300 sepia:text-rose-950 text-xs font-semibold border border-rose-500/30">
                    <span aria-hidden="true">❌</span> Hồ sơ bị từ chối: {{ $profile->rejection_reason ?? 'Vui lòng kiểm tra lại ảnh Căn Cước Công Dân' }}
                </span>
            @endif
        </div>
    @endif

    <!-- Form -->
    <form action="/tac-gia/dang-ky-dinh-danh" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 sepia:border-sepia-border shadow-sm sepia:shadow-amber-900/5 space-y-6 transition-colors">
        @csrf

        <!-- Block 1: Identity & Pen Name -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <span class="w-6 h-6 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-xs flex items-center justify-center font-bold">1</span>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 font-heading">
                    Thông Tin Định Danh & Tác Quyền
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="pen_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Bút danh hiển thị <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="pen_name" name="pen_name" value="{{ old('pen_name', $profile?->pen_name ?? '') }}" required placeholder="VD: Lão Trư, Cổ Long" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
                <div>
                    <label for="legal_full_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Họ và tên thật (trên Căn Cước Công Dân) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="legal_full_name" name="legal_full_name" value="{{ old('legal_full_name', $profile?->legal_full_name ?? '') }}" required placeholder="VD: NGUYỄN VĂN AN" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="identity_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Số Căn Cước Công Dân (12 số) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}" required maxlength="12" placeholder="001200001234" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Số điện thoại chính chủ (NĐ 147) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user?->phone ?? '') }}" required placeholder="0987654321" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
                <div>
                    <label for="identity_issued_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Ngày cấp Căn Cước Công Dân
                    </label>
                    <input type="date" id="identity_issued_date" name="identity_issued_date" value="{{ old('identity_issued_date', $profile?->identity_issued_date?->format('Y-m-d') ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
            </div>
        </div>

        <!-- Block 2: CCCD Photos -->
        <div class="space-y-4 pt-2">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <span class="w-6 h-6 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-xs flex items-center justify-center font-bold">2</span>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 font-heading">
                    Ảnh Chụp Căn Cước Công Dân (Bảo mật theo NĐ 13/2023)
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-100/60 border border-slate-200 dark:border-slate-800 sepia:border-sepia-border">
                    <label for="identity_front_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Ảnh mặt trước Căn Cước Công Dân <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="identity_front_image" name="identity_front_image" accept="image/*" required class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 dark:file:bg-indigo-500/20 sepia:file:bg-amber-900/20 file:text-indigo-700 dark:file:text-indigo-400 sepia:file:text-amber-900 hover:file:opacity-80 transition-opacity">
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-100/60 border border-slate-200 dark:border-slate-800 sepia:border-sepia-border">
                    <label for="identity_back_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
                        Ảnh mặt sau Căn Cước Công Dân <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="identity_back_image" name="identity_back_image" accept="image/*" required class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 dark:file:bg-indigo-500/20 sepia:file:bg-amber-900/20 file:text-indigo-700 dark:file:text-indigo-400 sepia:file:text-amber-900 hover:file:opacity-80 transition-opacity">
                </div>
            </div>
        </div>

        <!-- Block 3: Payment & Tax info -->
        <div class="space-y-4 pt-2">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60">
                <span class="w-6 h-6 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 sepia:bg-amber-900/15 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-xs flex items-center justify-center font-bold">3</span>
                <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 font-heading">
                    Thông Tin Quyết Toán & Nhuận Bút (Tùy chọn)
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tax_code" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Mã số thuế cá nhân</label>
                    <input type="text" id="tax_code" name="tax_code" value="{{ old('tax_code', $profile?->tax_code ?? '') }}" placeholder="MST nhận nhuận bút" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
                <div>
                    <label for="bank_account_number" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Số tài khoản ngân hàng</label>
                    <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $profile?->bank_account_number ?? '') }}" placeholder="Số TK ngân hàng" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
                <div>
                    <label for="bank_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Tên ngân hàng</label>
                    <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $profile?->bank_name ?? '') }}" placeholder="VD: Vietcombank, MB" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
                </div>
            </div>
        </div>

        <!-- Cam Kết Pháp Lý -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 sepia:border-sepia-border/60 space-y-3">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="agree_copyright" value="1" {{ old('agree_copyright') ? 'checked' : '' }} required class="mt-1 w-4 h-4 rounded text-indigo-600 sepia:text-amber-800 focus:ring-indigo-500 sepia:focus:ring-amber-800 bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border-slate-300 dark:border-slate-700 sepia:border-sepia-border">
                <span class="text-xs text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed">
                    Tôi đọc, hiểu rõ và ký điện tử vào <strong>Cam Kết Bản Quyền Tác Giả Số</strong> (theo Nghị định 17/2023/NĐ-CP). Tôi cam đoan chịu trách nhiệm pháp lý cao nhất về tính nguyên bản của mọi tác phẩm đăng tải trên nền tảng.
                </span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="agree_terms" value="1" {{ old('agree_terms') ? 'checked' : '' }} required class="mt-1 w-4 h-4 rounded text-indigo-600 sepia:text-amber-800 focus:ring-indigo-500 sepia:focus:ring-amber-800 bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border-slate-300 dark:border-slate-700 sepia:border-sepia-border">
                <span class="text-xs text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed">
                    Tôi đồng ý với <a href="{{ route('legal.terms') }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 sepia:text-amber-900 underline font-semibold">Điều khoản Dịch vụ</a> và cho phép nền tảng xử lý dữ liệu cá nhân theo đúng Nghị định 13/2023/NĐ-CP phục vụ mục đích xác minh tư cách tác giả.
                </span>
            </label>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400 sepia:text-sepia-muted flex items-center gap-1.5">
                <span aria-hidden="true">🔒</span> Dữ liệu định danh được mã hóa AES-256 an toàn.
            </span>
            <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs sm:text-sm text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-md sepia:shadow-amber-900/20 transition-colors shadow-md flex items-center justify-center gap-2">
                <span>Ký Số & Nộp Hồ Sơ Định Danh</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
    </form>
</div>
@endsection
