# Complete Story Reading Platform UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete, responsive, and production-quality story and comic reading web platform with comprehensive discovery, chapter reading, authentication, library management, and user settings.

**Architecture:** A modern Laravel 11 Blade + Tailwind CSS architecture with modular component layouts, full RESTful reader controllers, interactive vanilla JavaScript reading engines (supporting text typography customization, light/dark/sepia themes, and continuous vertical comic scrolling), and SQLite/MySQL Eloquent persistence.

**Tech Stack:** PHP 8.4, Laravel 11, Blade Components, Tailwind CSS v3.4, Vite v5, Vanilla JavaScript (ES6+), Google Fonts (Plus Jakarta Sans, Cinzel, Merriweather, Inter, JetBrains Mono).

**Spec:** Master Prompt — Build a Complete Story Reading Website UI (20 routes, 3 mandatory skills: UI/UX Pro Max, Frontend Design, Web Design Guidelines).

## Global Constraints

- Preserve all existing routes, models, and 112 passing PHPUnit tests (3D features, copyright ND 17/2023, e-KYC ND 147/2024).
- Full Vietnamese diacritics support across all headings, metadata, body text, and notifications.
- Accessible semantic HTML with visible `:focus-visible` rings, `aria-label` for icon buttons, and `aria-live="polite"` for dynamic updates.
- 3 Distinct Reading Themes: Light (`theme-light`), Dark (`theme-dark`), and Sepia (`theme-sepia`).
- Zero layout shift (CLS < 0.1) using explicit aspect ratios on story covers and comic panels.
- Responsive design verified at 375px (mobile), 768px (tablet), and 1280px+ (desktop).
- No placeholder or broken images: realistic SVG / seeded graphics for covers and comic panels.

## Review Focus

1. **Chapter reader font/theme preference persistence**: Unauthenticated guests must have their font size, line height, reading width, and theme saved to `localStorage` so changes persist across page reloads without errors.
2. **Reading progress calculation**: Scroll offset calculation must handle edge cases (empty chapter, single-line chapter, resize events) without generating NaN or dividing by zero.
3. **Vietnamese text line wrapping and punctuation**: Typography must use `text-wrap: balance` for titles and `text-pretty` for excerpts, preventing single orphan words or awkward breaks.
4. **Comic vs. Text reading mode switching**: The chapter reader must seamlessly detect and render comic page streams (vertical continuous scroll) or text paragraphs according to chapter/novel content type.
5. **Form validation and error recovery**: Authentication and settings forms must retain valid input values (`old()`), highlight invalid fields with `aria-invalid="true"`, and display an accessible error summary banner at the top of the form.

---

### Task 1: Design Tokens, Typography, and Shared Blade Layout Foundation

**Files:**
- Modify: `tailwind.config.js:1-21`
- Modify: `resources/css/app.css:1-5`
- Create: `resources/views/layouts/reader.blade.php`
- Create: `resources/views/layouts/partials/header.blade.php`
- Create: `resources/views/layouts/partials/footer.blade.php`
- Create: `resources/js/theme-manager.js`
- Test: `tests/Feature/LayoutAndThemeTest.php`

**Interfaces:**
- Consumes: Tailwind CSS framework, Bunny/Google Fonts CDN (`Plus Jakarta Sans`, `Cinzel`, `Merriweather`, `Inter`).
- Produces: `layouts.reader` Blade component with customizable slots, `<x-reader-layout>`, semantic color tokens (`--color-bg`, `--color-surface`, `--color-primary`, `--color-accent`), and client-side `theme-manager.js`.

- [ ] **Step 1: Write the failing test for layout rendering and theme tags**

```php
// tests/Feature/LayoutAndThemeTest.php
namespace Tests\Feature;

use Tests\TestCase;

class LayoutAndThemeTest extends TestCase
{
    public function test_reader_layout_renders_with_meta_and_fonts(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Plus+Jakarta+Sans', false);
        $response->assertSee('theme-manager', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter LayoutAndThemeTest`
Expected: FAIL with missing view or assertions failing on default welcome page.

- [ ] **Step 3: Implement design tokens and master layout**

1. Extend `tailwind.config.js` with fonts (`sans`: Plus Jakarta Sans, `novel`: Merriweather, `heading`: Cinzel, `mono`: JetBrains Mono) and semantic reader palette (`surface`, `sepia`, `accent-amber`, `brand-violet`).
2. Add CSS custom properties in `resources/css/app.css` for light, dark, and sepia themes.
3. Create `resources/views/layouts/reader.blade.php` with skip-link, header, main slot, footer, and toast container.
4. Implement `resources/views/layouts/partials/header.blade.php` with responsive navigation, search trigger, theme switcher, and library/auth shortcuts.
5. Implement `resources/views/layouts/partials/footer.blade.php` with category directories and copyright notices.
6. Create `resources/js/theme-manager.js` to manage dark/light/sepia modes and synchronize with `localStorage`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter LayoutAndThemeTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tailwind.config.js resources/css/app.css resources/views/layouts resources/js/theme-manager.js tests/Feature/LayoutAndThemeTest.php
git commit -m "feat(design-system): implement design tokens, typography, and reader layout"
```

---

### Task 2: Rich Platform Seed Data for Stories and Comics

**Files:**
- Modify: `database/seeders/NovelPlatformSeeder.php:90-199`
- Create: `database/seeders/ComicAndNovelShowcaseSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php:1-25`
- Test: `tests/Feature/CatalogSeedDataTest.php`

**Interfaces:**
- Consumes: `App\Models\Novel`, `App\Models\Chapter`, `App\Models\Genre`, `App\Models\User`.
- Produces: 8+ rich novels across Tiên Hiệp, Huyền Huyễn, Đô Thị, Khoa Huyễn, Kiếm Hiệp, and a Comic/Manga titled "Võ Thần Tái Sinh (Webtoon)" with illustrated panel data, ratings, views, and bookmark statistics.

- [ ] **Step 1: Write the failing test for catalog seed data**

```php
// tests/Feature/CatalogSeedDataTest.php
namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_contains_novels_and_comics_with_chapters(): void
    {
        $this->seed();
        $this->assertGreaterThanOrEqual(5, Novel::where('status', 'published')->count());
        $this->assertGreaterThanOrEqual(5, Genre::count());
        $this->assertNotNull(Novel::where('slug', 'vo-than-tai-sinh-webtoon')->first());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter CatalogSeedDataTest`
Expected: FAIL with `assertNotNull failed` for comic novel slug.

- [ ] **Step 3: Implement `ComicAndNovelShowcaseSeeder` and register in `DatabaseSeeder`**

1. Create `database/seeders/ComicAndNovelShowcaseSeeder.php` populating realistic Vietnamese literature metadata, multi-genre associations, multiple chapters with realistic story paragraphs, and a full comic chapter containing 8 high-resolution SVG/webp comic panels.
2. Link into `DatabaseSeeder.php` alongside `NovelPlatformSeeder.php`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter CatalogSeedDataTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/seeders tests/Feature/CatalogSeedDataTest.php
git commit -m "feat(seeders): populate realistic story and comic showcase records"
```

---

### Task 3: Discovery & Catalog Routes (`/`, `/latest`, `/popular`, `/genres`, `/genres/:slug`)

**Files:**
- Create: `app/Http/Controllers/Reader/HomeController.php`
- Create: `app/Http/Controllers/Reader/CatalogController.php`
- Create: `app/Http/Controllers/Reader/GenreController.php`
- Create: `resources/views/reader/home.blade.php`
- Create: `resources/views/reader/catalog/latest.blade.php`
- Create: `resources/views/reader/catalog/popular.blade.php`
- Create: `resources/views/reader/genres/index.blade.php`
- Create: `resources/views/reader/genres/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/DiscoveryPagesTest.php`

**Interfaces:**
- Consumes: `Novel`, `Genre`, `Chapter`, `ReadingHistory`.
- Produces:
  - `GET /` -> `HomeController@index` -> `reader.home`
  - `GET /latest` -> `CatalogController@latest` -> `reader.catalog.latest`
  - `GET /popular` -> `CatalogController@popular` -> `reader.catalog.popular`
  - `GET /genres` -> `GenreController@index` -> `reader.genres.index`
  - `GET /genres/{slug}` -> `GenreController@show` -> `reader.genres.show`

- [ ] **Step 1: Write failing test for discovery routes**

```php
// tests/Feature/DiscoveryPagesTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_discovery_pages_render_successfully(): void
    {
        $this->seed();
        $this->get('/')->assertStatus(200)->assertSee('Truyện Đề Cử');
        $this->get('/latest')->assertStatus(200)->assertSee('Mới Cập Nhật');
        $this->get('/popular')->assertStatus(200)->assertSee('Bảng Xếp Hạng');
        $this->get('/genres')->assertStatus(200)->assertSee('Thể Loại');
        $this->get('/genres/tien-hiep')->assertStatus(200)->assertSee('Tiên Hiệp');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter DiscoveryPagesTest`
Expected: FAIL with 404 or missing route.

- [ ] **Step 3: Implement controllers, views, and routes**

1. `HomeController`: Fetches featured stories, trending stories, latest chapter updates, genre badges, and continue-reading history if authenticated.
2. `CatalogController`: Provides `/latest` (ordered by chapter published_at) and `/popular` (ordered by view_count and rating_avg) with pagination.
3. `GenreController`: Lists all genres with count of published stories, and shows stories matching specific genre slug.
4. Build responsive, editorial Blade views using `layouts.reader`, story cards with hover depth, rating badges, word count metadata, and zero layout shift.
5. Register routes in `routes/web.php`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter DiscoveryPagesTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Reader/ resources/views/reader/ routes/web.php tests/Feature/DiscoveryPagesTest.php
git commit -m "feat(discovery): implement home, latest, popular, and genre catalog pages"
```

---

### Task 4: Search & Filters (`/search`)

**Files:**
- Create: `app/Http/Controllers/Reader/SearchController.php`
- Create: `resources/views/reader/search.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/SearchPageTest.php`

**Interfaces:**
- Consumes: Query parameters `q` (keyword), `genre` (slug), `status` (all/completed/ongoing), `sort` (relevance/latest/views/rating).
- Produces: `GET /search` -> `SearchController@index` -> `reader.search`.

- [ ] **Step 1: Write failing test for search functionality**

```php
// tests/Feature/SearchPageTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_filters_by_keyword_and_genre(): void
    {
        $this->seed();
        $response = $this->get('/search?q=Cửu+Châu');
        $response->assertStatus(200);
        $response->assertSee('Cửu Châu Thần Ma Lục');

        $emptyResponse = $this->get('/search?q=NonExistentKeywordXYZ');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Không tìm thấy kết quả phù hợp');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter SearchPageTest`
Expected: FAIL with 404.

- [ ] **Step 3: Implement SearchController and `reader/search.blade.php`**

1. `SearchController@index`: Query builder filtering by title/synopsis, genre slug, age rating, copyright status, sorting, and pagination (12 items per page).
2. `reader/search.blade.php`: Clean filter sidebar / top bar, search input with clear button, active filter tags, responsive story card grid, and helpful no-results empty state with recommendations.
3. Add quick search modal shortcut (`Cmd+K` / `Ctrl+K`) integration in header.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter SearchPageTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Reader/SearchController.php resources/views/reader/search.blade.php routes/web.php tests/Feature/SearchPageTest.php
git commit -m "feat(search): implement search and faceted filtering page"
```

---

### Task 5: Story Detail Page (`/stories/:slug` and `/truyen/:slug`)

**Files:**
- Modify: `app/Http/Controllers/Reader/NovelViewController.php`
- Modify: `resources/views/reader/novels/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/StoryDetailRouteTest.php`

**Interfaces:**
- Consumes: `Novel` slug.
- Produces:
  - `GET /stories/{slug}` and `GET /truyen/{slug}` rendering unified, polished story detail page.
  - Returns chapter list with sorting (newest/oldest) and read status indicators.

- [ ] **Step 1: Write failing test for `/stories/{slug}` route alias and content**

```php
// tests/Feature/StoryDetailRouteTest.php
namespace Tests\Feature;

use App\Models\Novel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryDetailRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_detail_renders_at_both_stories_and_truyen_slugs(): void
    {
        $this->seed();
        $novel = Novel::where('status', 'published')->first();

        $this->get('/stories/' . $novel->slug)
            ->assertStatus(200)
            ->assertSee($novel->title)
            ->assertSee('Danh Sách Chương');

        $this->get('/truyen/' . $novel->slug)
            ->assertStatus(200)
            ->assertSee($novel->title);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter StoryDetailRouteTest`
Expected: FAIL with 404 for `/stories/{slug}`.

- [ ] **Step 3: Update `NovelViewController.php`, `routes/web.php`, and `show.blade.php`**

1. Register `Route::get('/stories/{slug}', [NovelViewController::class, 'show'])->name('reader.stories.show')`.
2. Update `show.blade.php` to inherit `layouts.reader`, featuring:
   - Atmospheric backdrop blur header with cover artwork.
   - Author badge, genres, age rating, word count, chapter count, average rating, bookmark button.
   - Synopsis with readable typography.
   - Interactive Chapter List with fast search, sort order toggle (cũ nhất / mới nhất), and direct read action.
   - 3D Interactive Exhibition entry button if 3D assets exist.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter StoryDetailRouteTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Reader/NovelViewController.php resources/views/reader/novels/show.blade.php routes/web.php tests/Feature/StoryDetailRouteTest.php
git commit -m "feat(story-detail): polish story detail view with /stories/{slug} route support"
```

---

### Task 6: Full Chapter Reading Experience for Text and Comics (`/stories/:slug/read/:chapter`)

**Files:**
- Modify: `app/Http/Controllers/Reader/ChapterReaderController.php`
- Modify: `resources/views/reader/chapters/read_3d.blade.php`
- Create: `resources/views/reader/chapters/reader.blade.php`
- Create: `resources/js/reader-controls.js`
- Modify: `routes/web.php`
- Test: `tests/Feature/ChapterReaderExperienceTest.php`

**Interfaces:**
- Consumes: `Novel` slug, chapter number or slug.
- Produces:
  - `GET /stories/{slug}/read/{chapter}` and `GET /truyen/{slug}/chuong/{chapterNumber}`.
  - Text story reader: font size (14px-24px), font family (Serif/Sans), line height (1.4-2.2), width constraints, theme switcher (Light, Dark, Sepia), Prev/Next chapter navigation, Table of Contents drawer, bookmark button, keyboard handlers.
  - Comic reader: continuous vertical scrolling panel mode, image zoom, image loading skeletons, responsive image sizing.

- [ ] **Step 1: Write failing test for the unified reading experience**

```php
// tests/Feature/ChapterReaderExperienceTest.php
namespace Tests\Feature;

use App\Models\Novel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterReaderExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_chapter_reader_renders_for_text_and_comic(): void
    {
        $this->seed();
        $novel = Novel::where('status', 'published')->first();
        $firstChapter = $novel->chapters()->orderBy('chapter_number')->first();

        $response = $this->get("/stories/{$novel->slug}/read/{$firstChapter->chapter_number}");
        $response->assertStatus(200);
        $response->assertSee($firstChapter->title);
        $response->assertSee('reader-controls.js', false);
        $response->assertSee('theme-sepia', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter ChapterReaderExperienceTest`
Expected: FAIL with 404 for `/stories/{slug}/read/{chapter}`.

- [ ] **Step 3: Implement controller enhancements, reader view, and `reader-controls.js`**

1. Register `/stories/{slug}/read/{chapter}` in `routes/web.php` routing to `ChapterReaderController::read`.
2. Create `resources/views/reader/chapters/reader.blade.php` incorporating:
   - Sticky top reading progress bar.
   - Minimal floating navigation bar with Quick Prev, TOC, Font Settings, Theme Toggle, Next Chapter.
   - Slide-out Settings drawer for Font Size, Line Spacing, Column Width, and Background Mode.
   - Slide-out Table of Contents drawer with current chapter highlighted.
   - Auto-detection for Comic panels (rendering vertical image reel) vs. Text paragraphs (Merriweather editorial typography).
   - Preserved 3D interactive stage trigger if projections exist.
3. Implement `resources/js/reader-controls.js` managing settings, keyboard shortcuts (`ArrowLeft` for Prev, `ArrowRight` for Next, `t` for Theme, `Esc` for Drawers), and local/remote progress sync.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter ChapterReaderExperienceTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Reader/ChapterReaderController.php resources/views/reader/chapters/ resources/js/reader-controls.js routes/web.php tests/Feature/ChapterReaderExperienceTest.php
git commit -m "feat(reader): implement complete text and comic reading experience"
```

---

### Task 7: Authentication Flow (`/login`, `/register`, `/forgot-password`, `/reset-password`)

**Files:**
- Create: `app/Http/Controllers/Auth/AuthController.php`
- Create: `resources/views/auth/login.blade.php`
- Create: `resources/views/auth/register.blade.php`
- Create: `resources/views/auth/forgot-password.blade.php`
- Create: `resources/views/auth/reset-password.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/AuthenticationFlowTest.php`

**Interfaces:**
- Consumes: Credentials (email, password, name, password_confirmation), CSRF token.
- Produces:
  - `GET /login`, `POST /login`, `POST /logout`
  - `GET /register`, `POST /register`
  - `GET /forgot-password`, `POST /forgot-password`
  - `GET /reset-password/{token}`, `POST /reset-password`

- [ ] **Step 1: Write failing test for authentication flow**

```php
// tests/Feature/AuthenticationFlowTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_and_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->get('/login')->assertStatus(200)->assertSee('Đăng Nhập');

        $response = $this->post('/login', [
            'email' => 'reader@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_with_validation(): void
    {
        $this->get('/register')->assertStatus(200)->assertSee('Tạo Tài Khoản');

        $response = $this->post('/register', [
            'name' => 'Nguyễn Độc Giả',
            'email' => 'newreader@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'newreader@example.com']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter AuthenticationFlowTest`
Expected: FAIL with login returning plain string or 404 for register.

- [ ] **Step 3: Implement `AuthController` and auth views**

1. Create `AuthController` with `showLogin`, `login`, `showRegister`, `register`, `showForgotPassword`, `sendResetLink`, `showResetPassword`, `resetPassword`, and `logout`.
2. Build accessible, elegant Blade views for each auth screen with:
   - Editorial cover graphic / artwork panel on desktop.
   - Accessible labels, autofocus on primary input, `autocomplete` tags.
   - Password visibility toggle with SVG eye icon.
   - Quick demo account one-click filler buttons (`admin@novel3d.vn`, `author@novel3d.vn`).
   - Top error summary alert with role="alert" and inline field errors.
3. Update `routes/web.php` replacing the stub `/login` route with full auth routes.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter AuthenticationFlowTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Auth/ resources/views/auth/ routes/web.php tests/Feature/AuthenticationFlowTest.php
git commit -m "feat(auth): implement login, register, forgot-password, and reset-password flows"
```

---

### Task 8: Authenticated User Area (`/library`, `/history`, `/bookmarks`, `/notifications`, `/profile`, `/settings`, `/settings/security`)

**Files:**
- Create: `app/Http/Controllers/Reader/UserLibraryController.php`
- Create: `app/Http/Controllers/Reader/UserProfileController.php`
- Create: `resources/views/reader/user/library.blade.php`
- Create: `resources/views/reader/user/history.blade.php`
- Create: `resources/views/reader/user/bookmarks.blade.php`
- Create: `resources/views/reader/user/notifications.blade.php`
- Create: `resources/views/reader/user/profile.blade.php`
- Create: `resources/views/reader/user/settings.blade.php`
- Create: `resources/views/reader/user/security.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/UserAreaRoutesTest.php`

**Interfaces:**
- Consumes: Authenticated session (`auth` middleware).
- Produces:
  - `GET /library` -> `UserLibraryController@library`
  - `GET /history` -> `UserLibraryController@history`
  - `GET /bookmarks` -> `UserLibraryController@bookmarks`
  - `POST /api/bookmarks/toggle` -> `UserLibraryController@toggleBookmark`
  - `GET /notifications` -> `UserProfileController@notifications`
  - `GET /profile`, `POST /profile` -> `UserProfileController@profile`
  - `GET /settings`, `POST /settings` -> `UserProfileController@settings`
  - `GET /settings/security`, `POST /settings/security` -> `UserProfileController@security`

- [ ] **Step 1: Write failing test for user area routes**

```php
// tests/Feature/UserAreaRoutesTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAreaRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_library_history_and_settings(): void
    {
        $this->seed();
        $user = User::first();

        $this->actingAs($user)->get('/library')->assertStatus(200)->assertSee('Tủ Sách Cá Nhân');
        $this->actingAs($user)->get('/history')->assertStatus(200)->assertSee('Lịch Sử Đọc');
        $this->actingAs($user)->get('/bookmarks')->assertStatus(200)->assertSee('Đánh Dấu');
        $this->actingAs($user)->get('/notifications')->assertStatus(200)->assertSee('Thông Báo');
        $this->actingAs($user)->get('/profile')->assertStatus(200)->assertSee('Hồ Sơ');
        $this->actingAs($user)->get('/settings')->assertStatus(200)->assertSee('Cài Đặt');
        $this->actingAs($user)->get('/settings/security')->assertStatus(200)->assertSee('Bảo Mật');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter UserAreaRoutesTest`
Expected: FAIL with 404.

- [ ] **Step 3: Implement controllers, views, and routes**

1. `UserLibraryController`:
   - `library`: Stories saved in personal reading lists with reading status filters.
   - `history`: Timeline of read chapters, last read date, percentage completed, and "Tiếp tục đọc" action.
   - `bookmarks`: Bookmarked stories and chapters.
   - `toggleBookmark`: JSON endpoint to add/remove novel from bookmarks.
2. `UserProfileController`:
   - `profile`: Display name, email, avatar, reading statistics.
   - `settings`: Reading defaults (font size, line height, reader width, default theme) and language.
   - `security`: Password update with current password validation, active session list.
   - `notifications`: Activity alerts (new chapters, system notifications).
3. Connect all routes in `routes/web.php` with `auth` middleware (redirecting unauthenticated guests to `/login`).

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter UserAreaRoutesTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Reader/User* resources/views/reader/user/ routes/web.php tests/Feature/UserAreaRoutesTest.php
git commit -m "feat(user-area): implement library, history, bookmarks, profile, and settings"
```

---

### Task 9: Branded 404 Page and Web Interface Guidelines Quality Polish

**Files:**
- Create: `resources/views/errors/404.blade.php`
- Modify: `resources/views/layouts/partials/header.blade.php`
- Modify: `resources/views/reader/chapters/reader.blade.php`
- Test: `tests/Feature/NotFoundAndAccessibilityTest.php`

**Interfaces:**
- Consumes: Any invalid URL.
- Produces: Branded 404 page with navigation recovery, proper semantic headers, WCAG 2.1 AA compliant contrast and focus indicators.

- [ ] **Step 1: Write failing test for 404 page**

```php
// tests/Feature/NotFoundAndAccessibilityTest.php
namespace Tests\Feature;

use Tests\TestCase;

class NotFoundAndAccessibilityTest extends TestCase
{
    public function test_not_found_page_renders_branded_view_with_recovery_links(): void
    {
        $response = $this->get('/non-existent-page-xyz');
        $response->assertStatus(404);
        $response->assertSee('Trang Không Tồn Tại');
        $response->assertSee('Trở Về Trang Chủ');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter NotFoundAndAccessibilityTest`
Expected: FAIL with default Laravel 404 page or missing text.

- [ ] **Step 3: Create `resources/views/errors/404.blade.php` and run Web Design Guidelines checks**

1. Build `resources/views/errors/404.blade.php` matching the brand aesthetic with book-themed illustration, search bar, and direct links to Home, Catalog, and Library.
2. Audit all Blade views against Web Design Guidelines:
   - Ensure all buttons have `type="button"` or `type="submit"`.
   - Ensure all icon-only buttons have descriptive `aria-label`.
   - Ensure all form inputs have associated `<label>` and correct `autocomplete`.
   - Verify `focus-visible:ring-2` on all interactive elements.
   - Verify `scroll-margin-top` on headings.
   - Check `text-wrap: balance` on headers.
   - Check `tabular-nums` on word counts and timestamps.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter NotFoundAndAccessibilityTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/errors/404.blade.php tests/Feature/NotFoundAndAccessibilityTest.php
git commit -m "feat(quality): add branded 404 page and web interface guidelines compliance"
```

---

### Task 10: Full Regression Suite, Production Vite Build, and Visual Verification

**Files:**
- Modify: `resources/js/app.js`
- Test: Full PHPUnit test suite (`php artisan test`)
- Build: `npm run build`

- [ ] **Step 1: Run full PHPUnit suite**

Run: `php artisan test`
Expected: All tests pass (original 112 + new route and feature tests = 120+ passing tests).

- [ ] **Step 2: Run production Vite build**

Run: `npm run build`
Expected: Exit code 0, CSS and JS bundled cleanly in `public/build`.

- [ ] **Step 3: Visual inspection via browser subagent or local HTTP rendering check**

Inspect rendered pages at mobile (375px), tablet (768px), and desktop (1280px) viewports to verify aesthetic quality, typography hierarchy, zero horizontal overflow, and correct dark/sepia/light theme switching.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "chore(release): complete story reading platform UI with full test and build verification"
```
