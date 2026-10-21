# Kế Hoạch 3: Trình Đọc Truyện & Động Cơ Chiếu 3D Cụ Hóa (Interactive 3D Reader Engine)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Triển khai trải nghiệm đọc truyện nhập vai với Động cơ Chiếu 3D Cụ Hóa (Interactive 3D Projection Reader Engine) bằng Three.js; tự động kích hoạt xoay mô hình 3D và hiệu ứng hạt tương tác khi độc giả cuộn đến đoạn văn bản miêu tả vũ khí/nhân vật; cách ly cảm ứng tránh xung đột vuốt cuộn trên mobile; hỗ trợ chế độ Zen Reader tinh gọn, tùy chỉnh màu giấy đọc sách, cơ chế hạ cấp 2D mượt mà trên thiết bị yếu và lưu tiến độ đọc chính xác cho từng chương cụ thể.

**Architecture:** Frontend Three.js (WebGL) module hóa cao, tích hợp Intersection Observer để bắt sự kiện cuộn trang mà không tốn CPU; Canvas 3D mặc định đặt `pointer-events-none` trên di động để không chiếm quyền vuốt cuộn văn bản; hệ thống hạt tự tạo bằng Shader/Particle Point Cloud; Backend Laravel 11 phục vụ nội dung chương kèm metadata 3D projections và API đồng bộ tiến độ đọc theo từng chương (`chapter_reading_progress`).

**Tech Stack:** PHP 8.2+, Laravel 11.x, Three.js (r160+), DRACOLoader, OrbitControls, TailwindCSS v3.4, Alpine.js v3.

**Spec:** [docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md](docs/superpowers/plans/2026-10-21-novel-platform-with-3d-reader.md)

## Global Constraints

- **Hiệu năng 3D tối thiểu 45 - 60 FPS & Ngân sách tài nguyên:** Mỗi mô hình tối đa 20,000 polygons, texture tối đa 1024x1024; nén Draco; ngắt render loop (`cancelAnimationFrame`) khi người đọc không tương tác hoặc chuyển tab nhằm tiết kiệm pin thiết bị di động.
- **Cách ly thao tác cảm ứng di động (Touch Gesture Isolation):** Canvas 3D mặc định có CSS `pointer-events: none` để ngón tay vuốt cuộn đọc chữ không bị OrbitControls bắt giữ làm xoay mô hình. Chỉ bật `pointer-events: auto` khi người dùng chạm nút `[Tương tác 360°]`.
- **Tự động bắt sự kiện rớt WebGL (`webglcontextlost`):** Trình đọc không được phép văng trắng màn hình; lập tức chuyển sang chế độ ảnh 2D kèm thông báo nhẹ nhàng cho người đọc.
- **Tiến độ đọc độc lập từng chương:** Lưu vào bảng `chapter_reading_progress` (unique `[user_id, chapter_id]`), đọc nhiều chương khác nhau không bao giờ bị đè mất vị trí.

## Review Focus

1. **Người dùng lướt đọc trên điện thoại ở chế độ Thần Chiếu:** Ngón tay vuốt vào vùng 3D trang vẫn cuộn đọc văn bản bình thường; nút toggle tương tác 360 độ hoạt động mượt mà.
2. **Người dùng mở truyện trên điện thoại cấu hình thấp không có GPU rời:** Three.js engine phát hiện khả năng xử lý của thiết bị qua `gl.getParameter(gl.RENDERER)`, tự động tắt hiệu ứng hạt nặng và giảm độ phân giải canvas.
3. **Cuộn trang nhanh (Fast Scrolling) qua nhiều đoạn văn có gắn 3D:** Intersection Observer áp dụng Debounce để không kích hoạt tải/huỷ mô hình 3D liên tục làm giật khung hình.
4. **Mô hình 3D bị lệch tâm hoặc quá to/nhỏ trong khung hình:** Hệ thống tự động tính toán BoundingBox và căn chỉnh camera vị trí `(0, 0, maxDim * 2.5)` để mô hình luôn hiển thị trọn vẹn, đẹp mắt.
5. **Độc giả mất mạng khi đang đọc dở chương:** Lưu cache chương hiện tại và vị trí đọc vào IndexedDB/LocalStorage để người dùng đọc tiếp mà không bị gián đoạn.

---

## Task 1: Trang Chi Tiết Tác Phẩm & Thư Viện Bảo Vật 3D (Novel Detail & 3D Exhibition)
**Files:**
- Create: `app/Http/Controllers/Reader/NovelViewController.php`
- Create: `resources/views/reader/novels/show.blade.php`
- Create: `resources/views/reader/novels/3d_exhibition.blade.php`
- Test: `tests/Feature/NovelViewDetailTest.php`

**Interfaces:**
- Consumes: `novels`, `novel_3d_assets`, `chapters`.
- Produces: Trang chi tiết tác phẩm hiển thị tóm tắt, mục lục chương, giấy phép tác quyền, nhãn độ tuổi và phòng trưng bày 3D các bảo vật/nhân vật trong truyện.

- [ ] **Step 1: Viết test hiển thị trang chi tiết truyện, kiểm tra nhãn độ tuổi và danh sách mô hình 3D đính kèm**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=NovelViewDetailTest`
- [ ] **Step 3: Triển khai Controller, View Blade và hiển thị thư viện mô hình 3D**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=NovelViewDetailTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(reader): implement novel details and 3d exhibition stage"`

---

## Task 2: Động Cơ Chiếu 3D Three.js Module Hóa (Reader 3D Engine Core)
**Files:**
- Create: `resources/js/3d-engine/projection-stage.js`
- Create: `resources/js/3d-engine/particle-system.js`
- Create: `resources/js/3d-engine/model-cache.js`
- Modify: `resources/js/app.js`
- Test: `tests/Unit/ThreeEngineSetupTest.php`

**Interfaces:**
- Consumes: Three.js WebGLRenderer, GLTFLoader, DRACOLoader.
- Produces: Class `ProjectionStage` với các phương thức: `loadModel(url, scale)`, `setLighting(color)`, `triggerEffect(particleType)`, `setInteractive(boolean)`, `destroy()`.

- [ ] **Step 1: Viết test kiểm tra file cấu trúc bundle của 3D engine và cấu hình DRACO decoder**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ThreeEngineSetupTest`
- [ ] **Step 3: Xây dựng `projection-stage.js`, hệ thống hạt `particle-system.js` và bộ nhớ đệm mô hình `model-cache.js`**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ThreeEngineSetupTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add resources/js/ tests/ && git commit -m "feat(3d-engine): implement core three.js projection stage and particle effects"`

---

## Task 3: Giao Diện Đọc Truyện Chế Độ Thần Chiếu (3D Stage Mode & Touch Gesture Isolation)
**Files:**
- Create: `app/Http/Controllers/Reader/ChapterReaderController.php`
- Create: `resources/views/reader/chapters/read_3d.blade.php`
- Create: `resources/js/scroll-3d-observer.js`
- Create: `resources/css/reader-stage.css`
- Test: `tests/Feature/ChapterReader3dModeTest.php`

**Interfaces:**
- Consumes: `chapters`, `chapter_3d_projections`, `ProjectionStage`.
- Produces: Màn hình đọc truyện chia cột (Split Screen) hoặc khung Hologram nổi; khi cuộn tới đoạn văn thứ N, mô hình 3D tương ứng tự động hiển thị; lớp phủ canvas có `pointer-events-none` và nút `[Chạm để tương tác 360°]`.

- [ ] **Step 1: Viết test route đọc chương truyện kèm danh sách các mốc chiếu 3D (projections JSON)**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ChapterReader3dModeTest`
- [ ] **Step 3: Triển khai View Blade và tích hợp `scroll-3d-observer.js` bắt sự kiện cuộn qua Intersection Observer**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ChapterReader3dModeTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add app/ resources/ tests/ && git commit -m "feat(reader): implement 3d projection stage reader mode with touch isolation"`

---

## Task 4: Chế Độ Đọc Tinh Gọn & Tùy Biến Giao Diện (Zen Reader & Preferences)
**Files:**
- Create: `resources/views/reader/chapters/partials/zen_toolbar.blade.php`
- Create: `resources/js/reader-preferences.js`
- Test: `tests/Feature/ReaderPreferencesTest.php`

**Interfaces:**
- Consumes: User preference (Font size, Theme: Sepia/Dark/Light, Line spacing).
- Produces: Thanh công cụ đọc truyện thông minh, chuyển đổi 1-click giữa Chế độ 3D và Chế độ Tinh Gọn; icon `[3D]` nhỏ nổi bật tại từ khóa để mở modal Hologram theo yêu cầu.

- [ ] **Step 1: Viết test cho endpoint lưu tùy chọn giao diện đọc truyện của người dùng**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ReaderPreferencesTest`
- [ ] **Step 3: Viết script `reader-preferences.js` lưu cài đặt vào LocalStorage và áp dụng CSS class động**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ReaderPreferencesTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add resources/ tests/ && git commit -m "feat(reader): implement zen reader toolbar and reading theme customization"`

---

## Task 5: Cơ Chế Hạ Cấp An Toàn 2D & Xử Lý Sự Cố WebGL (Graceful Fallback)
**Files:**
- Create: `resources/js/3d-engine/webgl-fallback.js`
- Modify: `resources/js/3d-engine/projection-stage.js`
- Test: `tests/Unit/WebGLFallbackTest.php`

**Interfaces:**
- Consumes: `webglcontextlost` event, browser capability detection.
- Produces: Tự động chuyển đổi mượt mà sang hình ảnh 2D tĩnh chất lượng cao khi WebGL không thể khởi tạo hoặc bị tràn bộ nhớ VRAM.

- [ ] **Step 1: Viết test mô phỏng trường hợp WebGL không khả dụng**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=WebGLFallbackTest`
- [ ] **Step 3: Triển khai bộ phát hiện WebGL và chuyển đổi fallback 2D**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=WebGLFallbackTest`
- [ ] **Step 5: Commit mã nguồn**
  `git add resources/js/ tests/ && git commit -m "feat(3d-engine): implement graceful 2d fallback for low-end devices"`

---

## Task 6: Tự Động Lưu & Khôi Phục Tiến Độ Từng Chương Độc Lập (Accurate Reading Progress)
**Files:**
- Create: `app/Http/Controllers/Reader/ReadingHistoryController.php`
- Create: `resources/js/reading-progress-tracker.js`
- Test: `tests/Feature/ReadingHistorySyncTest.php`

**Interfaces:**
- Consumes: `chapter_reading_progress`, `/api/reader/progress`.
- Produces: Bắt vị trí đoạn văn đang đọc, gửi cập nhật debounce 5s lên server lưu vào `chapter_reading_progress` (unique `[user_id, chapter_id]`), tự động cuộn mượt khi độc giả quay lại chương đó.

- [ ] **Step 1: Viết test API lưu và tải tiến độ đọc theo đoạn văn cho từng chương độc lập**
- [ ] **Step 2: Chạy test xác nhận fail**
  `php artisan test --filter=ReadingHistorySyncTest`
- [ ] **Step 3: Triển khai Controller, API và tracker Javascript cuộn trang thông minh**
- [ ] **Step 4: Chạy test xác nhận pass**
  `php artisan test --filter=ReadingHistorySyncTest`
- [ ] **Step 5: Commit mã nguồn hoàn thiện Kế hoạch 3**
  `git add app/ resources/ tests/ && git commit -m "feat(reader): implement independent chapter reading progress sync"`
