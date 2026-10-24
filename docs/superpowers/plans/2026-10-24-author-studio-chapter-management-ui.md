# Author Studio Chapter Management UI & Action Triggers Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Triển khai đầy đủ hệ thống nút bấm hành động (Action Triggers) và bảng quản lý chương truyện trực quan cho tác giả trên giao diện Studio (`/tac-gia/truyen` và `/tac-gia/truyen/{id}/edit`), đảm bảo tuân thủ tiêu chuẩn thiết kế UI/UX hiện đại, WCAG 2.1/2.2 AA và xác thực E2E bằng tương tác click chuột thật trên trình duyệt.

**Architecture:** Mở rộng giao diện Blade Views của Author Studio (`author.novels.index` và `author.novels.create_or_edit`) bằng cách kết nối trực quan các route backend hiện có (`author.chapters.create`, `author.novels.bulk-chapters.create`, `author.novels.3d-assets.index`, `author.chapters.edit`). Tối ưu hoá truy vấn Eager-Loading trong `NovelController::edit` để hiển thị danh sách chương kèm trạng thái, số chữ và phiên bản.

**Tech Stack:** Laravel 11.31 Blade, Tailwind CSS v3, Alpine.js v3, PHPUnit 11, Playwright MCP.

**Spec:** Điều tra chẩn đoán thiếu nút bấm và sự kiện trên màn hình theo yêu cầu người dùng, tuân thủ `ui-ux-pro-max`, `frontend-design`, `web-design-guidelines` và `AGENTS.md`.

## Global Constraints

- Hỗ trợ đầy đủ 3 theme: Sáng (Light), Tối (Dark), và Sepia mặc định (`sepia:bg-sepia-card`, `sepia:text-sepia-text`, `sepia:border-sepia-border`).
- Không sử dụng `transition-all` (chỉ dùng `transition-colors`, `transition-opacity`, `transition-transform`).
- Tất cả các nút bấm hoặc liên kết phải có nhãn dễ tiếp cận (Accessible Name) chứa văn bản hiển thị (WCAG 2.5.3 Label in Name).
- Các icon trang trí hoặc emoji phải có `aria-hidden="true"`.
- Kích thước vùng bấm tương tác tối thiểu 44×44px (hoặc min-h-[38px] cho button inline kèm padding rõ ràng, không có dead zone).
- Hiển thị focus rõ ràng qua `focus-visible:ring-2`.

## Review Focus

1. Thẻ truyện trong Studio (`/tac-gia/truyen`): Phải có nút chính nổi bật `+ Soạn Chương` và các nút phụ `Tách Chương`, `Kho 3D`, `Chỉnh Sửa`.
2. Khi tác phẩm chưa có chương (`chapters->count() === 0`): Trạng thái hiển thị rõ ràng `0 chương` kèm lời mời gọi hành động `+ Viết Chương Đầu Tiên`.
3. Trang chỉnh sửa tác phẩm (`/tac-gia/truyen/{id}/edit`): Tải và hiển thị danh mục chương với bảng phân trang hoặc danh sách chương rõ ràng (Số chương, Tiêu đề, Số chữ dạng tabular-nums, Trạng thái, Nút `Sửa bản thảo`).
4. Kiểm thử TDD (Red -> Green -> Refactor): Viết test tự động kiểm tra HTML markup và route assertions trước khi cập nhật code giao diện.
5. Kiểm thử trình duyệt Playwright: Mô phỏng hành động click chuột thật trên nút bấm UI để điều hướng, ghi nhận 0 lỗi console và chụp ảnh minh chứng.

---

### Task 1: TDD Failing Tests cho UI Entry Points của Chương Truyện

**Files:**
- Modify: `tests/Feature/AuthorNovelsGuidelinesTest.php`
- Modify: `tests/Feature/AuthorNovelCrudTest.php`

- [x] **Step 1: Viết failing test trong `AuthorNovelsGuidelinesTest.php`**
  Thêm assertions kiểm tra trên trang `/tac-gia/truyen`:
  - Thẻ truyện chứa link `href="/tac-gia/truyen/{id}/chuong/them-moi"` với text hoặc aria-label chứa "Soạn Chương" hoặc "Viết Chương".
  - Thẻ truyện chứa link `href="/tac-gia/truyen/{id}/tach-chuong"` với text "Tách Chương".
  - Thẻ truyện chứa link `href="/tac-gia/truyen/{id}/kho-3d"` với text "Kho 3D".

- [x] **Step 2: Viết failing test trong `AuthorNovelCrudTest.php`**
  Thêm test kiểm tra trên trang `/tac-gia/truyen/{id}/edit`:
  - Khi truyện có chương: View nhận biến `$novel` chứa relation `chapters`, hiển thị tiêu đề chương, số từ, và nút link tới `/tac-gia/truyen/{id}/chuong/{chapter_id}/soan-thao`.
  - Hiển thị nút `+ Soạn Chương Mới` và `Tách Chương Hàng Loạt`.

- [x] **Step 3: Chạy test để xác nhận Red (Failure)**
  ```powershell
  php artisan test --filter=AuthorNovelsGuidelinesTest ; php artisan test --filter=AuthorNovelCrudTest
  ```

---

### Task 2: Nâng Cấp Giao Diện Thẻ Truyện trên Danh Sách Tác Giả (`index.blade.php`)

**Files:**
- Modify: `resources/views/author/novels/index.blade.php:73-95`

- [x] **Step 1: Thiết kế lại thanh thao tác (Action Bar) trên mỗi thẻ truyện**
  - **Nút hành động chính (Primary Action):**
    Nút `+ Soạn Chương` với màu sắc nổi bật, tương phản cao:
    - Light/Dark: `bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm`
    - Sepia: `sepia:bg-amber-800 sepia:hover:bg-amber-700`
    - Icon bút viết với `aria-hidden="true"`, link đến `route('author.chapters.create', $novel->id)`.
  - **Các nút chức năng bổ trợ (Secondary Actions Grid):**
    - `Tách Chương` (`route('author.novels.bulk-chapters.create', $novel->id)`): Hỗ trợ nạp văn bản hàng loạt.
    - `Kho 3D` (`route('author.novels.3d-assets.index', $novel->id)`): Quản lý mô hình 3D của tác phẩm.
    - `Sửa Thông Tin` (`route('author.novels.edit', $novel->id)`): Chỉnh sửa tên, thể loại, ảnh bìa.
    - `👁️ Xem` (`route('reader.stories.show', $novel->slug)`): Xem giao diện người đọc.

- [x] **Step 2: Tối ưu UI/UX & Khả năng tiếp cận (Accessibility)**
  - Thêm `aria-label` chi tiết cho từng nút kèm tên truyện, đảm bảo tuân thủ WCAG 2.5.3 (Label in Name).
  - Sử dụng `transition-colors`, `transition-opacity` (loại bỏ hoàn toàn `transition-all`).
  - Đảm bảo touch target thân thiện di động (`min-h-[38px]`, padding cân đối).

- [x] **Step 3: Chạy test để xác nhận Green**
  ```powershell
  php artisan test --filter=AuthorNovelsGuidelinesTest
  ```

---

### Task 3: Tích Hợp Bảng Quản Lý Chương & Bản Thảo vào Trang Chỉnh Sửa Truyện (`create_or_edit.blade.php`)

**Files:**
- Modify: `app/Http/Controllers/Author/NovelController.php:78-86`
- Modify: `resources/views/author/novels/create_or_edit.blade.php`

- [x] **Step 1: Cập nhật `NovelController::edit` để nạp chapters**
  Eager-load quan hệ `chapters` theo thứ tự chương tăng dần:
  ```php
  $novel = $truyen->load(['chapters' => fn($q) => $q->orderBy('chapter_number', 'asc')]);
  ```

- [x] **Step 2: Thêm khối giao diện "Quản Lý Chương & Bản Thảo" vào `create_or_edit.blade.php`**
  Chỉ hiển thị khi `$novel->exists`:
  - **Header khu vực chương:**
    - Tiêu đề: `Danh Sách Chương ({{ $novel->chapters->count() }} chương)`
    - Các nút hành động nhanh:
      - `+ Soạn Chương Mới` (Zen Editor)
      - `📥 Tách Chương Hàng Loạt` (Bulk Splitter)
      - `🎨 Quản Lý Kho 3D`
  - **Bảng danh sách chương hiện có:**
    - Cột: Số chương (`#1.00` với font `tabular-nums`), Tiêu đề chương, Số từ (`xx chữ`), Trạng thái badge (`draft`: Bản nháp, `published`: Đã xuất bản), Cập nhật lần cuối.
    - Cột Thao tác:
      - Nút `Sửa Bản Thảo` dẫn tới `route('author.chapters.edit', [$novel->id, $chapter->id])`.
      - Nút `Gắn Hiệu Ứng 3D` dẫn tới `route('author.chapters.projections.index', $chapter->id)`.
  - **Empty state (khi chưa có chương):**
    - Hộp thông báo ấm áp, mời tác giả bấm nút bắt đầu soạn chương đầu tiên.

- [x] **Step 3: Chạy test để xác nhận Green**
  ```powershell
  php artisan test --filter=AuthorNovelCrudTest
  ```

---

### Task 4: Kiểm Thử Toàn Diện Bộ Test Suite

**Files:**
- Toàn bộ test suite PHPUnit

- [x] **Step 1: Chạy toàn bộ test suite để đảm bảo không có bất kỳ regression nào**
  ```powershell
  php artisan test --compact
  ```
- [x] **Step 2: Commit các thay đổi code giao diện và test**
  ```powershell
  git add . ; git commit -m "feat(author): add prominent chapter action triggers and management table in author studio"
  ```

---

### Task 5: Kiểm Thử E2E Tương Tác Chuột Thật trên Trình Duyệt (Playwright)

**Công cụ:** `playwright` MCP

- [x] **Step 1: Đăng nhập tác giả và truy cập `/tac-gia/truyen`**
  - Đăng nhập `author_test@icegozi.com`.
  - Vào `http://localhost:8000/tac-gia/truyen`.
  - Chụp ảnh màn hình danh sách truyện hiển thị các nút thao tác mới.

- [x] **Step 2: Thao tác click chuột thật trên nút `+ Soạn Chương`**
  - Sử dụng `browser_click` click vào nút `+ Soạn Chương` trên thẻ truyện `Thần Ma Đại Lục`.
  - Xác nhận trình duyệt tự động chuyển hướng thành công đến `/tac-gia/truyen/8/chuong/them-moi` (Zen Editor).
  - Chụp ảnh màn hình bằng chứng.

- [x] **Step 3: Thao tác click chuột thật vào trang Chỉnh Sửa Truyện**
  - Click vào nút `Sửa Thông Tin`.
  - Trình duyệt điều hướng tới `/tac-gia/truyen/8/edit`.
  - Cuộn chuột xuống kiểm tra bảng "Quản Lý Chương & Bản Thảo" hiển thị đầy đủ Chương 1 ("Chương 1: Thần Kiếm Xuất Thế", 36 chữ).
  - Click nút `Sửa Bản Thảo` trên hàng của Chương 1.
  - Xác nhận mở đúng Zen Editor của Chương 1.
  - Chụp ảnh màn hình bằng chứng.

- [x] **Step 4: Kiểm tra console log trình duyệt**
  - Đảm bảo 0 console errors và 0 console warnings.
