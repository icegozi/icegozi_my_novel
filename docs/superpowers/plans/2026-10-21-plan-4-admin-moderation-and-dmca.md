# Kế Hoạch 4: Cổng Quản Trị Kiểm Duyệt & Xử Lý Khiếu Nại Bản Quyền Số (Notice & Takedown NĐ 17/2023)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Triển khai Cổng Quản trị Kiểm duyệt & Xử lý Khiếu nại Bản quyền Số (Notice-and-Takedown theo Nghị định 17/2023/NĐ-CP và Nghị định 147/2024/NĐ-CP); cung cấp hàng đợi kiểm duyệt truyện/chương 1-click mượt mà, highlight từ ngữ vi phạm an toàn trên DOM text nodes; quy trình xử lý tranh chấp bản quyền 2 chiều (Khiếu nại -> Tạm ẩn -> Thông báo phản đối Counter-Notice của tác giả -> Phục hồi hoặc Gỡ bỏ vĩnh viễn); duyệt hồ sơ tác giả e-KYC xóa ảnh tự động bảo vệ dữ liệu cá nhân theo Nghị định 13/2023/NĐ-CP và bảng đối soát nhật ký an ninh mạng Audit Log.

**Architecture:** Laravel 11 Blade + TailwindCSS + Alpine.js cho dashboard tốc độ cao; Mail Notification (Markdown Mailables) gửi thông báo pháp lý viện dẫn Luật SHTT Việt Nam; cơ chế xử lý tranh chấp bản quyền phân quyền Moderator/Admin; Middleware `EnsureAdminOrModerator`.

**Tech Stack:** PHP 8.2+, Laravel 11.x, Laravel Mail, TailwindCSS v3.4, Alpine.js v3, PHPUnit 11.

**Spec:** [docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md](docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md)

## Global Constraints

- **Quy trình Thông báo và Gỡ bỏ (Notice-and-Takedown) chuẩn Nghị định 17/2023/NĐ-CP:** Thay vì áp dụng quy chế nước ngoài, hệ thống tuân thủ pháp luật Việt Nam: Khi nhận đơn có tài liệu chứng minh quyền tác giả hợp lệ -> Tạm ẩn tác phẩm -> Gửi thông báo cho tác giả -> Tác giả có quyền gửi Thông Báo Phản Đối (Counter-Notice) trong 10 ngày làm việc. Nếu bên khiếu nại không xuất trình thụ lý vụ án của Tòa án/Trọng tài, hệ thống tự động phục hồi tác phẩm.
- **Bảo vệ Dữ liệu e-KYC (Nghị định 13/2023/NĐ-CP):** Admin xem ảnh CCCD qua Temporary Signed URL (TTL 10 phút). Khi duyệt xong hoặc từ chối, hệ thống tự động xóa file ảnh vật lý khỏi Private Disk, tuyệt đối không giữ ảnh CCCD vĩnh viễn.
- **Thao tác kiểm duyệt 1-click & phím tắt:** Kiểm duyệt viên có thể duyệt hoặc từ chối nhanh bằng phím tắt (`Alt + A` để Duyệt, `Alt + R` để Từ chối).

## Review Focus

1. **Kiểm duyệt viên lỡ tay bấm nhầm từ chối truyện:** Hệ thống yêu cầu xác nhận lý do mẫu từ dropdown trước khi thực hiện hành động, và cho phép xem lại lịch sử các tác phẩm vừa xử lý.
2. **Quy trình Thông báo phản đối (Counter-Notice):** Tác giả có thể nộp văn bản giải trình và bằng chứng quyền tác giả qua cổng quản lý truyện khi tác phẩm bị khiếu nại.
3. **Đơn tố cáo bản quyền ác ý (Spam Reports):** Admin có thể phân loại trạng thái `dismissed` (Bác đơn do thiếu căn cứ pháp lý) kèm thông báo lý do cho bên gửi đơn.
4. **An toàn hiển thị từ ngữ nhạy cảm:** `WordHighlighterService` thao tác an toàn trên text nodes / HTML escapes, không dùng regex thay thế chuỗi thô để tránh vỡ thẻ HTML và chống ReDoS.
5. **Thông báo email pháp lý:** Email viện dẫn chính xác Điều luật bản quyền Việt Nam (Nghị định 17/2023/NĐ-CP, Luật Sở hữu trí tuệ) và đính kèm liên kết gửi phản đối trực tuyến.

---

## Task 1: Hàng Đợi Kiểm Duyệt Tác Phẩm & Chương Truyện (Moderation Queue & 1-Click Action)
**Files:**
- Create: `app/Http/Controllers/Admin/ModerationQueueController.php`
- Create: `resources/views/admin/moderation/queue.blade.php`
- Create: `resources/views/admin/moderation/review_chapter.blade.php`
- Test: `tests/Feature/AdminModerationQueueTest.php`

**Interfaces:**
- Consumes: `novels.status = 'pending_approval'`, `chapters.status = 'pending_approval'`.
- Produces: Giao diện duyệt nội dung nhanh với các bộ lọc: Chứa từ khóa nhạy cảm, Tác phẩm mới, Chương mới; thao tác 1-click Duyệt / Từ chối (kèm lý do mẫu).

- [ ] **Step 1: Viết test kiểm tra danh sách hàng đợi kiểm duyệt và quyền truy cập của Moderator**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=AdminModerationQueueTest`
- [ ] **Step 3: Triển khai Controller, View Blade và phím tắt duyệt nhanh**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=AdminModerationQueueTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(admin): implement fast moderation queue and 1-click approvals"`

---

## Task 2: Highlight Từ Cấm An Toàn Trên Text Nodes (Safe Word Highlighter)
**Files:**
- Create: `app/Services/WordHighlighterService.php`
- Create: `resources/views/admin/moderation/partials/content_preview.blade.php`
- Test: `tests/Unit/WordHighlighterServiceTest.php`

**Interfaces:**
- Consumes: `chapters.content`, `content_filters.pattern`.
- Produces: `WordHighlighterService::highlight(string $content): string` tìm kiếm từ cấm an toàn trên text node, không phá hỏng thẻ HTML bên trong và chống ReDoS.

- [ ] **Step 1: Viết test unit kiểm tra highlight chính xác các từ nhạy cảm và không làm hỏng thẻ HTML**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=WordHighlighterServiceTest`
- [ ] **Step 3: Triển khai thuật toán thay thế an toàn DOM/Text node**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=WordHighlighterServiceTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(moderation): implement safe word highlighter on text nodes for reviewers"`

---

## Task 3: Cổng Xử Lý Khiếu Nại Bản Quyền Số & Counter-Notice (Notice & Takedown NĐ 17/2023)
**Files:**
- Create: `app/Http/Controllers/Legal/PublicReportController.php`
- Create: `app/Http/Controllers/Author/CounterNoticeController.php`
- Create: `app/Http/Controllers/Admin/CopyrightNoticeManagementController.php`
- Create: `resources/views/legal/copyright_report_form.blade.php`
- Create: `resources/views/author/counter_notice_form.blade.php`
- Create: `resources/views/admin/copyright/index.blade.php`
- Create: `resources/views/admin/copyright/show.blade.php`
- Test: `tests/Feature/CopyrightNoticeWorkflowTest.php`

**Interfaces:**
- Consumes: `reports`, `novels`.
- Produces: Form nộp thông báo vi phạm bản quyền công khai, dashboard quản trị xử lý đơn, nút "Tạm ẩn tác phẩm", form gửi Thông Báo Phản Đối (Counter-Notice) của tác giả và nút "Khôi phục tác phẩm sau đối soát".

- [ ] **Step 1: Viết test quy trình nộp đơn vi phạm bản quyền, tạm ẩn tác phẩm và gửi counter-notice của tác giả**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=CopyrightNoticeWorkflowTest`
- [ ] **Step 3: Triển khai các Controller, Views Blade chuẩn quy trình NĐ 17/2023/NĐ-CP**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=CopyrightNoticeWorkflowTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(copyright): implement notice and takedown workflow with author counter-notice"`

---

## Task 4: Phê Duyệt Hồ Sơ Định Danh Tác Giả e-KYC & Auto-Delete Ảnh (Retention Policy)
**Files:**
- Create: `app/Http/Controllers/Admin/AuthorKycReviewController.php`
- Create: `resources/views/admin/kyc/index.blade.php`
- Create: `resources/views/admin/kyc/show.blade.php`
- Test: `tests/Feature/AuthorKycReviewTest.php`

**Interfaces:**
- Consumes: `author_profiles.verification_status = 'pending'`, `IdentityEncryptionService`.
- Produces: Giao diện xem xét đối soát thông tin CCCD qua Temporary Signed URL, bấm Duyệt nâng quyền `author` (tự động xóa file ảnh CCCD vật lý khỏi disk bảo vệ quyền riêng tư) hoặc Từ chối kèm lý do.

- [ ] **Step 1: Viết test chức năng duyệt hồ sơ e-KYC, xác nhận xóa file ảnh CCCD sau khi duyệt xong**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=AuthorKycReviewTest`
- [ ] **Step 3: Triển khai Controller, View giải mã bảo mật và cơ chế tự động xóa ảnh sau thẩm định**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=AuthorKycReviewTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(kyc): implement author kyc review with automatic cccd image retention policy"`

---

## Task 5: Hệ Thống Mail Thông Báo Pháp Lý Chuẩn Luật SHTT (Legal Notifications)
**Files:**
- Create: `app/Mail/NovelTakedownNoticeMail.php`
- Create: `app/Mail/AuthorKycResultMail.php`
- Create: `resources/views/emails/legal/takedown_notice.blade.php`
- Create: `resources/views/emails/legal/kyc_result.blade.php`
- Test: `tests/Feature/LegalNotificationMailTest.php`

**Interfaces:**
- Consumes: Laravel Mailables.
- Produces: Mẫu email chuẩn pháp lý gửi cho tác giả khi tác phẩm bị khiếu nại bản quyền (hướng dẫn nộp tài liệu giải trình/counter-notice) và email thông báo kết quả duyệt e-KYC.

- [ ] **Step 1: Viết test gửi email thông báo bản quyền và kiểm tra nội dung viện dẫn pháp lý NĐ 17/2023**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=LegalNotificationMailTest`
- [ ] **Step 3: Xây dựng Mailables và View Blade email giao diện trang trọng, chuyên nghiệp**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=LegalNotificationMailTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(legal): implement legal email notification templates with nd 17/2023 references"`

---

## Task 6: Bảng Tra Cứu & Đối Soát Nhật Ký An Ninh Mạng Audit Log (Audit Trail Explorer)
**Files:**
- Create: `app/Http/Controllers/Admin/AuditLogExplorerController.php`
- Create: `resources/views/admin/audit_logs/index.blade.php`
- Test: `tests/Feature/AuditLogExplorerTest.php`

**Interfaces:**
- Consumes: `audit_logs`, `users`.
- Produces: Bảng đối soát nhật ký an ninh mạng: Tìm kiếm theo IP, thời gian, loại hành động (duyệt truyện, ẩn truyện, nộp e-KYC), xuất dữ liệu phục vụ thanh tra an ninh mạng theo NĐ 147/2024.

- [ ] **Step 1: Viết test truy vấn nhật ký an ninh mạng và bộ lọc theo địa chỉ IP/thời gian**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=AuditLogExplorerTest`
- [ ] **Step 3: Triển khai Controller và View Blade bảng tra cứu nhật ký audit trail**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=AuditLogExplorerTest`
- [ ] **Step 5: Commit mã nguồn hoàn thiện Kế hoạch 4**
  `git add app/ resources/ tests/ && git commit -m "feat(audit): implement audit trail explorer for cybersecurity compliance"`
