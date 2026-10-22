<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông Báo Pháp Lý: Tạm Ẩn Tác Phẩm Do Khiếu Nại Bản Quyền</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; padding: 24px; margin: 0; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 24px; }
        .legal-badge { display: inline-block; background-color: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .highlight-box { background-color: #f1f5f9; border-left: 4px solid #6366f1; padding: 16px; border-radius: 0 8px 8px 0; margin: 20px 0; font-size: 14px; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff !important; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; margin-top: 16px; }
        .footer { margin-top: 32px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="legal-badge">VĂN BẢN THÔNG BÁO PHÁP LÝ • NĐ 17/2023/NĐ-CP</span>
            <h2 style="color: #0f172a; margin-top: 12px; font-size: 20px;">Thông Báo Tạm Ẩn Tác Phẩm Chờ Đối Soát Bản Quyền</h2>
        </div>

        <p>Kính gửi Tác giả <strong>{{ $novel->author?->authorProfile?->pen_name ?? $novel->author?->name }}</strong>,</p>

        <p>
            Hệ thống nhận được văn bản yêu cầu xử lý xâm phạm quyền tác giả đối với tác phẩm <strong>"{{ $novel->title }}"</strong> từ bên thứ ba kèm theo tài liệu chứng minh quyền sở hữu theo quy định của <strong>Nghị định 17/2023/NĐ-CP</strong> và <strong>Luật Sở hữu trí tuệ Việt Nam</strong>.
        </p>

        <div class="highlight-box">
            <strong>Thông tin đối soát:</strong><br>
            • Tác phẩm liên quan: <strong>{{ $novel->title }}</strong><br>
            • Mã hồ sơ khiếu nại: <strong>#{{ $report->id }}</strong><br>
            • Nội dung khiếu nại tóm tắt: <em>"{{ $report->description }}"</em><br>
            • Tình trạng hiện tại: <strong>Tạm ẩn công khai</strong>
        </div>

        <h3 style="font-size: 15px; color: #0f172a;">Quyền Lợi & Thủ Tục Thông Báo Phản Đối (Counter-Notice):</h3>
        <p>
            Theo quy định tại Điều 114 Nghị định 17/2023/NĐ-CP, bạn có thời hạn <strong>10 ngày làm việc</strong> kể từ thời điểm nhận được thông báo này để nộp <strong>Thông Báo Phản Đối (Counter-Notice)</strong> kèm tài liệu, chứng cứ chứng minh quyền tác giả hợp pháp của mình.
        </p>
        <p>
            Nếu bạn nộp giải trình hợp lệ và bên khiếu nại không xuất trình văn bản thụ lý vụ án của Tòa án hoặc Trọng tài trong thời hạn luật định, tác phẩm của bạn sẽ được <strong>tự động phục hồi công khai</strong> trên toàn hệ thống.
        </p>

        <div style="text-align: center; margin: 24px 0;">
            <a href="{{ route('author.counter-notice.create', $report) }}" class="btn">
                Nộp Thông Báo Phản Đối Trực Tuyến &rarr;
            </a>
        </div>

        <div class="footer">
            <p>Email này được phát hành tự động từ Hội đồng Thẩm định Bản quyền Số - 3D Novel Platform tuân thủ pháp luật Việt Nam. Mọi thắc mắc vui lòng phản hồi qua cổng quản trị tác giả.</p>
        </div>
    </div>
</body>
</html>
