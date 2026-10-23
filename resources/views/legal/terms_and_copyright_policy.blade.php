@extends('layouts.reader', [
    'title' => 'Quy Chế Bản Quyền & Điều Khoản Dịch Vụ - Novel Engine',
    'metaDescription' => 'Cam kết bản quyền, quy chế xuất bản số và chính sách định danh tác giả e-KYC tuân thủ pháp luật Việt Nam.'
])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
    <!-- Header Hero -->
    <div class="mb-10 text-center">
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 dark:bg-amber-400/10 sepia:bg-amber-900/15 text-amber-800 dark:text-amber-400 sepia:text-amber-900 border border-amber-500/20 dark:border-amber-400/20 sepia:border-amber-800/30 mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Văn Bản Pháp Lý Số • Hiệu Lực 2026
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 dark:text-white sepia:text-sepia-text tracking-tight">
            Cam Kết Bản Quyền & Quy Chế Xuất Bản Số
        </h1>
        <p class="text-slate-600 dark:text-slate-400 sepia:text-sepia-muted mt-3 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
            Tuân thủ nghiêm ngặt Nghị định 147/2024/NĐ-CP (Quản lý Internet và nội dung số), Nghị định 17/2023/NĐ-CP (Hướng dẫn Luật Sở hữu trí tuệ) và Nghị định 13/2023/NĐ-CP (Bảo vệ dữ liệu cá nhân).
        </p>

        <!-- Quick Action Links -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('author.kyc') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 sepia:bg-amber-800 sepia:hover:bg-amber-700 shadow-md sepia:shadow-amber-900/20 transition-all">
                <span>Đăng Ký Tác Giả e-KYC</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
            <a href="{{ route('legal.reports.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-700 dark:text-rose-400 sepia:text-rose-900 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/25 transition-all">
                <span>Báo Cáo Vi Phạm Bản Quyền</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Main Content Cards -->
    <div class="space-y-6">
        <!-- Section 1: e-KYC -->
        <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-start gap-4">
                <span class="w-8 h-8 rounded-xl bg-amber-500/15 dark:bg-amber-500/20 sepia:bg-amber-900/20 text-amber-800 dark:text-amber-400 sepia:text-amber-900 text-sm flex items-center justify-center font-bold shrink-0">
                    1
                </span>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text">
                        Định Danh Người Dùng & Tác Giả (Nghị định 147/2024/NĐ-CP)
                    </h2>
                    <p class="text-xs text-amber-800/80 dark:text-amber-400/80 sepia:text-amber-900/80 font-medium mt-0.5">
                        Quy định bắt buộc đối với hoạt động sáng tác và xuất bản trực tuyến
                    </p>
                </div>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed">
                Mọi người dùng khi đăng ký trở thành tác giả, sáng tác hoặc phát hành nội dung số bắt buộc phải hoàn thành quy trình xác thực định danh điện tử (e-KYC) bao gồm: Họ tên thật, Số Căn cước công dân (12 chữ số) và Số điện thoại di động chính chủ tại Việt Nam.
            </p>

            <div class="p-4 rounded-2xl bg-amber-500/5 dark:bg-slate-800/50 sepia:bg-sepia-100 border border-amber-500/20 dark:border-slate-700/60 sepia:border-sepia-border text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted space-y-1">
                <strong class="text-slate-900 dark:text-slate-200 sepia:text-sepia-text block font-semibold">Bảo mật mã hóa đa tầng:</strong>
                <p>
                    Toàn bộ số định danh CCCD được mã hóa đối xứng AES-256 trên cơ sở dữ liệu. Hệ thống chỉ lưu trữ chuỗi băm bảo mật HMAC-SHA256 với secret pepper riêng biệt để bảo vệ quyền riêng tư tuyệt đối cho tác giả.
                </p>
            </div>
        </div>

        <!-- Section 2: Copyright -->
        <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-start gap-4">
                <span class="w-8 h-8 rounded-xl bg-indigo-500/15 dark:bg-indigo-500/20 sepia:bg-amber-900/20 text-indigo-700 dark:text-indigo-400 sepia:text-amber-900 text-sm flex items-center justify-center font-bold shrink-0">
                    2
                </span>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text">
                        Trách Nhiệm Bản Quyền & Tác Quyền Số (Nghị định 17/2023/NĐ-CP)
                    </h2>
                    <p class="text-xs text-indigo-700/80 dark:text-indigo-400/80 sepia:text-amber-900/80 font-medium mt-0.5">
                        Nguyên tắc sở hữu trí tuệ và cơ chế thông báo gỡ bỏ (Notice-and-Takedown)
                    </p>
                </div>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed">
                Tác giả đăng tải tác phẩm cam đoan tác phẩm là sản phẩm sáng tạo nguyên bản của chính mình, hoặc có hợp đồng/văn bản ủy quyền hợp pháp từ chủ sở hữu quyền tác giả đối với tác phẩm dịch thuật/phóng tác. Mọi hành vi sao chép không phép, đạo văn hoặc vi phạm quyền tác giả sẽ bị gỡ bỏ ngay lập tức theo cơ chế Notice-and-Takedown.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 sepia:bg-sepia-100/60 border border-slate-200/80 dark:border-slate-700/50 sepia:border-sepia-border">
                    <strong class="text-slate-900 dark:text-slate-200 sepia:text-sepia-text block mb-1">Cơ chế khiếu nại bản quyền:</strong>
                    Chủ sở hữu quyền tác giả có thể gửi văn bản yêu cầu qua cổng trực tuyến kèm tài liệu chứng minh quyền sở hữu hợp pháp.
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 sepia:bg-sepia-100/60 border border-slate-200/80 dark:border-slate-700/50 sepia:border-sepia-border">
                    <strong class="text-slate-900 dark:text-slate-200 sepia:text-sepia-text block mb-1">Quyền phản đối của tác giả:</strong>
                    Tác giả có quyền gửi thông báo phản đối (Counter-Notice) trong vòng 10 ngày làm việc kèm bằng chứng xác thực.
                </div>
            </div>
        </div>

        <!-- Section 3: Privacy & Data Protection -->
        <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-start gap-4">
                <span class="w-8 h-8 rounded-xl bg-purple-500/15 dark:bg-purple-500/20 sepia:bg-amber-900/20 text-purple-700 dark:text-purple-400 sepia:text-amber-900 text-sm flex items-center justify-center font-bold shrink-0">
                    3
                </span>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text">
                        Bảo Vệ Dữ Liệu Cá Nhân (Nghị định 13/2023/NĐ-CP)
                    </h2>
                    <p class="text-xs text-purple-700/80 dark:text-purple-400/80 sepia:text-amber-900/80 font-medium mt-0.5">
                        Cam kết an toàn thông tin và quyền riêng tư của tác giả
                    </p>
                </div>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-300 sepia:text-sepia-muted leading-relaxed">
                Nền tảng cam kết chỉ sử dụng ảnh Căn cước công dân và thông tin định danh cho mục đích xác thực pháp lý xuất bản theo quy định. Ảnh CCCD được lưu trữ trên ổ đĩa bảo mật Private Disk, chỉ được xem qua URL có ký duyệt bảo mật thời hạn và được bảo vệ theo tiêu chuẩn an toàn thông tin nghiêm ngặt nhất.
            </p>
        </div>

        <!-- Section 4: Operational Workflow -->
        <div class="bg-white dark:bg-slate-900/60 sepia:bg-sepia-card border border-slate-200 dark:border-slate-800 sepia:border-sepia-border rounded-3xl p-6 sm:p-8 shadow-sm sepia:shadow-amber-900/5 space-y-4 transition-colors">
            <div class="flex items-start gap-4">
                <span class="w-8 h-8 rounded-xl bg-emerald-500/15 dark:bg-emerald-500/20 sepia:bg-amber-900/20 text-emerald-700 dark:text-emerald-400 sepia:text-amber-900 text-sm flex items-center justify-center font-bold shrink-0">
                    4
                </span>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold font-heading text-slate-900 dark:text-white sepia:text-sepia-text">
                        Quy Trình Tiếp Nhận & Giải Quyết Tranh Chấp
                    </h2>
                    <p class="text-xs text-emerald-700/80 dark:text-emerald-400/80 sepia:text-amber-900/80 font-medium mt-0.5">
                        Quy trình 3 bước xử lý minh bạch và công bằng
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 sepia:bg-sepia-100/60 border border-slate-200/80 dark:border-slate-700/50 sepia:border-sepia-border">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 sepia:text-amber-900 block mb-1">Bước 1: Tiếp nhận</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted leading-relaxed">
                        Chủ sở hữu nộp hồ sơ khiếu nại qua cổng tiếp nhận bản quyền trực tuyến của nền tảng.
                    </p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 sepia:bg-sepia-100/60 border border-slate-200/80 dark:border-slate-700/50 sepia:border-sepia-border">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 sepia:text-amber-900 block mb-1">Bước 2: Tạm gỡ</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted leading-relaxed">
                        Nội dung bị khiếu nại được tạm ẩn trong vòng 24h để xác minh và thông báo cho tác giả.
                    </p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 sepia:bg-sepia-100/60 border border-slate-200/80 dark:border-slate-700/50 sepia:border-sepia-border">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 sepia:text-amber-900 block mb-1">Bước 3: Phản đối / Khôi phục</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted leading-relaxed">
                        Tác giả nộp đơn phản đối trong 10 ngày. Nếu không có khởi kiện tư pháp, nội dung được khôi phục.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
