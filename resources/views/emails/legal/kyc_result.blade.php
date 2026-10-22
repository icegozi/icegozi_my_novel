<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết Quả Thẩm Định Hồ Sơ Định Danh Tác Giả e-KYC</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; padding: 24px; margin: 0; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 24px; }
        .badge-success { display: inline-block; background-color: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .badge-warning { display: inline-block; background-color: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .highlight-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin: 20px 0; font-size: 14px; }
        .footer { margin-top: 32px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        @if($status === 'verified')
            <div class="header">
                <span class="badge-success">ĐỊNH DANH THÀNH CÔNG • NĐ 147/2024/NĐ-CP</span>
                <h2 style="color: #0f172a; margin-top: 12px; font-size: 20px;">Chúc Mừng! Hồ Sơ Tác Giả Đã Được Phê Duyệt</h2>
            </div>

            <p>Xin chào tác giả <strong>{{ $profile->pen_name }}</strong>,</p>

            <p>
                Hồ sơ định danh Căn Cước Công Dân (e-KYC) của bạn đã được <strong>phê duyệt thành công</strong> theo đúng quy định của <strong>Nghị định 147/2024/NĐ-CP</strong> về quản lý dịch vụ Internet và thông tin trên mạng.
            </p>

            <div class="highlight-box">
                <strong>Quyền lợi của bạn đã được kích hoạt:</strong><br>
                • Xuất bản tiểu thuyết và chương truyện trực tiếp trên nền tảng.<br>
                • Tải lên và cấu hình vật thể không gian 3D tương tác cho tác phẩm.<br>
                • Đủ điều kiện nhận doanh thu và ký kết hợp đồng bản quyền số.
            </div>

            <p style="font-size: 13px; color: #475569;">
                <em>Cam kết bảo mật dữ liệu:</em> Theo quy định tại <strong>Nghị định 13/2023/NĐ-CP</strong> về bảo vệ dữ liệu cá nhân, toàn bộ hình ảnh CCCD vật lý bạn đã nộp đã được hệ thống <strong>xóa vĩnh viễn khỏi máy chủ lưu trữ</strong> ngay sau khi quá trình đối soát kết thúc.
            </p>
        @else
            <div class="header">
                <span class="badge-warning">CẦN BỔ SUNG HỒ SƠ • NĐ 147/2024/NĐ-CP</span>
                <h2 style="color: #0f172a; margin-top: 12px; font-size: 20px;">Thông Báo Hồ Sơ e-KYC Chưa Được Phê Duyệt</h2>
            </div>

            <p>Xin chào <strong>{{ $profile->pen_name }}</strong>,</p>

            <p>
                Rất tiếc, hồ sơ đăng ký định danh tác giả của bạn <strong>chưa được phê duyệt</strong> do thông tin hoặc hình ảnh giấy tờ tùy thân chưa đạt tiêu chuẩn thẩm định.
            </p>

            <div class="highlight-box" style="border-left: 4px solid #ef4444; background-color: #fff1f2;">
                <strong>Lý do từ chối cụ thể:</strong><br>
                <span style="color: #991b1b;">{{ $reason ?? 'Thông tin không khớp hoặc ảnh chụp không đủ độ rõ nét.' }}</span>
            </div>

            <p>
                Bạn vui lòng đăng nhập vào trang quản trị tác giả và thực hiện nộp lại ảnh chụp CCCD rõ nét để chúng tôi nhanh chóng kích hoạt tài khoản xuất bản cho bạn.
            </p>
        @endif

        <div class="footer">
            <p>Hệ thống Quản trị Bản quyền & Xuất bản 3D Novel Platform. Trân trọng cảm ơn sự hợp tác của bạn.</p>
        </div>
    </div>
</body>
</html>
