# Kế hoạch Triển khai Nền tảng Đăng bài & Đọc truyện 3D Cụ Hóa (Chuẩn Pháp lý & Trải nghiệm Siêu mượt)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Xây dựng hệ thống web đọc và đăng truyện hoàn chỉnh trên nền tảng Laravel 11 + TailwindCSS + Three.js, đáp ứng đầy đủ hành lang pháp lý xuất bản nội dung số tại Việt Nam (Nghị định 147/2024/NĐ-CP, Luật Sở hữu trí tuệ), tích hợp trình diễn hình chiếu 3D cụ hóa bối cảnh/nhân vật tương tác trong khi đọc, cùng quy trình đăng bài - kiểm duyệt không giật lag (ultra-smooth author & admin publishing UX).

**Architecture:** Kiến trúc monolithic modular trên Laravel 11; giao diện kết hợp Blade Component, Tailwind CSS, Alpine.js và Three.js/WebGL cho trải nghiệm 3D; cơ sở dữ liệu quan hệ tối ưu hóa index; hệ thống queue xử lý bất đồng bộ (Async Queue) để nén asset 3D, chuyển đổi ảnh WebP và quét tự động từ khóa cấm/nhạy cảm; kiến trúc phân quyền đa tầng (RBAC: Reader, Author, Moderator, Admin).

**Tech Stack:**
- **Backend:** PHP 8.2+, Laravel 11.x, SQLite (dev) / MySQL/PostgreSQL (prod), Redis/Database Queue.
- **Frontend Core:** Blade Templates, TailwindCSS v3.4, Alpine.js v3.x.
- **3D Visualization Engine:** Three.js (WebGL), GLTFLoader, OrbitControls, DRACOLoader.
- **Editor & Smooth UX:** Tiptap Editor / ProseMirror (hoặc EasyMDE), LocalStorage/IndexedDB Auto-save + Debounced API Sync.
- **Testing:** PHPUnit 11, Laravel Dusk / Pest (nếu có), Mockery.

**Spec:** Bản đặc tả kỹ thuật và thiết kế hệ thống đọc truyện 3D, pháp lý số và trải nghiệm xuất bản siêu mượt (được quy định chi tiết trong tài liệu này).

## Global Constraints

- **Tuân thủ Pháp lý:** Mọi tài khoản đăng bài bắt buộc phải hoàn thành bước ký cam kết bản quyền số và xác thực thông tin định danh (SĐT/CCCD theo NĐ 147/2024/NĐ-CP). Mọi tác phẩm phải khai báo rõ nguồn gốc tác quyền (`original`, `authorized_translation`, `public_domain`).
- **Gắn nhãn độ tuổi & Cảnh báo:** Hệ thống bắt buộc phân loại nhãn độ tuổi (`ALL`, `13+`, `16+`, `18+`) và cảnh báo nội dung nhạy cảm trước khi xuất bản.
- **Cơ chế DMCA / Takedown:** Cung cấp kênh báo cáo vi phạm bản quyền trực tiếp, tự động chuyển truyện vào trạng thái xem xét bảo vệ tác quyền khi có yêu cầu hợp lệ.
- **Hiệu năng 3D (Smooth 3D FPS):** Render 3D qua Three.js phải đạt tối thiểu 45-60 FPS, hỗ trợ WebGL fallback tự động sang chế độ 2D thuần nếu thiết bị không hỗ trợ GPU hoặc bị mất context (`webglcontextlost`).
- **Smooth Publishing UX:** Trình soạn thảo phải có bộ đệm lưu nháp tự động mỗi 3 giây (debounce), giữ nguyên nội dung khi mất mạng (Offline Resilience qua LocalStorage), tải file ảnh và mô hình 3D qua Background Queue không gây đơ UI.
- **Audit Logs:** Toàn bộ lịch sử duyệt, sửa, xóa, khóa truyện và thông tin IP của tác giả/admin phải được lưu giữ tối thiểu 2 năm phục vụ đối soát.

## Review Focus

1. **Rớt mạng khi tác giả đang gõ dở chương truyện dài:** Hệ thống phải lưu ngay lập tức vào LocalStorage, tự động đồng bộ lên server khi có mạng trở lại mà không mất ký tự nào.
2. **Thiết bị yếu / GPU không hỗ trợ WebGL khi đọc truyện 3D:** Three.js reader phải bắt exception nhẹ nhàng, tự động hạ cấp xuống giao diện đọc văn bản mượt mà kèm ảnh 2D tĩnh không làm vỡ trang.
3. **Tác giả đăng tải tác phẩm vi phạm từ khóa cấm hoặc thuần phong mỹ tục:** Bộ lọc nhạy cảm tự động phát hiện, gắn cờ cảnh báo trong hàng đợi kiểm duyệt trước khi hiển thị công khai.
4. **File 3D (.glb) dung lượng lớn hoặc chứa mã độc hại:** Tải lên phải được validate định dạng nhị phân GLTF, kiểm tra giới hạn dung lượng, chạy nén ngầm DRACO và hiển thị thanh tiến trình trực quan.
5. **Tranh chấp bản quyền (DMCA notice):** Đơn tố cáo có tài liệu chứng minh phải cho phép Moderator ẩn tác phẩm tạm thời chỉ bằng 1-click kèm email thông báo tự động cho tác giả giải trình trong 72 giờ.

---

## 📌 Danh Mục Kế Hoạch Triển Khai Chi Tiết (Basic Design Subsystems)

Hệ thống được chia thành 4 phân hệ triển khai độc lập theo quy chuẩn module:

1. 📂 **[Kế Hoạch 1: Cơ Sở Dữ Liệu Nền Tảng & Phân Hệ Pháp Lý, e-KYC Tác Giả](file:///d:/My%20Project/Github/icegozi_my_novel/docs/superpowers/plans/2026-10-21-plan-1-database-and-legal-foundation.md)** (Migrations, Models, NĐ 147/2024, Cam kết bản quyền, Mã hóa CCCD AES-256, Audit Logs).
2. ✍️ **[Kế Hoạch 2: Hệ Thống Tác Giả & Trình Đăng Bài Siêu Mượt](file:///d:/My%20Project/Github/icegozi_my_novel/docs/superpowers/plans/2026-10-21-plan-2-author-studio-smooth-publishing.md)** (Zen Editor, Auto-save kép LocalStorage + Cloud Debounce, Offline Resilience, Bulk Chapter Splitter, Quản lý Asset 3D).
3. 🔮 **[Kế Hoạch 3: Trình Đọc Truyện & Động Cơ Chiếu 3D Cụ Hóa](file:///d:/My%20Project/Github/icegozi_my_novel/docs/superpowers/plans/2026-10-21-plan-3-interactive-3d-reader.md)** (Three.js WebGL Engine, Sân khấu 3D cuộn theo đoạn văn, Hạt ánh sáng, Fallback 2D, Lưu tiến độ đọc chính xác).
4. 🛡️ **[Kế Hoạch 4: Cổng Quản Trị Kiểm Duyệt & Khiếu Nại Bản Quyền DMCA](file:///d:/My%20Project/Github/icegozi_my_novel/docs/superpowers/plans/2026-10-21-plan-4-admin-moderation-and-dmca.md)** (Hàng đợi kiểm duyệt 1-click, Highlight từ nhạy cảm, Tạm ẩn tác phẩm 72h, Mail thông báo pháp lý, Tra cứu IP audit trail).

---

## 1. Thiết Kế Cấu Trúc Database Chi Tiết (Entity-Relationship & Migrations)

### 1.1 Bảng Tài khoản & Định danh Pháp lý (`users`, `author_profiles`, `legal_consents`)

```
users
├── id (BIGINT, PK, Auto Increment)
├── name (VARCHAR 100) - Tên hiển thị
├── email (VARCHAR 191, UNIQUE)
├── phone (VARCHAR 20, NULL) - Dùng xác thực SĐT theo NĐ 147/2024
├── phone_verified_at (TIMESTAMP, NULL)
├── password (VARCHAR 255)
├── role (ENUM: 'reader', 'author', 'moderator', 'admin') DEFAULT 'reader'
├── status (ENUM: 'active', 'suspended', 'banned') DEFAULT 'active'
├── avatar_url (VARCHAR 255, NULL)
├── remember_token (VARCHAR 100, NULL)
├── created_at & updated_at (TIMESTAMP)

author_profiles (Thông tin pháp lý tác giả để xuất bản)
├── id (BIGINT, PK, Auto Increment)
├── user_id (BIGINT, FK -> users.id, UNIQUE, CASCADE)
├── pen_name (VARCHAR 100) - Bút danh hiển thị
├── legal_full_name (VARCHAR 150) - Họ tên thật trên CCCD/Hộ chiếu
├── identity_number_encrypted (TEXT) - Số CCCD/Mã số định danh (mã hóa AES-256)
├── identity_hash (VARCHAR 64, UNIQUE) - HMAC-SHA256 với secret pepper để tra cứu unique
├── identity_issued_date (DATE)
├── identity_front_image (VARCHAR 255, NULL) - Lưu Private Disk, tự động xóa sau khi duyệt e-KYC
├── identity_back_image (VARCHAR 255, NULL) - Lưu Private Disk, tự động xóa sau khi duyệt e-KYC
├── tax_code (VARCHAR 30, NULL) - Mã số thuế cá nhân (nếu nhận nhuận bút)
├── bank_account_number (VARCHAR 50, NULL)
├── bank_name (VARCHAR 100, NULL)
├── verification_status (ENUM: 'pending', 'verified', 'rejected') DEFAULT 'pending'
├── rejection_reason (TEXT, NULL)
├── verified_at (TIMESTAMP, NULL)
├── created_at & updated_at (TIMESTAMP)

legal_consents (Lưu vết ký cam kết bản quyền & điều khoản sử dụng)
├── id (BIGINT, PK, Auto Increment)
├── user_id (BIGINT, FK -> users.id)
├── policy_type (ENUM: 'terms_of_service', 'author_copyright_agreement', 'privacy_policy')
├── policy_version (VARCHAR 20) - VD: 'v1.0-2026'
├── ip_address (VARCHAR 45) - IP khi ấn đồng ý
├── user_agent (TEXT) - Thiết bị & trình duyệt ký
├── signed_at (TIMESTAMP)
```

### 1.2 Bảng Truyện & Thể loại (`novels`, `genres`, `novel_genre`)

```
genres
├── id (BIGINT, PK, Auto Increment)
├── name (VARCHAR 100) - Tiên hiệp, Kiếm hiệp, Khoa huyễn, Huyền bí, Đô thị, Trinh thám
├── slug (VARCHAR 120, UNIQUE)
├── description (TEXT, NULL)
├── created_at & updated_at (TIMESTAMP)

novels
├── id (BIGINT, PK, Auto Increment)
├── author_id (BIGINT, FK -> users.id)
├── title (VARCHAR 255) - Tên truyện
├── slug (VARCHAR 255, UNIQUE)
├── synopsis (TEXT) - Tóm tắt truyện
├── cover_image (VARCHAR 255) - Đường dẫn ảnh bìa (WebP)
├── status (ENUM: 'draft', 'pending_approval', 'published', 'rejected', 'paused', 'completed', 'takedown') DEFAULT 'draft'
├── age_rating (ENUM: 'all', '13_plus', '16_plus', '18_plus') DEFAULT 'all'
├── copyright_type (ENUM: 'original', 'authorized_translation', 'public_domain') DEFAULT 'original'
├── copyright_license_notes (TEXT, NULL) - Giấy phép bản quyền, nguồn tác phẩm gốc
├── copyright_certificate_file (VARCHAR 255, NULL) - Hợp đồng/giấy ủy quyền đính kèm
├── content_warnings (JSON, NULL) - ['violence', 'blood', 'gambling', 'sensitive_elements']
├── total_words (BIGINT) DEFAULT 0
├── total_chapters (INT) DEFAULT 0
├── view_count (BIGINT) DEFAULT 0
├── bookmark_count (INT) DEFAULT 0
├── rating_avg (DECIMAL 3,2) DEFAULT 0.00
├── rejection_notes (TEXT, NULL) - Lý do từ chối nếu bị reject
├── approved_at (TIMESTAMP, NULL)
├── approved_by (BIGINT, FK -> users.id, NULL)
├── created_at & updated_at (TIMESTAMP)

novel_genre (Bảng trung gian pivot)
├── novel_id (BIGINT, FK -> novels.id, CASCADE)
├── genre_id (BIGINT, FK -> genres.id, CASCADE)
└── PRIMARY KEY (novel_id, genre_id)
```

### 1.3 Bảng Chương Truyện & Lịch Trình Đăng (`chapters`)

```
chapters
├── id (BIGINT, PK, Auto Increment)
├── novel_id (BIGINT, FK -> novels.id, CASCADE)
├── chapter_number (DECIMAL 8,2) - Số chương (hỗ trợ chương ngoại truyện, VD: 101.5)
├── title (VARCHAR 255) - Tiêu đề chương
├── slug (VARCHAR 255)
├── content (LONGTEXT) - Nội dung chương truyện (đã làm sạch HTML/Markdown)
├── word_count (INT) DEFAULT 0
├── status (ENUM: 'draft', 'pending_approval', 'published', 'scheduled', 'rejected') DEFAULT 'draft'
├── scheduled_at (TIMESTAMP, NULL) - Hẹn giờ tự động mở chương
├── published_at (TIMESTAMP, NULL)
├── view_count (BIGINT) DEFAULT 0
├── created_at & updated_at (TIMESTAMP)
└── UNIQUE INDEX (novel_id, chapter_number)
```

### 1.4 Bảng Mô Hình & Bối Cảnh Chiếu 3D Cụ Hóa (`novel_3d_assets`, `chapter_3d_projections`)

```
novel_3d_assets (Kho mô hình 3D của toàn bộ tác phẩm: Nhân vật, Thần binh, Bối cảnh)
├── id (BIGINT, PK, Auto Increment)
├── novel_id (BIGINT, FK -> novels.id, CASCADE)
├── name (VARCHAR 150) - VD: 'Hiên Viên Kiếm', 'Thần thú Cửu Vĩ Hồ', 'Cổ Thành Lâm An'
├── asset_type (ENUM: 'character', 'weapon', 'beast', 'artifact', 'environment')
├── model_file_url (VARCHAR 255) - File .glb/.gltf đã qua nén Draco
├── thumbnail_url (VARCHAR 255, NULL) - Ảnh chụp xoay 2D thumbnail
├── scale_factor (DECIMAL 4,2) DEFAULT 1.00
├── camera_init_position (JSON, NULL) - {"x": 0, "y": 2, "z": 5}
├── ambient_light_color (VARCHAR 10) DEFAULT '#ffffff'
├── particle_effect (ENUM: 'none', 'fireflies', 'snow', 'rain', 'divine_sparks', 'sakura') DEFAULT 'none'
├── sound_ambient_url (VARCHAR 255, NULL) - Tiếng nền gươm đao, suối chảy, sấm chớp
├── description (TEXT, NULL) - Bảng chỉ số, lai lịch cổ vật
├── created_at & updated_at (TIMESTAMP)

chapter_3d_projections (Gắn mô hình 3D vào đoạn văn bản cụ thể trong chương)
├── id (BIGINT, PK, Auto Increment)
├── chapter_id (BIGINT, FK -> chapters.id, CASCADE)
├── asset_3d_id (BIGINT, FK -> novel_3d_assets.id, CASCADE)
├── paragraph_index (INT) - Kích hoạt khi độc giả cuộn đến đoạn văn thứ N
├── trigger_keyword (VARCHAR 100, NULL) - Từ khóa tương tác bấm vào để bật hình chiếu 3D
├── hologram_title (VARCHAR 150) - Tiêu đề bảng chiếu nổi 3D
├── auto_rotate (BOOLEAN) DEFAULT TRUE
├── display_mode (ENUM: 'inline_box', 'split_stage', 'hologram_overlay', 'background_ambience') DEFAULT 'split_stage'
├── created_at & updated_at (TIMESTAMP)
```

### 1.5 Bảng Pháp Lý, Báo Cáo Vi Phạm DMCA & Giám Sát (`reports`, `audit_logs`, `content_filters`)

```
reports (Báo cáo vi phạm pháp lý & bản quyền DMCA)
├── id (BIGINT, PK, Auto Increment)
├── reporter_id (BIGINT, FK -> users.id, NULL) - Cho phép báo cáo ẩn danh hoặc có tài khoản
├── reporter_email (VARCHAR 191)
├── reportable_type (VARCHAR 50) - 'novel', 'chapter', 'comment'
├── reportable_id (BIGINT)
├── violation_category (ENUM: 'copyright_infringement', 'violates_national_security', 'pornography', 'violence_hate_speech', 'defamation')
├── evidence_urls (JSON, NULL) - Tài liệu pháp lý chứng minh bản quyền
├── description (TEXT) - Mô tả chi tiết hành vi vi phạm
├── status (ENUM: 'received', 'under_review', 'temporary_takedown', 'dismissed', 'resolved') DEFAULT 'received'
├── resolution_notes (TEXT, NULL)
├── resolved_by (BIGINT, FK -> users.id, NULL)
├── created_at & updated_at (TIMESTAMP)

audit_logs (Lưu vết pháp lý các thao tác quản trị & đăng tải theo quy định an ninh mạng)
├── id (BIGINT, PK, Auto Increment)
├── user_id (BIGINT, FK -> users.id, NULL)
├── action (VARCHAR 100) - 'create_novel', 'publish_chapter', 'approve_novel', 'takedown_novel', 'ban_author'
├── target_type (VARCHAR 50)
├── target_id (BIGINT)
├── old_values (JSON, NULL)
├── new_values (JSON, NULL)
├── ip_address (VARCHAR 45)
├── user_agent (TEXT)
├── created_at (TIMESTAMP)

content_filters (Từ điển từ khóa cấm/nhạy cảm)
├── id (BIGINT, PK, Auto Increment)
├── pattern (VARCHAR 255) - Regex hoặc từ khóa cấm
├── category (ENUM: 'politics', 'vulgarity', 'hate_speech', 'fraud')
├── severity (ENUM: 'flag_for_review', 'auto_block') DEFAULT 'flag_for_review'
├── created_at & updated_at (TIMESTAMP)
```

### 1.6 Bảng Đọc Truyện & Tương Tác (`reading_history`, `bookmarks`, `chapter_comments`)

```
reading_history (Lưu mốc chương đọc gần nhất của cả truyện)
├── id (BIGINT, PK, Auto Increment)
├── user_id (BIGINT, FK -> users.id, CASCADE)
├── novel_id (BIGINT, FK -> novels.id, CASCADE)
├── last_chapter_id (BIGINT, FK -> chapters.id, CASCADE)
├── updated_at (TIMESTAMP)
└── UNIQUE INDEX (user_id, novel_id)

chapter_reading_progress (Lưu chính xác vị trí đoạn văn của từng chương cụ thể)
├── id (BIGINT, PK, Auto Increment)
├── user_id (BIGINT, FK -> users.id, CASCADE)
├── chapter_id (BIGINT, FK -> chapters.id, CASCADE)
├── last_read_paragraph (INT) DEFAULT 0
├── scroll_percentage (DECIMAL 5,2) DEFAULT 0.00
├── updated_at (TIMESTAMP)
└── UNIQUE INDEX (user_id, chapter_id)

bookmarks
├── id (BIGINT, PK, Auto Increment)
├── user_id (BIGINT, FK -> users.id, CASCADE)
├── novel_id (BIGINT, FK -> novels.id, CASCADE)
├── created_at (TIMESTAMP)
└── UNIQUE INDEX (user_id, novel_id)

chapter_comments
├── id (BIGINT, PK, Auto Increment)
├── chapter_id (BIGINT, FK -> chapters.id, CASCADE)
├── user_id (BIGINT, FK -> users.id, CASCADE)
├── paragraph_index (INT, NULL) - Bình luận ngay tại đoạn văn
├── content (TEXT)
├── is_spoiler (BOOLEAN) DEFAULT FALSE
├── is_flagged (BOOLEAN) DEFAULT FALSE
├── created_at & updated_at (TIMESTAMP)
```

---

## 2. Thiết Kế Màn Hình Trải Nghiệm (Wireframes & Screen Interaction Flows)

### 2.1 Nhóm Màn Hình Độc Giả (Reader Experience)

```
+-----------------------------------------------------------------------------------------+
| [LOGO NOVEL3D]  Tìm kiếm truyện, tác giả... [Search]  | Thể loại | Bảng xếp hạng | [User]|
+-----------------------------------------------------------------------------------------+
| [ HERO BANNER 3D: TÁC PHẨM NỔI BẬT ]                                                    |
| +---------------------------------------------------+---------------------------------+ |
| | TIÊN NGHỊCH: VẠN CỔ CHI VƯƠNG                     | [ 3D INTERACTIVE HERO STAGE ]   | |
| | Nhãn: [16+] Thể loại: [Tiên hiệp, Huyền ảo]       | Xoay 360 độ Thần Binh Tru Tiên  | |
| | Đã đăng: 1,450 chương | Lượt đọc: 2.8M             | [Xoay] [Phát sáng] [Hiệu ứng hồ] | |
| | [ĐỌC NGAY]  [THƯ VIỆN BẢO VẬT 3D]  [LƯU TRUYỆN]   |                                 | |
| +---------------------------------------------------+---------------------------------+ |
|                                                                                         |
| == DANH SÁCH TRUYỆN MỚI CẬP NHẬT ==                   == BẢNG XẾP HẠNG THỊNH HÀNH ==    |
| [Bìa] Trọng Sinh Tu Tiên [16+] - Chương 52            1. Vũ Động Càn Khôn (3D Ready)    |
| [Bìa] Toàn Cầu Cao Võ [All] - Chương 120              2. Kiếm Đạo Đệ Nhất Tiên          |
| [Bìa] Quỷ Bí Chi Chủ [18+] (Cảnh báo nhạy cảm)        3. Mục Thần Ký (Đang phát sóng)   |
|                                                                                         |
| [Footer Pháp lý: Giấy phép xuất bản số..., Tuân thủ NĐ 147/2024/NĐ-CP, Quy chế DMCA]   |
+-----------------------------------------------------------------------------------------+
```

#### Màn hình Đọc Truyện 3D Cụ Hóa (Interactive 3D Projection Reader)
Giao diện có 2 chế độ linh hoạt chuyển đổi tức thì:
1. **Chế độ Thần Chiếu (3D Stage Mode - Chia đôi hoặc Hologram ghim góc):**
   - Bên trái là cột văn bản đọc mượt, font chữ serif/sans tuỳ biến, khoảng cách dòng thoáng mắt.
   - Bên phải là **Sân khấu Chiếu 3D (3D Hologram Projection Stage)**: Khi người đọc cuộn tới đoạn tác giả miêu tả vũ khí/linh thú/nhân vật, mô hình 3D tương ứng lập tức hiển thị, tự xoay nhẹ với hiệu ứng ánh sáng phát quang huyền ảo (Bloom/Glow), hạt tuyết/lửa/đom đóm bay lơ lửng.
   - Độc giả có thể dùng chuột hoặc cử chỉ cảm ứng xoay 360 độ, zoom cận cảnh chi tiết hoa văn trên thân kiếm hoặc trang phục nhân vật.
2. **Chế độ Tinh Gọn (Zen Pure Reader):**
   - Ẩn khung 3D để tập trung 100% vào văn bản chữ. Các từ khóa có liên kết 3D sẽ có icon tinh tế hình khối lập phương nhỏ `[3D]`, bấm vào sẽ bật một Modal Hologram 3D nổi lên không làm mất trang.
   - Tùy chỉnh: Màu nền (Giấy vàng chống mỏi mắt, Tối Dark Mode, Trắng ngọc), Kích cỡ chữ, Tự động cuộn trang.
   - Tự động lưu tiến độ đọc (Paragraph progress) chính xác đến từng đoạn văn.

---

### 2.2 Nhóm Màn Hình Tác Giả & Xuất Bản Siêu Mượt (Author Studio)

```
+-----------------------------------------------------------------------------------------+
| [STUDIO TÁC GIẢ]  Truyện của tôi  |  Kho mô hình 3D  |  Doanh thu / Bản quyền  | [Bút danh]|
+-----------------------------------------------------------------------------------------+
| THÔNG BÁO PHÁP LÝ: [V] Đã xác thực CCCD & Ký Cam Kết Bản Quyền Tác Giả Số NĐ147-2024    |
|                                                                                         |
| [TRÌNH SOẠN THẢO SIÊU MƯỢT - ZEN AUTHOR EDITOR]                                          |
| Tiêu đề chương: [Chương 128: Nhất Kiếm Đoạt Mệnh____________________________________]   |
| Số từ: 3,420 từ | Ước tính đọc: 9 phút | Trạng thái: [Đã lưu tự động lúc 23:15 (Cloud)]  |
|                                                                                         |
| [ B  I  U  H2  H3  Trích dẫn  |  + Gắn Mô Hình 3D Chiếu Cụ Hóa  |  Zen Mode Toàn Màn Hình ] |
| --------------------------------------------------------------------------------------- |
|  Nhiếp Ly nhẹ nhàng vung tay, một đạo thanh quang lóe lên giữa không trung.             |
|  Đó chính là Cổ Kiếm Thanh Trúc, thân kiếm trong suốt như ngọc bích, khắc đầy cổ phù... |
|  >>> [ĐÃ GẮN CHIẾU 3D: Cổ Kiếm Thanh Trúc (Hiệu ứng: Hạt Lôi Quang) - Xem Thử] <<<      |
|                                                                                         |
|  Khí tức bá đạo từ lưỡi kiếm tản ra khiến cho hư không như muốn rạn nứt...              |
|                                                                                         |
| --------------------------------------------------------------------------------------- |
| Cài đặt xuất bản:                                                                       |
| (*) Đăng ngay     ( ) Hẹn giờ đăng: [2026-10-22 08:00]     ( ) Lưu nháp nội bộ          |
| [X] Tôi cam kết nội dung này do tôi sáng tác, không vi phạm pháp luật & thuần phong mỹ  |
| [ XUẤT BẢN CHƯƠNG MỚI (Ctrl+Enter) ]             [ XEM TRƯỚC TRÊN MOBILE / 3D ]         |
+-----------------------------------------------------------------------------------------+
```

#### Các Tính Năng Đột Phá Đảm Bảo Độ "Smooth" Tuyệt Đối Cho Tác Giả:
- **Zero Latency Typing & Dual Storage:** Toàn bộ thao tác gõ được lưu trực tiếp vào LocalStorage/IndexedDB không trễ 1ms. Cơ chế Debounce 3000ms gửi gói nén nhẹ lên máy chủ Laravel.
- **Offline Resilience:** Nếu mất kết nối Internet, badge trên góc màn hình chuyển sang màu vàng `[Đang lưu Offline]`. Tác giả vẫn tiếp tục viết bình thường. Khi có mạng trở lại, hệ thống tự động đồng bộ ngầm mà không gây giật lag.
- **Split Chapter Utility (Tách chương hàng loạt siêu tốc):** Tác giả có thể dán một file text 50 chương, hệ thống tự động phân tách theo quy tắc regex `Chương \d+:` và tạo các bản nháp tuần tự chỉ trong 1 giây.
- **Quản lý Asset 3D Kéo Thả (Drag & Drop 3D Uploader):** Kéo thả file `.glb` trực tiếp vào khung soạn thảo, trình đọc Three.js tự động sinh thumbnail 3D ngay trên giao diện client để tác giả xoay thử góc nhìn camera trước khi lưu.

---

### 2.3 Nhóm Màn Hình Quản Trị & Pháp Lý (Admin & Moderation Dashboard)

```
+-----------------------------------------------------------------------------------------+
| [ADMIN NOVEL PORTAL]   Hàng đợi duyệt (12) | Khiếu nại DMCA (2) | Tác giả chờ KYC (5)    |
+-----------------------------------------------------------------------------------------+
| BỘ LỌC KIỂM DUYỆT NHANH: [Tất cả] [Chứa từ khóa cảnh báo] [Nhãn 18+] [Truyện dịch]      |
|                                                                                         |
| 1. "Đại Chúa Tể Ma Đạo" - Tác giả: Hắc Dạ (CCCD: Đã xác thực)                           |
|    - Cảnh báo hệ thống: Phát hiện 2 từ thuộc danh mục nhạy cảm tại Chương 15            |
|    - Giấy tờ tác quyền: [Hợp đồng bản quyền số 2026/VN-NOVEL.pdf] (Đã đính kèm)        |
|    - Chiếu 3D đính kèm: 3 mô hình (.glb) - Đã qua kiểm tra virus & dung lượng (2.4MB)   |
|    - Thao tác 1-Click: [ DUYỆT XUẤT BẢN ]  [ TỪ CHỐI (Chọn lý do) ]  [ YÊU CẦU SỬA ]   |
|                                                                                         |
| 2. TRUNG TÂM TIẾP NHẬN TRANH CHẤP BẢN QUYỀN (DMCA TAKEDOWN)                            |
|    - Đơn khiếu nại #DMCA-902: Truyện "Kiếm Vương Khắc Cốt" bị tố sao chép nguyên tác     |
|    - Bên khiếu nại: NXB Văn Học Trẻ | Đính kèm: [Giấy chứng nhận đăng ký quyền tác giả] |
|    - Hành động khẩn cấp: [ TẠM ẨN TÁC PHẨM CHỜ GIẢI TRÌNH (72h) ]  [ BÁC ĐƠN KHIẾU NẠI ]|
|    - Tự động ghi nhật ký Audit Log & lưu trữ IP phục vụ thanh tra pháp lý                |
+-----------------------------------------------------------------------------------------+
```

---

## 3. Danh Sách Nhiệm Vụ Triển Khai (Bite-Sized Implementation Tasks)

### Task 1: Thiết lập Migration Cơ sở Dữ liệu & Hệ thống Phân quyền (Legal RBAC & Schema)
**Files:**
- Create: `database/migrations/2026_10_21_000001_create_legal_and_author_profiles_tables.php`
- Create: `database/migrations/2026_10_21_000002_create_novels_and_genres_tables.php`
- Create: `database/migrations/2026_10_21_000003_create_chapters_and_reading_history_tables.php`
- Create: `database/migrations/2026_10_21_000004_create_3d_assets_and_projections_tables.php`
- Create: `database/migrations/2026_10_21_000005_create_reports_and_audit_logs_tables.php`
- Create: `app/Models/AuthorProfile.php`
- Create: `app/Models/LegalConsent.php`
- Create: `app/Models/Novel.php`
- Create: `app/Models/Genre.php`
- Create: `app/Models/Chapter.php`
- Create: `app/Models/Novel3dAsset.php`
- Create: `app/Models/Chapter3dProjection.php`
- Create: `app/Models/Report.php`
- Create: `app/Models/AuditLog.php`
- Test: `tests/Feature/DatabaseSchemaTest.php`

**Interfaces:**
- Consumes: Laravel Database Schema Builder.
- Produces: Toàn bộ bảng dữ liệu đầy đủ ràng buộc khóa ngoại, index tối ưu cho tra cứu và trường pháp lý.

- [ ] **Step 1: Viết test kiểm tra migration các bảng trọng yếu và quan hệ Model**
- [ ] **Step 2: Chạy test để xác nhận test thất bại (chưa có migration & model)**
- [ ] **Step 3: Tạo các migration files và khai báo trường dữ liệu chuẩn xác**
- [ ] **Step 4: Chạy `php artisan migrate` và chạy test xác nhận pass 100%**
- [ ] **Step 5: Commit mã nguồn Task 1**

---

### Task 2: Module Xác thực Tác giả & Cam kết Pháp lý Số (e-KYC & Copyright Consent)
**Files:**
- Create: `app/Http/Controllers/AuthorVerificationController.php`
- Create: `app/Http/Requests/SubmitAuthorVerificationRequest.php`
- Create: `app/Services/AuditLogService.php`
- Create: `resources/views/author/register_verification.blade.php`
- Create: `resources/views/legal/terms_and_copyright_policy.blade.php`
- Test: `tests/Feature/AuthorVerificationTest.php`

**Interfaces:**
- Consumes: `users`, `author_profiles`, `legal_consents`.
- Produces: API/Route nộp hồ sơ định danh tác giả, mã hoá CCCD, lưu vết IP ký cam kết bản quyền, phân quyền tác giả sau khi duyệt.

- [ ] **Step 1: Viết test quy trình nộp định danh và ký cam kết bản quyền (bắt buộc theo NĐ 147)**
- [ ] **Step 2: Chạy test xác nhận fail**
- [ ] **Step 3: Triển khai Controller, Request validation, mã hoá số định danh và Service AuditLog**
- [ ] **Step 4: Chạy test xác nhận pass**
- [ ] **Step 5: Commit mã nguồn Task 2**

---

### Task 3: Trình Đăng Bài & Soạn Thảo Siêu Mượt (Smooth Zen Author Studio)
**Files:**
- Create: `app/Http/Controllers/Author/ChapterEditorController.php`
- Create: `app/Http/Controllers/Author/NovelManagementController.php`
- Create: `resources/views/author/editor.blade.php`
- Create: `resources/js/author-editor.js`
- Create: `app/Jobs/ScanSensitiveContentJob.php`
- Test: `tests/Feature/ChapterEditorApiTest.php`

**Interfaces:**
- Consumes: `novels`, `chapters`, LocalStorage/IndexedDB API.
- Produces: Trình soạn thảo chống mất dữ liệu, Auto-save debounce 3s, phím tắt xuất bản, tự động quét từ khóa nhạy cảm qua Queue.

- [ ] **Step 1: Viết test API lưu nháp tự động (`/api/author/chapters/autosave`) và xuất bản chương**
- [ ] **Step 2: Chạy test xác nhận fail**
- [ ] **Step 3: Viết logic Controller, Job quét từ cấm, xây dựng giao diện Blade + JS Auto-save siêu mượt**
- [ ] **Step 4: Chạy test xác nhận pass**
- [ ] **Step 5: Commit mã nguồn Task 3**

---

### Task 4: Trình Quản Lý & Tải Lên Asset 3D (3D Model Uploader & Validation)
**Files:**
- Create: `app/Http/Controllers/Author/Novel3dAssetController.php`
- Create: `app/Http/Requests/Upload3dAssetRequest.php`
- Create: `resources/views/author/3d_assets_manager.blade.php`
- Create: `resources/js/3d-preview.js`
- Test: `tests/Feature/Upload3dAssetTest.php`

**Interfaces:**
- Consumes: File upload `.glb`/`.gltf`, Storage facade.
- Produces: Endpoint upload an toàn (kiểm tra MIME type, dung lượng tối đa 15MB, cấu hình camera, màu ánh sáng).

- [ ] **Step 1: Viết test upload file 3D hợp lệ và chặn file sai định dạng hoặc độc hại**
- [ ] **Step 2: Chạy test xác nhận fail**
- [ ] **Step 3: Triển khai Controller xử lý lưu trữ asset 3D, tích hợp viewer Three.js xem trước**
- [ ] **Step 4: Chạy test xác nhận pass**
- [ ] **Step 5: Commit mã nguồn Task 4**

---

### Task 5: Trình Đọc Truyện Chiếu 3D Cụ Hóa (Interactive 3D Projection Reader Engine)
**Files:**
- Create: `app/Http/Controllers/Reader/NovelReaderController.php`
- Create: `resources/views/reader/chapter.blade.php`
- Create: `resources/js/reader-3d-engine.js`
- Create: `resources/css/reader-theme.css`
- Test: `tests/Feature/NovelReaderTest.php`

**Interfaces:**
- Consumes: `chapters.content`, `chapter_3d_projections`, Three.js (WebGL).
- Produces: Trải nghiệm đọc cuộn mượt mà với sàn diễn 3D tự động phản ứng theo đoạn văn bản, hiệu ứng hạt ánh sáng, hỗ trợ fallback 2D cho máy yếu.

- [ ] **Step 1: Viết test route hiển thị nội dung chương và danh sách 3D projections tương ứng**
- [ ] **Step 2: Chạy test xác nhận fail**
- [ ] **Step 3: Cài đặt Three.js, xây dựng Engine `reader-3d-engine.js` với tính năng load GLB, particle effect, scroll trigger**
- [ ] **Step 4: Chạy test xác nhận pass**
- [ ] **Step 5: Commit mã nguồn Task 5**

---

### Task 6: Cổng Quản Trị Kiểm Duyệt & Tiếp Nhận Khiếu Nại Bản Quyền (DMCA & Moderation)
**Files:**
- Create: `app/Http/Controllers/Admin/ModerationController.php`
- Create: `app/Http/Controllers/Admin/DmcaReportController.php`
- Create: `resources/views/admin/moderation/queue.blade.php`
- Create: `resources/views/admin/dmca/index.blade.php`
- Create: `app/Mail/NovelTakedownNotification.php`
- Test: `tests/Feature/AdminModerationTest.php`

**Interfaces:**
- Consumes: `reports`, `novels`, `audit_logs`.
- Produces: Dashboard kiểm duyệt 1-click duyệt/từ chối, quy trình tạm ẩn truyện vi phạm bản quyền và gửi email thông báo giải trình pháp lý.

- [ ] **Step 1: Viết test chức năng duyệt truyện, từ chối kèm lý do và xử lý đơn DMCA takedown**
- [ ] **Step 2: Chạy test xác nhận fail**
- [ ] **Step 3: Triển khai Controller kiểm duyệt, view blade dashboard quản trị, mail thông báo**
- [ ] **Step 4: Chạy test xác nhận pass**
- [ ] **Step 5: Commit mã nguồn Task 6**

---

## 4. Tự Đánh Giá (Self-Review Checklist)

1. **Độ phủ yêu cầu (Spec Coverage):**
   - Đầy đủ yếu tố pháp lý: Định danh tác giả NĐ 147/2024/NĐ-CP, cam kết bản quyền, phân loại độ tuổi 18+, quy trình xử lý khiếu nại DMCA, audit logs.
   - Ứng dụng hình chiếu 3D cụ hóa: Engine Three.js liên kết đoạn văn bản với mô hình 3D xoay 360 độ, hiệu ứng hạt, fallback WebGL an toàn.
   - Trải nghiệm đăng bài siêu mượt (Smooth UX): Auto-save debounce, offline storage, background queues xử lý file nặng.
2. **Tính nhất quán của Interface & Kiểu dữ liệu:** Các tên bảng, trường dữ liệu, model relation giữa các task ăn khớp chặt chẽ (`novel_3d_assets` -> `chapter_3d_projections`, `author_profiles` -> `users`).
3. **Review Focus Handled:** Đã đưa ra giải pháp cụ thể cho rớt mạng, máy yếu không có WebGL, quét từ cấm, file 3D độc hại và quy trình pháp lý DMCA.
