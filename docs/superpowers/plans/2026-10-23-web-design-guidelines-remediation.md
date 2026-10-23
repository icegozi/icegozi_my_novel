# Web Interface Guidelines Remediation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remediate all accessibility, typography, touch target, CLS, and CSS transition issues identified during the Web Interface Guidelines audit across the reader, novel details, navigation header, and user library views.

**Architecture:** Refactor Blade templates to include explicit `aria-label`, `aria-hidden="true"`, semantic labels, and focus rings (`focus-visible:ring-2`); stabilize layout with explicit `width`/`height` on images to eliminate Cumulative Layout Shift (CLS); update CSS transitions from `transition: all` to explicit properties with `prefers-reduced-motion` fallbacks; and verify compliance using automated feature tests.

**Tech Stack:** Laravel 11, Blade Components, Tailwind CSS v3.4, Alpine.js v3, Vite 5, PHPUnit 11.

**Spec:** Audit findings and recommendations from `/web-design-guidelines` review on October 23, 2026.

## Global Constraints

- **Accessibility First:** Every icon-only button must have an explicit `aria-label`. All decorative emojis and icons must have `aria-hidden="true"`.
- **Keyboard & Focus States:** Never use `focus:outline-none` without `focus-visible:ring-2 focus-visible:ring-indigo-500` (or equivalent visible focus indicator).
- **Animation Hygiene:** Never use `transition: all`. Always specify transitioned properties and honor `prefers-reduced-motion: reduce`.
- **Layout Stability:** All `<img>` tags must include explicit `width` and `height` attributes to prevent CLS.
- **Typography Standards:** Always use typographic ellipsis `…` instead of triple dots `...`. Numeric metrics must use `tabular-nums`.
- **Backwards Compatibility:** All existing 154 automated tests must remain 100% passing.

## Review Focus

1. **Touch Target Size on Reader Toolbar:** Confirm theme selector buttons have an effective touch hit target >= 44x44px and `touch-action: manipulation` on mobile viewports.
2. **Keyboard Discovery of Card Removal:** Ensure that navigating via `Tab` key on `/library` and `/bookmarks` makes the remove button visible (`focus-visible:opacity-100`).
3. **Motion Sensitivity:** Verify that `reader-stage.css` disables transitions and highlight animations when the user has enabled `prefers-reduced-motion: reduce`.
4. **Accessible Chapter Filtering:** Ensure `#chapter-filter-input` is read properly by screen readers with an explicit label.
5. **Rating Stars Accessibility:** Confirm each star button in the rating modal provides a distinct `:aria-label="'Đánh giá ' + star + ' sao'"`.

---

### Task 1: CSS Transitions & Motion Accessibility (`reader-stage.css`)

**Files:**
- Modify: `resources/css/reader-stage.css`
- Test: `tests/Feature/ReaderCssGuidelinesTest.php`

**Interfaces:**
- Consumes: `.paragraph-highlight-3d`, `body.zen-mode #zen-toolbar`.
- Produces: Explicit property transitions without `transition: all` and `@media (prefers-reduced-motion: reduce)` overrides.

- [x] **Step 1: Write failing test in `tests/Feature/ReaderCssGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReaderCssGuidelinesTest extends TestCase
{
    public function test_reader_stage_css_does_not_use_transition_all(): void
    {
        $css = file_get_contents(resource_path('css/reader-stage.css'));

        $this->assertStringNotContainsString('transition: all', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReaderCssGuidelinesTest`
Expected: FAIL (assertion `assertStringNotContainsString('transition: all', $css)` fails).

- [x] **Step 3: Update `resources/css/reader-stage.css`**

1. Replace `transition: all 0.3s ease;` with:
   ```css
   transition: background-color 0.3s ease, border-color 0.3s ease, padding 0.3s ease;
   ```
2. Add a `prefers-reduced-motion` media block at the end of `reader-stage.css`:
   ```css
   @media (prefers-reduced-motion: reduce) {
       .paragraph-highlight-3d,
       .theme-btn,
       #zen-toolbar,
       #zen-toolbar button {
           transition: none !important;
           animation: none !important;
       }
   }
   ```

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ReaderCssGuidelinesTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/css/reader-stage.css tests/Feature/ReaderCssGuidelinesTest.php
git commit -m "fix(css): replace transition-all and add prefers-reduced-motion rules"
```

---

### Task 2: Zen Toolbar Accessibility & Touch Targets (`zen_toolbar.blade.php`)

**Files:**
- Modify: `resources/views/reader/chapters/partials/zen_toolbar.blade.php`
- Test: `tests/Feature/ZenToolbarGuidelinesTest.php`

**Interfaces:**
- Consumes: `#zen-toolbar`, `.theme-btn`, `#btn-font-decrease`, `#btn-font-increase`, `#font-size-label`.
- Produces: WCAG compliant theme buttons with `aria-label`, 44px hit-target padding, `tabular-nums` typography, and `aria-hidden="true"` on emoji.

- [x] **Step 1: Write failing test in `tests/Feature/ZenToolbarGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZenToolbarGuidelinesTest extends TestCase
{
    public function test_zen_toolbar_has_accessible_labels_and_numeric_classes(): void
    {
        $view = $this->blade("@include('reader.chapters.partials.zen_toolbar')");

        $view->assertSee('aria-label="Chuyển sang giao diện tối"', false);
        $view->assertSee('aria-label="Chuyển sang giao diện giấy cổ"', false);
        $view->assertSee('aria-label="Chuyển sang giao diện sáng"', false);
        $view->assertSee('aria-label="Giảm kích thước phông chữ"', false);
        $view->assertSee('aria-label="Tăng kích thước phông chữ"', false);
        $view->assertSee('tabular-nums', false);
        $view->assertSee('aria-hidden="true"', false);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ZenToolbarGuidelinesTest`
Expected: FAIL (missing `aria-label` attributes).

- [x] **Step 3: Update `resources/views/reader/chapters/partials/zen_toolbar.blade.php`**

1. Theme selector buttons:
   - Add `aria-label="Chuyển sang giao diện tối"` to dark button.
   - Add `aria-label="Chuyển sang giao diện giấy cổ"` to sepia button.
   - Add `aria-label="Chuyển sang giao diện sáng"` to light button.
   - Wrap or style theme buttons with `p-2 touch-manipulation` or an accessible 44x44px target area.
2. Font controls:
   - Add `aria-label="Giảm kích thước phông chữ"` to `#btn-font-decrease`.
   - Add `aria-label="Tăng kích thước phông chữ"` to `#btn-font-increase`.
   - Update `#font-size-label` to include `font-mono tabular-nums`.
3. Zen icon:
   - Update `<span id="zen-icon">🧘</span>` to `<span id="zen-icon" aria-hidden="true">🧘</span>`.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ZenToolbarGuidelinesTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/views/reader/chapters/partials/zen_toolbar.blade.php tests/Feature/ZenToolbarGuidelinesTest.php
git commit -m "fix(a11y): add aria-labels, tabular-nums and touch targets to zen toolbar"
```

---

### Task 3: Navigation Header Accessibility & Focus Rings (`header.blade.php`)

**Files:**
- Modify: `resources/views/layouts/partials/header.blade.php`
- Test: `tests/Feature/HeaderGuidelinesTest.php`

**Interfaces:**
- Consumes: `#user-menu-button`, `#mobile-menu-toggle`.
- Produces: Visible focus rings on keyboard navigation and explicit `aria-label` on user profile menu trigger.

- [x] **Step 1: Write failing test in `tests/Feature/HeaderGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_menu_button_has_accessible_label_and_focus_ring(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('aria-label="Menu tài khoản cá nhân"', false);
        $response->assertSee('focus-visible:ring-2', false);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HeaderGuidelinesTest`
Expected: FAIL (missing `aria-label="Menu tài khoản cá nhân"`).

- [x] **Step 3: Update `resources/views/layouts/partials/header.blade.php`**

1. On `#user-menu-button`:
   - Add `aria-label="Menu tài khoản cá nhân"`.
   - Update class list from `focus:outline-none` to `focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500`.
2. On `#mobile-menu-toggle`:
   - Update class list from `focus:outline-none` to `focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500`.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=HeaderGuidelinesTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/views/layouts/partials/header.blade.php tests/Feature/HeaderGuidelinesTest.php
git commit -m "fix(a11y): add user-menu-button aria-label and visible focus rings"
```

---

### Task 4: Novel Detail Page Accessibility & Typography (`show.blade.php`)

**Files:**
- Modify: `resources/views/reader/novels/show.blade.php`
- Test: `tests/Feature/NovelShowGuidelinesTest.php`

**Interfaces:**
- Consumes: CTAs, chapter filter search, star rating modal.
- Produces: Hidden decorative icons (`aria-hidden="true"`), accessible chapter filter label, screen-reader friendly star rating buttons, and typographic ellipsis `…`.

- [x] **Step 1: Write failing test in `tests/Feature/NovelShowGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelShowGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_show_has_accessible_filter_and_star_labels(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Võ Thần Tái Sinh',
            'slug' => 'vo-than-tai-sinh',
            'synopsis' => 'Tiểu thuyết tu chân',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);

        $response = $this->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Lọc danh sách chương"', false);
        $response->assertSee(':aria-label="\'Đánh giá \' + star + \' sao\'"', false);
        $response->assertSee('placeholder="Viết vài dòng chia sẻ về truyện…"', false);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=NovelShowGuidelinesTest`
Expected: FAIL (missing `aria-label` or typographic ellipsis).

- [x] **Step 3: Update `resources/views/reader/novels/show.blade.php`**

1. In CTAs:
   - Add `<span aria-hidden="true">📖</span>` on "Đọc Từ Đầu".
   - Add `<span aria-hidden="true">🔖</span>` on "Lưu Vào Tủ Sách".
   - Add `<span aria-hidden="true">🔗</span>` on "Chia Sẻ".
   - Add `<span aria-hidden="true">★</span>` on "Đánh Giá".
   - Add `<span aria-hidden="true">✨</span>` on "Phòng Trưng Bày 3D".
2. On `#chapter-filter-input`:
   - Add `aria-label="Lọc danh sách chương"`.
3. In Rating Modal:
   - Add `:aria-label="'Đánh giá ' + star + ' sao'"` to star buttons.
   - Update textarea placeholder to `"Viết vài dòng chia sẻ về truyện…"`.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=NovelShowGuidelinesTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/views/reader/novels/show.blade.php tests/Feature/NovelShowGuidelinesTest.php
git commit -m "fix(a11y): add aria-hidden on decorative emojis, accessible star rating and filter labels"
```

---

### Task 5: User Library, Bookmarks, and History CLS & Keyboard Focus (`library`, `bookmarks`, `history`)

**Files:**
- Modify: `resources/views/reader/user/library.blade.php`
- Modify: `resources/views/reader/user/bookmarks.blade.php`
- Modify: `resources/views/reader/user/history.blade.php`
- Test: `tests/Feature/UserLibraryGuidelinesTest.php`

**Interfaces:**
- Consumes: Book cover images and quick remove overlay buttons.
- Produces: Zero Cumulative Layout Shift via explicit `width` and `height` attributes on images, and keyboard-accessible remove buttons (`focus-visible:opacity-100`).

- [x] **Step 1: Write failing test in `tests/Feature/UserLibraryGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLibraryGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_and_history_views_include_image_dimensions_and_focus_opacity(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Thần Ma Biến',
            'slug' => 'than-ma-bien',
            'synopsis' => 'Tiểu thuyết kiểm thử',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1',
            'slug' => 'chuong-1',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $user->bookmarks()->create(['novel_id' => $novel->id]);
        ReadingHistory::create([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $libResponse = $this->actingAs($user)->get(route('reader.library'));
        $libResponse->assertStatus(200);
        $libResponse->assertSee('width="300"', false);
        $libResponse->assertSee('height="400"', false);
        $libResponse->assertSee('focus-visible:opacity-100', false);

        $histResponse = $this->actingAs($user)->get(route('reader.history'));
        $histResponse->assertStatus(200);
        $histResponse->assertSee('width="56"', false);
        $histResponse->assertSee('height="80"', false);
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=UserLibraryGuidelinesTest`
Expected: FAIL (missing `width="300"`, `focus-visible:opacity-100`).

- [x] **Step 3: Update `library.blade.php`, `bookmarks.blade.php`, and `history.blade.php`**

1. In `resources/views/reader/user/library.blade.php`:
   - Add `width="300" height="400"` to cover `<img>`.
   - Update remove button classes to include `focus-visible:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500`.
2. In `resources/views/reader/user/bookmarks.blade.php`:
   - Add `width="300" height="400"` to cover `<img>`.
   - Update remove button classes to include `focus-visible:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500`.
3. In `resources/views/reader/user/history.blade.php`:
   - Add `width="56" height="80"` to thumbnail `<img>`.
   - Add `focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500` to the delete history button.

- [x] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=UserLibraryGuidelinesTest`
Expected: PASS.

- [x] **Step 5: Commit changes**

```bash
git add resources/views/reader/user/library.blade.php resources/views/reader/user/bookmarks.blade.php resources/views/reader/user/history.blade.php tests/Feature/UserLibraryGuidelinesTest.php
git commit -m "fix(a11y): add image dimensions for CLS and keyboard focus discovery on card actions"
```

---

### Task 6: Comprehensive Verification & Production Build

**Files:**
- Modify: (None, verification only)
- Test: Full PHPUnit test suite & Vite production build

**Interfaces:**
- Consumes: All updated templates, styles, and tests.
- Produces: 100% green test suite (>= 159 passing tests) and verified Vite production build.

- [x] **Step 1: Run complete PHPUnit test suite**

Run: `php artisan test`
Expected: 100% PASS across all tests with 0 failures.

- [x] **Step 2: Run Vite production bundle build**

Run: `npm run build`
Expected: Vite build completes cleanly with zero errors.

- [x] **Step 3: Final commit**

```bash
git add -A
git commit -m "chore(release): complete web design guidelines remediation"
```
