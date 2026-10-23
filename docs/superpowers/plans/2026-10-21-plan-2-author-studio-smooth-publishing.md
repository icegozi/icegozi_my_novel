# Kế Hoạch 2: Hệ Thống Tác Giả & Trình Đăng Bài Siêu Mượt (Author Studio)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng Author Studio hoàn chỉnh với trải nghiệm soạn thảo và xuất bản truyện không độ trễ (Zero Latency Typing, Auto-save kép IndexedDB + Server Debounce với Revision Lock), hỗ trợ viết tiếp khi mất mạng (Offline Resilience), công cụ tách chương tự động hàng loạt, làm sạch nội dung chống XSS, trình quản lý tải lên mô hình 3D (.glb) an toàn và Scheduled Command tự động phát hành chương theo lịch hẹn.

**Architecture:** Frontend kết hợp Blade Components, Alpine.js, IndexedDB (với `idb-keyval` / native IndexedDB) làm bộ đệm ngoại tuyến tức thời không giới hạn dung lượng 5MB; Three.js client-side thumbnail renderer cho model 3D; Backend Laravel 11 Controller xử lý API autosave với `revision` & `client_uuid` chống ghi đè chéo; Service HTMLPurifier chống XSS; Queue Job phân luồng ngầm quét từ khóa nhạy cảm; Artisan Command phát hành chương hẹn giờ.

**Tech Stack:** PHP 8.2+, Laravel 11.x, Alpine.js v3, TailwindCSS v3.4, Three.js (GLTFLoader), Tiptap / Markdown Engine, HTMLPurifier, Laravel Queue & Scheduler.

**Spec:** [docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md](docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md)

## Global Constraints

- **Chống ghi đè & Quota bền vững (IndexedDB & Optimistic Locking):** Dữ liệu soạn thảo được ghi tức thời vào IndexedDB phía client. Request autosave gửi kèm `revision` và `client_uuid`; nếu server phát hiện bản ghi đã có `revision` cao hơn thì từ chối ghi đè và trả cảnh báo xung đột (Conflict Warning).
- **Làm sạch nội dung (Content Sanitization):** Mọi văn bản chương trước khi lưu vào CSDL bắt buộc chạy qua bộ lọc HTMLPurifier để loại bỏ triệt để các thẻ `<script>`, `onerror`, `javascript:` độc hại.
- **Khả năng phục hồi khi mất mạng (Offline Resilience):** Khi mất kết nối Internet, trạng thái lưu chuyển sang "Đang lưu trên máy (Offline)", dữ liệu không bị mất và tự động đồng bộ khi có mạng trở lại.
- **Tự động kích hoạt chương hẹn giờ:** Phải có Scheduled Command chạy định kỳ mỗi phút qua Cron để chuyển trạng thái chương từ `scheduled` sang `published`.

## Review Focus

1. **Hai tab mở cùng một chương hoặc request gửi trễ:** Kiểm tra cơ chế `revision`: server chỉ chấp nhận bản ghi có `revision >= current_revision`.
2. **File 3D (.glb) tải lên vượt quá dung lượng cho phép hoặc chứa buffer độc hại:** Kiểm tra magic byte `glTF`, giới hạn 15MB, cấu hình CORS và symbolic link (`php artisan storage:link`).
3. **Chương truyện dài chứa hàng chục nghìn từ:** Trình tách chương (Bulk Chapter Splitter) xử lý mượt mà bằng regex tối ưu trong dưới 1 giây mà không gây treo tab.
4. **Nội dung chương chứa từ ngữ kích động bạo lực/đồi trụy:** Job `ScanSensitiveContentJob` chạy ngầm, áp dụng regex an toàn chống ReDoS, tự động gắn cờ `status = 'pending_approval'` để Admin đối soát.
5. **Gợi ý số chương thông minh:** Hỗ trợ cả số nguyên và số thập phân (ngoại truyện, VD: `101.5` -> gợi ý tiếp theo là `102`).

---

## Task 1: Quản Lý Tác Phẩm Truyện (Novel CRUD, Cover Image & Copyright Declaration)
**Files:**
- Create: `app/Http/Controllers/Author/NovelController.php`
- Create: `app/Http/Requests/StoreNovelRequest.php`
- Create: `resources/views/author/novels/index.blade.php`
- Create: `resources/views/author/novels/create_or_edit.blade.php`
- Test: `tests/Feature/AuthorNovelCrudTest.php`

**Interfaces:**
- Consumes: `novels`, `genres`, `author_profiles`.
- Produces: Giao diện quản lý danh sách truyện của tác giả, thêm truyện mới kèm khai báo bản quyền (`copyright_type`), gắn nhãn độ tuổi (`13+`, `16+`, `18+`) và xử lý tối ưu ảnh bìa WebP.

- [ ] **Step 1: Viết test cho chức năng tạo, cập nhật truyện và kiểm tra quyền sở hữu tác giả**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=AuthorNovelCrudTest`
- [ ] **Step 3: Triển khai Controller, Request validation, View Blade với Tailwind CSS**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=AuthorNovelCrudTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(author): implement novel management and copyright declaration"`

---

## Task 2: Trình Soạn Thảo Zen Editor Siêu Mượt & Chống XSS (IndexedDB + Revision Lock)
**Files:**
- Create: `app/Http/Controllers/Author/ChapterEditorController.php`
- Create: `app/Services/ContentSanitizerService.php`
- Create: `resources/views/author/chapters/editor.blade.php`
- Create: `resources/js/zen-editor.js`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/ChapterAutoSaveApiTest.php`

**Interfaces:**
- Consumes: `/api/author/chapters/autosave` (JSON request với `content`, `revision`, `client_uuid`).
- Produces: Endpoint lưu nháp ngầm có khóa revision, bộ đệm IndexedDB client-side không lo đầy bộ nhớ, làm sạch mã độc bằng `ContentSanitizerService`, chế độ gõ toàn màn hình (Zen Mode), thống kê số từ thời gian thực.

- [ ] **Step 1: Viết test API autosave kiểm tra lưu nháp thành công, chặn ghi đè revision cũ và sanitize mã XSS `<script>`**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ChapterAutoSaveApiTest`
- [ ] **Step 3: Triển khai Controller, Service làm sạch HTML, viết file `zen-editor.js` với IndexedDB + debounce 3s**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ChapterAutoSaveApiTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(editor): implement ultra-smooth indexeddb auto-save with revision lock and xss sanitization"`

---

## Task 3: Hàng Đợi Quét Từ Khóa Cấm & Nhạy Cảm Chống ReDoS (Async Filter Job)
**Files:**
- Create: `app/Jobs/ScanSensitiveContentJob.php`
- Create: `app/Services/ContentFilterService.php`
- Test: `tests/Unit/ScanSensitiveContentJobTest.php`

**Interfaces:**
- Consumes: `content_filters`, `chapters`.
- Produces: Service quét nội dung dựa trên danh mục từ khóa an toàn (chống ReDoS), chuẩn hóa tiếng Việt không dấu; Job đẩy chương vào hàng đợi kiểm duyệt nếu phát hiện từ cấm.

- [ ] **Step 1: Viết test kiểm tra ContentFilterService phát hiện chính xác từ khóa nhạy cảm và gán cờ kiểm duyệt**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ScanSensitiveContentJobTest`
- [ ] **Step 3: Triển khai Service và Queue Job với cơ chế timeout bảo vệ**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ScanSensitiveContentJobTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ tests/ && git commit -m "feat(moderation): implement async sensitive content screening job with redos protection"`

---

## Task 4: Tiện Ích Tách Chương Tự Động Hàng Loạt (Bulk Chapter Splitter Utility)
**Files:**
- Create: `app/Services/ChapterSplitterService.php`
- Create: `app/Http/Controllers/Author/BulkChapterController.php`
- Create: `resources/views/author/chapters/bulk_splitter.blade.php`
- Test: `tests/Unit/ChapterSplitterServiceTest.php`

**Interfaces:**
- Consumes: Văn bản dài chứa nhiều chương (text payload).
- Produces: `ChapterSplitterService::split(string $rawText): array` tách tự động các chương theo định dạng `Chương X: [Tên]` hoặc `Hồi X: [Tên]` thành danh sách bản nháp có sẵn.

- [ ] **Step 1: Viết test unit kiểm tra bóc tách văn bản 50 chương liên tiếp đúng thứ tự và không sót nội dung**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ChapterSplitterServiceTest`
- [ ] **Step 3: Viết thuật toán phân tách biểu thức chính quy (Regex) và giao diện dán text xem trước**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ChapterSplitterServiceTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(author): implement bulk chapter splitter utility"`

---

## Task 5: Quản Lý & Tải Lên Kho Mô Hình 3D Cụ Hóa (3D Asset Gallery & Storage Link)
**Files:**
- Create: `app/Http/Controllers/Author/Novel3dAssetController.php`
- Create: `app/Http/Requests/Upload3dAssetRequest.php`
- Create: `resources/views/author/3d_assets/index.blade.php`
- Create: `resources/js/3d-asset-uploader.js`
- Test: `tests/Feature/Novel3dAssetManagementTest.php`

**Interfaces:**
- Consumes: File `.glb`, `novel_3d_assets`.
- Produces: Upload mô hình 3D an toàn (kiểm tra binary header `glTF`), thiết lập symbolic link `php artisan storage:link`, chọn loại tài sản, chọn hiệu ứng hạt, cấu hình góc xoay mặc định.

- [ ] **Step 1: Viết test upload file .glb hợp lệ, kiểm tra magic header và chặn file giả mạo**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=Novel3dAssetManagementTest`
- [ ] **Step 3: Triển khai Controller, form upload drag-and-drop và viewer Three.js xem trước**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=Novel3dAssetManagementTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(3d): implement 3d asset manager and live preview with storage linking"`

---

## Task 6: Tích Hợp Gán Mô Hình 3D Vào Đoạn Văn Bản (Chapter 3D Projection Linker)
**Files:**
- Create: `app/Http/Controllers/Author/Chapter3dProjectionController.php`
- Create: `resources/views/author/chapters/partials/3d_projection_modal.blade.php`
- Modify: `resources/js/zen-editor.js`
- Test: `tests/Feature/Chapter3dProjectionLinkerTest.php`

**Interfaces:**
- Consumes: `chapter_3d_projections`, `novel_3d_assets`.
- Produces: Cho phép tác giả bôi đen hoặc chọn vị trí đoạn văn thứ N trong bài viết, chọn mô hình 3D tương ứng trong kho để gán hiệu ứng chiếu cụ hóa.

- [ ] **Step 1: Viết test API lưu liên kết mô hình 3D với đoạn văn thứ N của chương**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=Chapter3dProjectionLinkerTest`
- [ ] **Step 3: Triển khai Controller, Modal chọn asset 3D và nhúng tag kích hoạt vào editor**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=Chapter3dProjectionLinkerTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(editor): link 3d models to chapter paragraphs"`

---

## Task 7: Command Tự Động Xuất Bản Chương Hẹn Giờ (Scheduled Chapters Command)
**Files:**
- Create: `app/Console/Commands/PublishScheduledChaptersCommand.php`
- Modify: `routes/console.php`
- Test: `tests/Feature/PublishScheduledChaptersCommandTest.php`

**Interfaces:**
- Consumes: `chapters.status = 'scheduled'`, `scheduled_at <= now()`.
- Produces: Artisan Command `app:publish-scheduled-chapters` quét các chương đến giờ phát hành, chuyển trạng thái sang `published`, cập nhật `published_at`, ghi AuditLog và đăng ký vào Laravel Scheduler (`Schedule::command('app:publish-scheduled-chapters')->everyMinute()`).

- [ ] **Step 1: Viết test cho Command kích hoạt xuất bản đúng các chương đã đến giờ hẹn**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=PublishScheduledChaptersCommandTest`
- [ ] **Step 3: Triển khai Command và cấu hình lịch chạy trong `routes/console.php`**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=PublishScheduledChaptersCommandTest`
- [ ] **Step 5: Commit mã nguồn hoàn thiện Kế hoạch 2**
  `git add app/Console/ routes/console.php tests/ && git commit -m "feat(scheduler): implement automated scheduled chapters publisher command"`
