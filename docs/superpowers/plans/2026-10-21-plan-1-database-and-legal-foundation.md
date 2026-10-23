# Kế Hoạch 1: Cơ Sở Dữ Liệu Nền Tảng & Phân Hệ Pháp Lý, e-KYC Tác Giả

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng toàn bộ lược đồ cơ sở dữ liệu quan hệ cho nền tảng đọc truyện 3D và triển khai phân hệ định danh pháp lý tác giả (e-KYC CCCD, xác thực SĐT theo Nghị định 147/2024/NĐ-CP, cam kết bản quyền số theo Nghị định 17/2023/NĐ-CP, bảo vệ dữ liệu cá nhân theo Nghị định 13/2023/NĐ-CP, và hệ thống nhật ký an ninh mạng Audit Log).

**Architecture:** Laravel 11 Eloquent ORM kết hợp SQLite (Local Dev) / MySQL; cơ chế mã hóa AES-256 cho số định danh CCCD và HMAC-SHA256 với secret pepper cho tra cứu unique; lưu trữ ảnh CCCD trên Private Disk với Signed URL tạm thời và tự động xóa sau thẩm định (Data Retention Policy); Service xử lý AuditLog ghi nhận IP và User-Agent cho mọi hành động pháp lý; Middleware kiểm tra quyền xuất bản dựa trên trạng thái xác thực.

**Tech Stack:** PHP 8.2+, Laravel 11.x, Eloquent ORM, PHPUnit 11, TailwindCSS.

**Spec:** [docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md](docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md)

## Global Constraints

- **Bảo mật dữ liệu định danh (Nghị định 13/2023/NĐ-CP):** Số CCCD bắt buộc mã hóa AES-256 bằng `Crypt::encryptString()`. Tra cứu unique dùng `hash_hmac('sha256', $idNumber, config('app.identity_pepper'))` (tuyệt đối không dùng hash SHA-256 trần vì không gian 12 số dễ bị vét cạn).
- **Vòng đời lưu trữ ảnh CCCD (Data Retention):** File ảnh CCCD phải lưu tại `storage/app/private/identity_documents` (Private Disk). Khi admin duyệt hoặc từ chối e-KYC, hệ thống xóa file vật lý và chỉ lưu vết thẩm định trong Audit Log.
- **Cam kết pháp lý bất biến:** Bản ghi `legal_consents` một khi đã ghi nhận thì không được phép xóa (`immutability`), lưu giữ IP và User-Agent tại thời điểm người dùng bấm ký.
- **Quyền xuất bản nội dung:** Chỉ tài khoản có `role = 'author'` và `author_profiles.verification_status = 'verified'` mới được cấp quyền tạo hoặc đăng truyện.

## Review Focus

1. **Người dùng chưa xác thực CCCD cố tình gọi API tạo truyện:** Middleware `EnsureAuthorVerified` phải chặn lại với mã lỗi HTTP 403 Forbidden và chuyển hướng đến trang nộp hồ sơ e-KYC.
2. **Trường số CCCD trùng lặp:** Hệ thống dùng `identity_hash = hash_hmac('sha256', $idNumber, config('app.identity_pepper'))` để kiểm tra unique mà không làm lộ dữ liệu thô.
3. **Người dùng giả mạo IP khi ký cam kết pháp lý:** Lưu trữ cả `request()->ip()` và proxy header (`X-Forwarded-For`) đã qua validate.
4. **Xác thực số điện thoại theo NĐ 147/2024/NĐ-CP:** Kiểm tra định dạng đầu số viễn thông Việt Nam (10 số, bắt đầu bằng 03, 05, 07, 08, 09).
5. **Rollback migration sạch sẽ:** Mọi migration phải viết đầy đủ phương thức `down()` tương ứng và không bị lỗi khóa ngoại khi chạy lại `migrate:fresh`.

---

## Task 1: Migration & Model cho Tài Khoản, e-KYC và Cam Kết Pháp Lý
**Files:**
- Create: `database/migrations/2026_10_21_000001_create_legal_and_author_profiles_tables.php`
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`
- Create: `app/Models/AuthorProfile.php`
- Create: `app/Models/LegalConsent.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/LegalUserSchemaTest.php`

**Interfaces:**
- Consumes: Laravel Schema Builder.
- Produces: `User` model quan hệ `hasOne(AuthorProfile::class)` và `hasMany(LegalConsent::class)`. `author_profiles` có các cột: `identity_number_encrypted`, `identity_hash` (indexed), `verification_status`, `identity_front_image`, `identity_back_image`.

- [ ] **Step 1: Viết test kiểm tra cấu trúc bảng users, author_profiles và legal_consents kèm cột identity_hash**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=LegalUserSchemaTest`
- [ ] **Step 3: Triển khai cập nhật migration users và tạo migration author_profiles, legal_consents; định nghĩa quan hệ trong Models**
- [ ] **Step 4: Chạy migration và chạy lại test xác nhận pass**
  `php artisan migrate && php artisan test --filter=LegalUserSchemaTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add database/ app/Models/ tests/ && git commit -m "feat(legal): add author profile and legal consent schema with hmac index"`

---

## Task 2: Migration & Model cho Truyện, Thể Loại, Chương, Tiến Độ & Tương Tác
**Files:**
- Create: `database/migrations/2026_10_21_000002_create_novels_and_genres_tables.php`
- Create: `database/migrations/2026_10_21_000003_create_chapters_and_interactions_tables.php`
- Create: `app/Models/Novel.php`
- Create: `app/Models/Genre.php`
- Create: `app/Models/Chapter.php`
- Create: `app/Models/ReadingHistory.php`
- Create: `app/Models/ChapterReadingProgress.php`
- Create: `app/Models/Bookmark.php`
- Create: `app/Models/ChapterComment.php`
- Test: `tests/Feature/NovelChapterSchemaTest.php`

**Interfaces:**
- Consumes: Task 1 (`users` table).
- Produces:
  - `reading_history`: lưu chương đọc gần nhất của cả truyện, unique `[user_id, novel_id]`.
  - `chapter_reading_progress`: lưu tiến độ cuộn và vị trí đoạn văn chính xác cho từng chương, unique `[user_id, chapter_id]`.
  - `chapters`: có `status` ('draft', 'pending_approval', 'scheduled', 'published', 'rejected'), `scheduled_at`, `word_count`.

- [ ] **Step 1: Viết test kiểm tra bảng novels, genres, chapters, reading_history, chapter_reading_progress và các ràng buộc khóa ngoại**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=NovelChapterSchemaTest`
- [ ] **Step 3: Tạo migration và hoàn thiện Models với các relations (belongsTo, hasMany, belongsToMany)**
- [ ] **Step 4: Chạy migration và chạy test xác nhận pass**
  `php artisan migrate && php artisan test --filter=NovelChapterSchemaTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add database/ app/Models/ tests/ && git commit -m "feat(novel): add novel, genre, chapter and paragraph-accurate progress schemas"`

---

## Task 3: Migration & Model cho Kho Asset 3D và Bản Chiếu Cụ Hóa
**Files:**
- Create: `database/migrations/2026_10_21_000004_create_3d_assets_and_projections_tables.php`
- Create: `app/Models/Novel3dAsset.php`
- Create: `app/Models/Chapter3dProjection.php`
- Test: `tests/Feature/Novel3dSchemaTest.php`

**Interfaces:**
- Consumes: `novels`, `chapters`.
- Produces: Model `Novel3dAsset` (kho mô hình 3D của truyện, `is_private` boolean, `scale_factor`) và `Chapter3dProjection` (gắn mô hình vào đoạn văn bản thứ N).

- [ ] **Step 1: Viết test cho bảng novel_3d_assets và chapter_3d_projections**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=Novel3dSchemaTest`
- [ ] **Step 3: Tạo migration và định nghĩa Model với cấu hình JSON casting cho camera_init_position**
- [ ] **Step 4: Chạy migration và kiểm tra pass**
  `php artisan migrate && php artisan test --filter=Novel3dSchemaTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add database/ app/Models/ tests/ && git commit -m "feat(3d): add 3d asset and projection schema"`

---

## Task 4: Migration & Model cho Báo Cáo Vi Phạm (Nghị Định 17/2023), Bộ Lọc & Audit Log
**Files:**
- Create: `database/migrations/2026_10_21_000005_create_reports_filters_and_audit_logs_tables.php`
- Create: `app/Models/Report.php`
- Create: `app/Models/ContentFilter.php`
- Create: `app/Models/AuditLog.php`
- Test: `tests/Feature/LegalComplianceSchemaTest.php`

**Interfaces:**
- Consumes: `users`, `novels`.
- Produces:
  - `reports`: hỗ trợ quy trình Thông báo và Gỡ bỏ (Notice-and-Takedown) theo NĐ 17/2023/NĐ-CP kèm thông báo phản đối của tác giả (`counter_notice_text`, `counter_notice_evidence_urls`, `counter_submitted_at`).
  - `audit_logs`: index trên `(target_type, target_id)`, `(ip_address, created_at)`.
  - `content_filters`: từ điển từ khóa cấm/nhạy cảm.

- [ ] **Step 1: Viết test cho bảng reports, audit_logs và content_filters**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=LegalComplianceSchemaTest`
- [ ] **Step 3: Viết migration và định nghĩa Models kèm quan hệ polymorphic morphTo**
- [ ] **Step 4: Chạy migration và chạy test xác nhận pass**
  `php artisan migrate && php artisan test --filter=LegalComplianceSchemaTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add database/ app/Models/ tests/ && git commit -m "feat(compliance): add notice-and-takedown reports and audit log schema"`

---

## Task 5: Service AuditLog & Bảo Mật Định Danh HMAC Pepper (Legal Security Service)
**Files:**
- Create: `app/Services/AuditLogService.php`
- Create: `app/Services/IdentityEncryptionService.php`
- Modify: `config/app.php`
- Test: `tests/Unit/LegalSecurityServiceTest.php`

**Interfaces:**
- Consumes: `AuditLog`, `request()`, `Crypt`, `config('app.identity_pepper')`.
- Produces: 
  - `AuditLogService::log(User $user, string $action, string $type, int $id, array $old = [], array $new = []): AuditLog`
  - `IdentityEncryptionService::encryptAndHash(string $idNumber): array` (`['encrypted' => ..., 'hash' => hash_hmac('sha256', $idNumber, pepper)]`)
  - `IdentityEncryptionService::decrypt(string $encrypted): string`

- [ ] **Step 1: Viết test unit kiểm tra mã hóa/giải mã số định danh, tính toán HMAC với pepper và ghi nhật ký IP tự động**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=LegalSecurityServiceTest`
- [ ] **Step 3: Triển khai mã nguồn cho AuditLogService và IdentityEncryptionService**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=LegalSecurityServiceTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/Services/ config/ tests/ && git commit -m "feat(security): implement identity hmac pepper encryption and audit log service"`

---

## Task 6: Giao Diện & Quy Trình Ký Cam Kết Bản Quyền & Nộp Hồ Sơ Tác Giả (e-KYC)
**Files:**
- Create: `app/Http/Controllers/Legal/AuthorRegistrationController.php`
- Create: `app/Http/Requests/SubmitAuthorRegistrationRequest.php`
- Create: `app/Http/Middleware/EnsureAuthorVerified.php`
- Create: `resources/views/legal/terms_and_copyright_policy.blade.php`
- Create: `resources/views/author/kyc_registration.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AuthorRegistrationWorkflowTest.php`

**Interfaces:**
- Consumes: `IdentityEncryptionService`, `AuditLogService`, `author_profiles`, `legal_consents`.
- Produces: Tuyến đường `/tac-gia/dang-ky-dinh-danh`, xử lý upload ảnh CCCD vào Private Disk (`identity_documents`), lưu vết ký số cam kết bản quyền NĐ 17/2023.

- [ ] **Step 1: Viết test quy trình nộp hồ sơ e-KYC, xác thực SĐT VN, upload private disk và chặn truy cập trái phép bằng Middleware**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=AuthorRegistrationWorkflowTest`
- [ ] **Step 3: Triển khai Request validation, Controller, Middleware và View Blade chuẩn giao diện Tailwind**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=AuthorRegistrationWorkflowTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ routes/ tests/ && git commit -m "feat(kyc): complete author registration and legal consent workflow"`

---

## Task 7: Database Seeder Khởi Tạo Dữ Liệu Nền Tảng (Seeders)
**Files:**
- Create: `database/seeders/NovelPlatformSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/NovelPlatformSeederTest.php`

**Interfaces:**
- Consumes: Models đã tạo ở các Task 1-4.
- Produces: Dữ liệu mẫu hoàn chỉnh:
  - 1 tài khoản Admin (`admin@novel3d.vn`)
  - 1 tài khoản Tác giả đã duyệt e-KYC (`author@novel3d.vn`)
  - Danh mục thể loại mẫu: Tiên hiệp, Kiếm hiệp, Huyền huyễn, Khoa huyễn, Đô thị
  - 1 tác phẩm mẫu đính kèm 3 chương và 1 asset 3D mẫu phục vụ kiểm thử nhanh

- [ ] **Step 1: Viết test kiểm tra Seeder chạy thành công và tạo đủ các bản ghi cốt lõi**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=NovelPlatformSeederTest`
- [ ] **Step 3: Viết mã nguồn cho NovelPlatformSeeder.php**
- [ ] **Step 4: Chạy `php artisan db:seed` và chạy test xác nhận pass**
  `php artisan test --filter=NovelPlatformSeederTest`
- [ ] **Step 5: Commit mã nguồn hoàn thiện Kế hoạch 1**
  `git add database/seeders/ tests/ && git commit -m "feat(seed): add novel platform database seeder for test and development"`
