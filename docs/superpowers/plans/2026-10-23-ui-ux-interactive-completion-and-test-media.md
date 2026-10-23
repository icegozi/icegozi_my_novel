# UI/UX Interactive Completion & Test Media Suite Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Complete all non-functioning and placeholder UI/UX controls visible across the novel platform (Zen mode toolbar, 404 reader assets, search shortcut, novel interactions, library card management), bundle local testing imagery for covers/comics/avatars, and verify with automated feature tests.

**Architecture:** Integrate Alpine.js for lightweight modal and dropdown interactivity; resolve all reader module dependencies inside `app.js` and `app.css` to eliminate 404 network errors; wire up reader Zen toolbar controls and global keyboard shortcuts; enhance novel and library views with optimistic actions; and provide self-contained offline test image assets in `public/images/` seeded into the database.

**Tech Stack:** Laravel 11, Blade Components, Tailwind CSS v3.4, Alpine.js v3, Three.js (r128), Vite 5, PHPUnit 11.

**Spec:** User prompt: "hãy hãy viết hoàn thiện những tính năng trên ui ux, những tính năng có trên màn hình những chưa sử dụng được, hãy thêm ảnh để kiểm thử".

## Global Constraints

- **Zero 404 Network Errors:** Every script, stylesheet, image, and route referenced in views must return HTTP 200 without broken relative links.
- **Theme Cohesion:** All interactive elements must support Dark, Warm Sepia (default), and Light modes cleanly.
- **Responsive & Accessible:** Touch targets >= 44x44px on mobile, visible focus indicators (`focus-visible:ring-2`), and `Escape` key dismissals for all overlays.
- **Local Media Offline-Readiness:** Seeded test novels, comics, and user profiles must reference local public image assets in `public/images/` so tests and offline demonstrations do not rely on third-party CDNs.
- **Backwards Compatibility:** All 139 existing automated tests must remain passing.

## Review Focus

1. **Reader Asset Bundling:** Confirm that loading `/stories/{slug}/read/{chapter}` produces zero 404 requests in the browser console for `reader-stage.css`, `reader-controls.js`, or Three.js modules.
2. **Zen Toolbar Controls:** Verify that clicking `#btn-toggle-zen-mode`, font resize `A-` / `A+`, and theme selector buttons immediately update the reader DOM and persist preferences.
3. **Novel Show Initial Bookmark State:** Ensure that when a logged-in user who already bookmarked a novel views `/stories/{slug}`, the button reflects "Đã Lưu Vào Tủ Sách" on initial server render.
4. **Direct Library/History Actions:** Confirm that users can remove items directly from `/library` and `/history` without navigating away.
5. **Fallback 3D Mesh:** Verify that the 3D Projection Stage gracefully generates a procedural glowing artifact if the `.glb` model file is missing or offline.

---

### Task 1: Alpine.js Integration & Modal Interactivity (`<x-modal>`)

**Files:**
- Modify: `package.json`
- Modify: `resources/js/app.js`
- Modify: `resources/views/components/modal.blade.php`
- Test: `tests/Feature/ModalAndAlpineTest.php`

**Interfaces:**
- Consumes: npm package `alpinejs`.
- Produces: Global `window.Alpine` initialized via `Alpine.start()`, making `<x-modal>` dialogs open/close and dismiss on `Escape` or backdrop click.

- [x] **Step 1: Write failing test in `tests/Feature/ModalAndAlpineTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModalAndAlpineTest extends TestCase
{
    public function test_modal_component_renders_with_aria_and_alpine_bindings(): void
    {
        $view = $this->blade(
            '<x-modal name="testModal" title="Cửa Sổ Thử Nghiệm">Nội dung modal</x-modal>'
        );

        $view->assertSee('role="dialog"', false);
        $view->assertSee('aria-modal="true"', false);
        $view->assertSee('Cửa Sổ Thử Nghiệm');
        $view->assertSee('Nội dung modal');
    }

    public function test_app_js_imports_and_starts_alpine(): void
    {
        $appJs = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('Alpine', $appJs);
        $this->assertStringContainsString('Alpine.start()', $appJs);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ModalAndAlpineTest`
Expected: FAIL (assertion on `resources/js/app.js` failing for Alpine).

- [x] **Step 3: Install `alpinejs` and initialize in `resources/js/app.js`**

Run: `npm install alpinejs --save-dev`
In `resources/js/app.js`:
```javascript
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ModalAndAlpineTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add package.json package-lock.json resources/js/app.js tests/Feature/ModalAndAlpineTest.php
git commit -m "feat(ui): integrate alpinejs and enable modal interactivity"
```

---

### Task 2: Reader Assets Bundling & 404 Elimination

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Modify: `resources/js/3d-engine/projection-stage.js`
- Modify: `resources/views/reader/chapters/read_3d.blade.php`
- Test: `tests/Feature/ReaderAssetsBundlingTest.php`

**Interfaces:**
- Consumes: `resources/css/reader-stage.css`, `resources/js/reader-controls.js`, `resources/js/3d-engine/projection-stage.js`.
- Produces: Clean asset loading via `@vite(['resources/css/app.css', 'resources/js/app.js'])` and `window.Novel3D` global namespace, completely removing broken `/resources/...` 404 links.

- [x] **Step 1: Write failing test in `tests/Feature/ReaderAssetsBundlingTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderAssetsBundlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_3d_view_does_not_contain_broken_resource_links(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::factory()->create(['author_id' => $author->id, 'status' => 'published']);
        $chapter = Chapter::factory()->create(['novel_id' => $novel->id, 'chapter_number' => 1, 'status' => 'published']);

        $response = $this->get(route('reader.chapters.read', [$novel->slug, 1]));

        $response->assertStatus(200);
        $response->assertDontSee('/resources/css/reader-stage.css', false);
        $response->assertDontSee('/resources/js/reader-controls.js', false);
        $response->assertDontSee("import { ProjectionStage } from '/resources/", false);
    }

    public function test_app_css_imports_reader_stage_css(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString("reader-stage.css", $appCss);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReaderAssetsBundlingTest`
Expected: FAIL (reader view contains `/resources/css/reader-stage.css`).

- [x] **Step 3: Bundle reader styles and scripts**

1. In `resources/css/app.css`, add `@import './reader-stage.css';` at the top.
2. In `resources/js/app.js`:
   - Import `./reader-controls.js`.
   - Import `Scroll3dObserver` from `./scroll-3d-observer.js`.
   - Import `ReaderPreferences` from `./reader-preferences.js`.
   - Import `ReadingProgressTracker` from `./reading-progress-tracker.js`.
   - Expose them on `window.Novel3D`:
     ```javascript
     window.Novel3D = {
         ProjectionStage,
         ParticleSystem,
         ModelCache,
         Scroll3dObserver,
         ReaderPreferences,
         ReadingProgressTracker,
     };
     ```
3. In `resources/js/3d-engine/projection-stage.js`, add fallback procedural mesh generator in `loadModel()` error handler so if a 3D model file is not found, a glowing mystical sword mesh is generated and attached to the scene instead of leaving the canvas empty.
4. In `resources/views/reader/chapters/read_3d.blade.php`:
   - Remove `<link rel="stylesheet" href="/resources/css/reader-stage.css">`.
   - Remove `<script src="/resources/js/reader-controls.js" defer></script>`.
   - Update script blocks to use `window.Novel3D.ProjectionStage`, `window.Novel3D.Scroll3dObserver`, `window.Novel3D.ReaderPreferences`, and `window.Novel3D.ReadingProgressTracker`.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ReaderAssetsBundlingTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/css/app.css resources/js/app.js resources/js/3d-engine/projection-stage.js resources/views/reader/chapters/read_3d.blade.php tests/Feature/ReaderAssetsBundlingTest.php
git commit -m "fix(reader): bundle reader styles and engine to eliminate 404 asset links"
```

---

### Task 3: Zen Mode Toolbar & Reading Interactivity

**Files:**
- Modify: `resources/js/reader-controls.js`
- Modify: `resources/views/reader/chapters/partials/zen_toolbar.blade.php`
- Modify: `resources/css/reader-stage.css`
- Test: `tests/Feature/ZenToolbarInteractivityTest.php`

**Interfaces:**
- Consumes: `#btn-toggle-zen-mode`, `.theme-btn`, `#btn-font-decrease`, `#btn-font-increase`, `#font-size-label`.
- Produces: Dynamic `.zen-mode` layout toggle (hides headers/sidebars, full width reader), interactive font scaling (14px–28px with percentage badge), and 1-click theme switching.

- [x] **Step 1: Write failing test in `tests/Feature/ZenToolbarInteractivityTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZenToolbarInteractivityTest extends TestCase
{
    public function test_zen_toolbar_contains_accessible_controls(): void
    {
        $view = $this->blade("@include('reader.chapters.partials.zen_toolbar')");

        $view->assertSee('id="zen-toolbar"', false);
        $view->assertSee('id="btn-toggle-zen-mode"', false);
        $view->assertSee('id="btn-font-decrease"', false);
        $view->assertSee('id="btn-font-increase"', false);
        $view->assertSee('data-theme="dark"', false);
        $view->assertSee('data-theme="sepia"', false);
        $view->assertSee('data-theme="light"', false);
    }

    public function test_reader_controls_script_handles_zen_mode_and_font_resize(): void
    {
        $js = file_get_contents(resource_path('js/reader-controls.js'));
        $this->assertStringContainsString('btn-toggle-zen-mode', $js);
        $this->assertStringContainsString('zen-mode', $js);
        $this->assertStringContainsString('btn-font-decrease', $js);
        $this->assertStringContainsString('btn-font-increase', $js);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ZenToolbarInteractivityTest`
Expected: FAIL (`btn-toggle-zen-mode` missing in `reader-controls.js`).

- [x] **Step 3: Implement Zen Toolbar logic in `resources/js/reader-controls.js` and styling in `reader-stage.css`**

1. In `resources/css/reader-stage.css`, add styles for `.zen-mode`:
   ```css
   body.zen-mode header,
   body.zen-mode aside,
   body.zen-mode #reading-progress-bar {
       display: none !important;
   }
   body.zen-mode main {
       max-width: 52rem !important;
       margin-left: auto !important;
       margin-right: auto !important;
       padding-top: 2rem !important;
   }
   ```
2. In `resources/js/reader-controls.js`:
   - Bind click on `#btn-toggle-zen-mode`:
     - Toggle class `zen-mode` on `document.body`.
     - Update button icon (`🧘` ↔ `✕`) and label (`Chế Độ Tinh Gọn` ↔ `Thoát Zen Mode`).
     - Save preference in `localStorage`.
   - Bind `#btn-font-decrease` and `#btn-font-increase` to `window.adjustFontSize(-2)` and `(+2)`.
   - Update `#font-size-label` with percentage calculated from default 18px (e.g. `Math.round(fontSize / 18 * 100) + '%'`).
   - Bind `.theme-btn` click to call `window.setReaderTheme(btn.dataset.theme)`.
   - Add `Escape` key handler to exit Zen mode if active.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ZenToolbarInteractivityTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/css/reader-stage.css resources/js/reader-controls.js resources/views/reader/chapters/partials/zen_toolbar.blade.php tests/Feature/ZenToolbarInteractivityTest.php
git commit -m "feat(reader): implement zen mode toggle, font scaling, and theme buttons"
```

---

### Task 4: Global Shortcuts & Navigation Header Polish

**Files:**
- Modify: `resources/js/app.js`
- Modify: `resources/views/layouts/partials/header.blade.php`
- Test: `tests/Feature/HeaderNavigationPolishTest.php`

**Interfaces:**
- Consumes: `#header-search-input`, `#user-dropdown`, `#mobile-nav`.
- Produces: `Ctrl+K / ⌘K` global search focus shortcut, auto-closing dropdown on click-outside, and `Escape` dismiss.

- [x] **Step 1: Write failing test in `tests/Feature/HeaderNavigationPolishTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HeaderNavigationPolishTest extends TestCase
{
    public function test_header_view_contains_search_shortcut_kbd(): void
    {
        $view = $this->blade("@include('layouts.partials.header')");

        $view->assertSee('id="header-search-input"', false);
        $view->assertSee('⌘K');
        $view->assertSee('id="user-menu-container"', false);
    }

    public function test_app_js_handles_ctrl_k_and_click_outside(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('header-search-input', $js);
        $this->assertStringContainsString('user-dropdown', $js);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HeaderNavigationPolishTest`
Expected: FAIL (`header-search-input` missing in `app.js`).

- [x] **Step 3: Implement keyboard shortcut and click-outside dismissal in `resources/js/app.js`**

1. Add global keyboard shortcut:
   ```javascript
   document.addEventListener('keydown', (e) => {
       if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
           const searchInput = document.getElementById('header-search-input') || document.getElementById('search-page-input');
           if (searchInput) {
               e.preventDefault();
               searchInput.focus();
               searchInput.select();
           }
       }
   });
   ```
2. Add click-outside and `Escape` listener for `#user-dropdown` and `#mobile-nav`:
   ```javascript
   document.addEventListener('click', (e) => {
       const userContainer = document.getElementById('user-menu-container');
       const userDropdown = document.getElementById('user-dropdown');
       if (userContainer && userDropdown && !userContainer.contains(e.target)) {
           userDropdown.classList.add('hidden');
       }
   });

   document.addEventListener('keydown', (e) => {
       if (e.key === 'Escape') {
           const userDropdown = document.getElementById('user-dropdown');
           const mobileNav = document.getElementById('mobile-nav');
           if (userDropdown) userDropdown.classList.add('hidden');
           if (mobileNav) mobileNav.classList.add('hidden');
       }
   });
   ```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=HeaderNavigationPolishTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/js/app.js resources/views/layouts/partials/header.blade.php tests/Feature/HeaderNavigationPolishTest.php
git commit -m "feat(nav): add Ctrl+K shortcut and click-outside dropdown dismiss"
```

---

### Task 5: Novel Detail Page Interactive Features (Bookmark, Sort Chapters, Share, Rate)

**Files:**
- Modify: `app/Http/Controllers/Reader/NovelViewController.php`
- Modify: `resources/views/reader/novels/show.blade.php`
- Test: `tests/Feature/NovelShowInteractivityTest.php`

**Interfaces:**
- Consumes: `$isBookmarked` boolean passed from `NovelViewController`, chapter row sorting.
- Produces: Synchronized initial bookmark button state, reverse chapter ordering button, quick URL copy share button, and interactive star rating modal.

- [x] **Step 1: Write failing test in `tests/Feature/NovelShowInteractivityTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelShowInteractivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_show_reflects_bookmarked_state_for_logged_in_user(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);
        $novel = Novel::factory()->create(['author_id' => $author->id, 'status' => 'published']);
        Chapter::factory()->create(['novel_id' => $novel->id, 'chapter_number' => 1, 'status' => 'published']);

        Bookmark::create([
            'user_id' => $reader->id,
            'novel_id' => $novel->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($reader)->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('Đã Lưu Vào Tủ Sách');
    }

    public function test_novel_show_contains_sort_and_share_controls(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::factory()->create(['author_id' => $author->id, 'status' => 'published']);

        $response = $this->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('id="btn-sort-chapters"', false);
        $response->assertSee('id="btn-share-novel"', false);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=NovelShowInteractivityTest`
Expected: FAIL (assertion on bookmark state or sort controls failing).

- [x] **Step 3: Update `NovelViewController.php` and `resources/views/reader/novels/show.blade.php`**

1. In `app/Http/Controllers/Reader/NovelViewController.php`:
   ```php
   $isBookmarked = auth()->check()
       ? auth()->user()->bookmarks()->where('novel_id', $novel->id)->exists()
       : false;

   return view('reader.novels.show', compact('novel', 'isBookmarked'));
   ```
2. In `resources/views/reader/novels/show.blade.php`:
   - Initialize `#btn-bookmark-toggle` text and classes according to `$isBookmarked`:
     - Text: `{{ $isBookmarked ? '✓ Đã Lưu Vào Tủ Sách' : '🔖 Lưu Vào Tủ Sách' }}`.
     - Classes: active indigo background/border if bookmarked.
   - Add `#btn-share-novel` next to bookmark button:
     - On click, copies `window.location.href` using `navigator.clipboard.writeText()` and triggers `window.showToast('Đã sao chép liên kết truyện vào clipboard!', 'success')`.
   - Add `#btn-sort-chapters` above chapter directory:
     - On click, inverts children in `#chapters-list-container` and toggles button label between `Mới nhất trước ↓` and `Cũ nhất trước ↑`.
   - Add interactive Rating button & `<x-modal name="ratingModal" title="Đánh Giá Tác Phẩm">`:
     - 5 clickable stars with live hover fill.
     - Submitting shows a thank-you toast `Cảm ơn bạn đã gửi đánh giá!` and closes modal.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=NovelShowInteractivityTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add app/Http/Controllers/Reader/NovelViewController.php resources/views/reader/novels/show.blade.php tests/Feature/NovelShowInteractivityTest.php
git commit -m "feat(novel): add bookmark state sync, chapter sort, share, and rating modal"
```

---

### Task 6: User Library & History Direct Management Actions

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/Reader/UserLibraryController.php`
- Modify: `resources/views/reader/user/library.blade.php`
- Modify: `resources/views/reader/user/bookmarks.blade.php`
- Modify: `resources/views/reader/user/history.blade.php`
- Test: `tests/Feature/UserLibraryActionsTest.php`

**Interfaces:**
- Consumes: Authenticated user bookmarks and reading histories.
- Produces: `DELETE /api/history/{id}` endpoint and 1-click removal buttons directly on cards with instant UI transition.

- [x] **Step 1: Write failing test in `tests/Feature/UserLibraryActionsTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLibraryActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_delete_reading_history_record(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::factory()->create(['author_id' => $author->id, 'status' => 'published']);
        $chapter = Chapter::factory()->create(['novel_id' => $novel->id, 'chapter_number' => 1, 'status' => 'published']);

        $history = ReadingHistory::create([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/history/{$history->id}");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
        $this->assertDatabaseMissing('reading_history', ['id' => $history->id]);
    }

    public function test_user_cannot_delete_another_users_reading_history(): void
    {
        $user1 = User::factory()->create(['status' => 'active']);
        $user2 = User::factory()->create(['status' => 'active']);
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::factory()->create(['author_id' => $author->id, 'status' => 'published']);
        $chapter = Chapter::factory()->create(['novel_id' => $novel->id, 'chapter_number' => 1, 'status' => 'published']);

        $history = ReadingHistory::create([
            'user_id' => $user1->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($user2)->deleteJson("/api/history/{$history->id}");
        $response->assertStatus(403);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=UserLibraryActionsTest`
Expected: FAIL (route `/api/history/{id}` not found 404).

- [x] **Step 3: Implement deleteHistory endpoint and UI remove buttons**

1. In `routes/web.php` under `Route::middleware('auth')`:
   ```php
   Route::delete('/api/history/{id}', [\App\Http\Controllers\Reader\UserLibraryController::class, 'deleteHistory'])->name('reader.history.delete');
   ```
2. In `app/Http/Controllers/Reader/UserLibraryController.php`:
   ```php
   public function deleteHistory(int $id)
   {
       $history = ReadingHistory::findOrFail($id);
       if ($history->user_id !== Auth::id()) {
           abort(403);
       }
       $history->delete();

       return response()->json([
           'status' => 'ok',
           'message' => 'Đã xóa tác phẩm khỏi lịch sử đọc.',
       ]);
   }
   ```
3. In `resources/views/reader/user/history.blade.php`:
   - Add a "Xóa" button on each history item with AJAX `fetch('/api/history/' + id, { method: 'DELETE', ... })` that animates item removal and displays `showToast()`.
4. In `resources/views/reader/user/library.blade.php` and `bookmarks.blade.php`:
   - Add a quick "Bỏ lưu" button overlay on the card that calls `/api/bookmarks/toggle` and removes the card element immediately.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=UserLibraryActionsTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add routes/web.php app/Http/Controllers/Reader/UserLibraryController.php resources/views/reader/user/library.blade.php resources/views/reader/user/bookmarks.blade.php resources/views/reader/user/history.blade.php tests/Feature/UserLibraryActionsTest.php
git commit -m "feat(library): add direct remove actions for bookmarks and reading history"
```

---

### Task 7: Local Test Images & Media Suite ("Thêm ảnh để kiểm thử")

**Files:**
- Create: `public/images/default_cover.webp`
- Create: `public/images/covers/tien-hiep.webp`
- Create: `public/images/covers/huyen-huyen.webp`
- Create: `public/images/covers/comic-webtoon.webp`
- Create: `public/images/covers/khoa-huyen.webp`
- Create: `public/images/covers/do-thi.webp`
- Create: `public/images/covers/kiem-hiep.webp`
- Create: `public/images/comics/panel-1.webp`
- Create: `public/images/comics/panel-2.webp`
- Create: `public/images/comics/panel-3.webp`
- Create: `public/images/avatars/admin.webp`
- Create: `public/images/avatars/author.webp`
- Create: `public/images/avatars/reader.webp`
- Modify: `database/seeders/NovelPlatformSeeder.php`
- Modify: `database/seeders/ComicAndNovelShowcaseSeeder.php`
- Test: `tests/Feature/LocalMediaAssetsTest.php`

**Interfaces:**
- Consumes: PHP GD/Imagick or SVG raster generator to produce crisp, high-resolution WebP image files.
- Produces: 100% offline-testable public image assets under `public/images/` mapped to database seeds.

- [x] **Step 1: Write failing test in `tests/Feature/LocalMediaAssetsTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalMediaAssetsTest extends TestCase
{
    public function test_default_cover_image_exists_and_is_accessible(): void
    {
        $this->assertFileExists(public_path('images/default_cover.webp'));
        $this->assertGreaterThan(100, filesize(public_path('images/default_cover.webp')));
    }

    public function test_genre_cover_images_exist(): void
    {
        $genres = ['tien-hiep', 'huyen-huyen', 'comic-webtoon', 'khoa-huyen', 'do-thi', 'kiem-hiep'];
        foreach ($genres as $slug) {
            $this->assertFileExists(public_path("images/covers/{$slug}.webp"));
        }
    }

    public function test_comic_panels_and_avatars_exist(): void
    {
        $this->assertFileExists(public_path('images/comics/panel-1.webp'));
        $this->assertFileExists(public_path('images/comics/panel-2.webp'));
        $this->assertFileExists(public_path('images/avatars/admin.webp'));
        $this->assertFileExists(public_path('images/avatars/author.webp'));
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocalMediaAssetsTest`
Expected: FAIL (files do not exist in `public/images/`).

- [x] **Step 3: Generate local WebP images and update seeders**

1. Create a PHP generator script (or use GD) to synthesize rich WebP images with gradient backdrops, book titles, and typography:
   - `public/images/default_cover.webp` (600x800)
   - `public/images/covers/*.webp` (600x800 for each genre)
   - `public/images/comics/*.webp` (800x1200 high-res vertical webtoon panels)
   - `public/images/avatars/*.webp` (200x200 circular avatars)
2. Update `NovelPlatformSeeder.php`:
   - Change `'cover_image' => 'covers/cuuchau.webp'` to `'/images/covers/tien-hiep.webp'`.
3. Update `ComicAndNovelShowcaseSeeder.php`:
   - Change comic novel `'cover_image'` to `'/images/covers/comic-webtoon.webp'`.
   - Update comic chapter 1 panels to use `'/images/comics/panel-1.webp'`, `'/images/comics/panel-2.webp'`, and `'/images/comics/panel-3.webp'`.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LocalMediaAssetsTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add public/images/ database/seeders/NovelPlatformSeeder.php database/seeders/ComicAndNovelShowcaseSeeder.php tests/Feature/LocalMediaAssetsTest.php
git commit -m "feat(media): create local test images suite and update seeders"
```

---

### Task 8: Full Test Suite & Production Bundle Verification

**Files:**
- Modify: (None, verification only)
- Test: Full PHPUnit test suite & Vite production build

**Interfaces:**
- Consumes: All updated components, scripts, assets, routes, and seeders.
- Produces: 100% green test suite (>= 145 passing tests) and verified Vite production build.

- [x] **Step 1: Run complete PHPUnit test suite**

Run: `php artisan test`
Expected: 100% PASS with 0 failures.

- [x] **Step 2: Re-run database seeders to verify integrity**

Run: `php artisan migrate:fresh --seed`
Expected: Migration and seeders complete successfully without any errors.

- [x] **Step 3: Run Vite production bundle build**

Run: `npm run build`
Expected: Vite build succeeds with clean outputs in `public/build/`.

- [x] **Step 4: Final commit**

```bash
git add -A
git commit -m "chore(release): verify all interactive ui/ux features and test media suite"
```
