# Loại Bỏ Chế Độ Tối & Thay Bằng Chủ Đề Màu Vàng (Sepia) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove dark mode completely from the novel platform reading experience and public interfaces, replacing it with a clean binary theme system between the current warm yellow parchment theme (`sepia` / Giấy Cổ) and light mode (`light` / Sáng).

**Architecture:** Update backend controllers and request validation to treat `sepia` as the canonical non-light reading theme while auto-migrating legacy `dark` preferences; update client theme managers (`theme-manager.js`, `reader-preferences.js`, and inline SSR boot scripts) to cycle exclusively between `sepia` (Màu Vàng / Giấy Cổ) and `light` (Sáng); update reader UI components (Zen toolbar, 3D reader settings drawer, user settings page, and header button) to eliminate dark mode controls and present polished dual-theme selection.

**Tech Stack:** Laravel 11.31, Blade Templates, Tailwind CSS v3.4 (`.sepia` variant), Alpine.js v3.14, Vanilla JS Theme Manager, Vite 5.4, PHPUnit 11.

**Spec:** User directive: "tôi muốn bỏ chế độ tối, chế độ tối sẽ thay bằng chủ đề màu vàng hiện tại".

---

## Global Constraints

- **Shell Protocol:** Windows 11 PowerShell syntax with `;` command separators; never use POSIX `&&` or `cd` commands.
- **Role Invariant:** Table `users.role` constrained to `reader`, `author`, `moderator`, `admin`; never use `'role' => 'user'`.
- **Accessibility & WCAG:** Interactive controls must retain accessible labels (WCAG 2.5.3); decorative icons must have `aria-hidden="true"`; zero `transition-all`.
- **Theme Color & FOUC:** SSR `<html class="sepia">` and `<meta name="theme-color" content="#f5efe6">` prevent flash of wrong theme; inline blocking scripts execute before DOM paint.
- **Regression Invariant:** Maintain 100% pass rate across the full PHPUnit test suite (currently 229 passing tests).

---

## Review Focus

1. **Legacy Client Storage Migration (`localStorage.novel_reader_theme = 'dark'`):** An existing reader holding `'dark'` in `localStorage` must be immediately migrated to `'sepia'` on page boot without visual flashing or script errors.
2. **Backward Compatible API Requests (`POST /api/reader/preferences` with `'theme' => 'dark'`):** Any cached script, cookie, or API client sending `'dark'` must be transparently sanitized to `'sepia'` and return 200 OK with `preferences.theme = 'sepia'`, not 422 Unprocessable Entity.
3. **1-Click Theme Cycle Smoothness:** The header theme toggle (`window.toggleReaderTheme()`) and shortcut (`T` key) must cleanly alternate between `sepia` and `light` (`sepia` <-> `light`).
4. **3D Reader Canvas Initial Styling (`read_3d.blade.php`):** The chapter reader body element must initialize with `theme-sepia sepia` instead of the legacy `theme-dark`, avoiding any dark flicker before scripts execute.
5. **Zen Toolbar Button State Management:** With `data-theme="dark"` removed, the active ring indicator (`ring-2 ring-indigo-500`) must correctly highlight either the Yellow (`sepia`) or Light (`light`) button.

---

### Task 1: Controller & Validation Layer: Migrate Default & Valid Themes from Dark to Sepia (Yellow)

**Files:**
- Modify: `app/Http/Controllers/Reader/UserProfileController.php:38-60`
- Modify: `app/Http/Controllers/Reader/ChapterReaderController.php:80-96`
- Modify: `tests/Feature/ReaderPreferencesTest.php:64-93`

**Interfaces:**
- Consumes: HTTP request payloads containing `theme`, `font_size`, `mode`.
- Produces: Session and JSON response containing sanitized `preferences.theme` (`'sepia'` or `'light'`), auto-migrating legacy `'dark'` to `'sepia'`.

- [ ] **Step 1: Write failing tests in `tests/Feature/ReaderPreferencesTest.php` for yellow/light theme validation and dark migration**

```php
// tests/Feature/ReaderPreferencesTest.php
public function test_reader_preferences_api_accepts_sepia_and_light_themes(): void
{
    $response = $this->actingAs($this->user)
        ->postJson(route('reader.preferences.update'), [
            'theme' => 'sepia',
            'font_size' => 'base',
        ]);

    $response->assertOk()
        ->assertJsonPath('preferences.theme', 'sepia');

    $responseLight = $this->actingAs($this->user)
        ->postJson(route('reader.preferences.update'), [
            'theme' => 'light',
            'font_size' => 'base',
        ]);

    $responseLight->assertOk()
        ->assertJsonPath('preferences.theme', 'light');
}

public function test_reader_preferences_api_migrates_legacy_dark_theme_to_sepia(): void
{
    $response = $this->actingAs($this->user)
        ->postJson(route('reader.preferences.update'), [
            'theme' => 'dark',
            'font_size' => 'base',
        ]);

    $response->assertOk()
        ->assertJsonPath('preferences.theme', 'sepia');

    $this->assertEquals('sepia', session('reader_preferences.theme'));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter ReaderPreferencesTest`
Expected: FAIL on `test_reader_preferences_api_migrates_legacy_dark_theme_to_sepia` because current controller saves `'dark'` as-is without migrating to `'sepia'`.

- [ ] **Step 3: Update `UserProfileController.php` and `ChapterReaderController.php`**

1. In `app/Http/Controllers/Reader/UserProfileController.php`:
   - Change default preference from `'theme' => 'dark'` to `'theme' => 'sepia'`.
   - In `updateSettings(Request $request)`:
     If `$request->input('theme') === 'dark'`, merge `['theme' => 'sepia']`.
     Validate `'theme' => ['required', 'in:sepia,light']`.
2. In `app/Http/Controllers/Reader/ChapterReaderController.php`:
   - In `updatePreferences(Request $request)`:
     If `$request->input('theme') === 'dark'`, merge `['theme' => 'sepia']`.
     Validate `'theme' => ['required', 'in:sepia,light']`.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter ReaderPreferencesTest`
Expected: PASS with all tests passing.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Reader/ tests/Feature/ReaderPreferencesTest.php ; git commit -m "feat(theme): migrate backend reader theme defaults and validation from dark to sepia"
```

---

### Task 2: Client-side Scripting & Layout Boot: Update Theme Managers & SSR Scripts

**Files:**
- Modify: `resources/js/theme-manager.js:1-55`
- Modify: `resources/js/reader-preferences.js:20-30, 80-110`
- Modify: `resources/views/layouts/reader.blade.php:18-25`
- Modify: `resources/views/author/chapters/editor.blade.php:14-20`
- Modify: `tests/Feature/LayoutAndThemeTest.php:18-35`

**Interfaces:**
- Consumes: Browser `localStorage.novel_reader_theme`, `themechange` events.
- Produces: `VALID_THEMES = ['light', 'sepia']`, 2-state toggle rotation (`sepia` <-> `light`), instant migration of `'dark'` to `'sepia'`.

- [ ] **Step 1: Write failing test in `tests/Feature/LayoutAndThemeTest.php` asserting yellow/sepia theme rotation and dark elimination**

```php
// tests/Feature/LayoutAndThemeTest.php
public function test_theme_manager_script_contains_binary_sepia_light_rotation(): void
{
    $js = file_get_contents(resource_path('js/theme-manager.js'));

    $this->assertStringNotContainsString("'dark'", $js);
    $this->assertStringContainsString("VALID_THEMES = ['light', 'sepia']", $js);
    $this->assertStringContainsString("nextMap = { sepia: 'light', light: 'sepia'", $js);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter LayoutAndThemeTest`
Expected: FAIL because `theme-manager.js` currently includes `'dark'`.

- [ ] **Step 3: Update `theme-manager.js`, `reader-preferences.js`, and inline layout scripts**

1. In `resources/js/theme-manager.js`:
   - Update `VALID_THEMES = ['light', 'sepia'];`.
   - In `getPreferredTheme()`:
     ```javascript
     const saved = localStorage.getItem(THEME_KEY);
     if (saved === 'dark') {
         localStorage.setItem(THEME_KEY, 'sepia');
         return 'sepia';
     }
     if (saved && VALID_THEMES.includes(saved)) {
         return saved;
     }
     return 'sepia';
     ```
   - In `applyTheme(theme)`:
     Map `bgMap = { light: '#f8fafc', sepia: '#f5efe6' };`.
     Remove `'dark'` class removal list reference; keep clean class switching.
   - In `window.toggleReaderTheme`:
     ```javascript
     window.toggleReaderTheme = function () {
         const current = getPreferredTheme();
         const nextMap = { sepia: 'light', light: 'sepia' };
         applyTheme(nextMap[current] || 'sepia');
     };
     ```
2. In `resources/js/reader-preferences.js`:
   - In `loadPreferences()`: change default `theme` fallback from `'dark'` to `'sepia'`. If stored theme is `'dark'`, update to `'sepia'`.
   - In `applyTheme()`: remove `theme-dark`, set `theme-` + (`this.preferences.theme === 'dark' ? 'sepia' : this.preferences.theme`).
3. In `resources/views/layouts/reader.blade.php`:
   - In `<script>` boot block:
     ```javascript
     var theme = localStorage.getItem('novel_reader_theme') || 'sepia';
     if (theme === 'dark') {
         theme = 'sepia';
         localStorage.setItem('novel_reader_theme', 'sepia');
     }
     document.documentElement.className = theme;
     ```
4. In `resources/views/author/chapters/editor.blade.php`:
   - In `<script>` boot block:
     ```javascript
     var theme = localStorage.getItem('novel_reader_theme') || 'sepia';
     if (theme === 'dark') {
         theme = 'sepia';
         localStorage.setItem('novel_reader_theme', 'sepia');
     }
     document.documentElement.className = theme;
     ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter LayoutAndThemeTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/js/theme-manager.js resources/js/reader-preferences.js resources/views/layouts/reader.blade.php resources/views/author/chapters/editor.blade.php tests/Feature/LayoutAndThemeTest.php ; git commit -m "feat(theme): configure client theme-manager and boot scripts for binary sepia-light toggle"
```

---

### Task 3: Reader & Navigation UI: Remove Dark Controls and Polish Yellow (Sepia) Theme Presentation

**Files:**
- Modify: `resources/views/reader/chapters/partials/zen_toolbar.blade.php:1-10`
- Modify: `resources/views/reader/chapters/read_3d.blade.php:55-65, 158-168`
- Modify: `resources/views/reader/user/settings.blade.php:25-45`
- Modify: `resources/views/layouts/partials/header.blade.php:69-86`
- Modify: `tests/Feature/ZenToolbarInteractivityTest.php:10-21`
- Create: `tests/Feature/ReaderThemeControlsTest.php`

**Interfaces:**
- Consumes: Blade template layout components and CSS tokens.
- Produces: Dual-theme UI controls with "Màu Vàng (Giấy Cổ)" and "Sáng (Light)", zero dark mode buttons, and clear accessible metadata.

- [ ] **Step 1: Write failing test in `tests/Feature/ZenToolbarInteractivityTest.php` and `tests/Feature/ReaderThemeControlsTest.php`**

1. In `tests/Feature/ZenToolbarInteractivityTest.php`:
   - Replace `$view->assertSee('data-theme="dark"')` with `$view->assertDontSee('data-theme="dark"')`.
   - Retain `$view->assertSee('data-theme="sepia"')` and `$view->assertSee('data-theme="light"')`.
2. In `tests/Feature/ReaderThemeControlsTest.php`:
   ```php
   <?php

   namespace Tests\Feature;

   use App\Models\AuthorProfile;
   use App\Models\Chapter;
   use App\Models\Genre;
   use App\Models\Novel;
   use App\Models\User;
   use Illuminate\Foundation\Testing\RefreshDatabase;
   use Tests\TestCase;

   class ReaderThemeControlsTest extends TestCase
   {
       use RefreshDatabase;

       public function test_read_3d_view_defaults_to_sepia_and_has_no_dark_button(): void
       {
           $author = User::factory()->create(['role' => 'author']);
           AuthorProfile::create([
               'user_id' => $author->id,
               'pen_name' => 'Tác Giả Vàng',
               'legal_full_name' => 'Nguyễn Văn Vàng',
               'identity_number_encrypted' => 'enc123',
               'identity_hash' => 'hash123',
               'verification_status' => 'verified',
               'verified_at' => now(),
           ]);

           $novel = Novel::create([
               'author_id' => $author->id,
               'title' => 'Chủ Đề Vàng',
               'slug' => 'chu-de-vang',
               'synopsis' => 'Mô tả truyện...',
               'copyright_type' => 'original',
               'age_rating' => 'all',
               'status' => 'published',
           ]);

           $chapter = Chapter::create([
               'novel_id' => $novel->id,
               'chapter_number' => 1,
               'title' => 'Chương 1',
               'slug' => 'chuong-1',
               'content' => '<p>Nội dung trên nền vàng.</p>',
               'word_count' => 100,
               'status' => 'published',
               'published_at' => now(),
               'revision' => 1,
           ]);

           $response = $this->get(route('reader.chapters.read', ['slug' => $novel->slug, 'chapterNumber' => 1]));
           $response->assertOk();
           $response->assertSee('theme-sepia sepia', false);
           $response->assertDontSee('theme-dark', false);
           $response->assertDontSee("window.setReaderTheme('dark')", false);
       }

       public function test_user_settings_page_does_not_contain_dark_mode_option(): void
       {
           $user = User::factory()->create(['role' => 'reader']);

           $response = $this->actingAs($user)->get(route('user.settings'));
           $response->assertOk();
           $response->assertSee('Màu Vàng (Giấy Cổ)', false);
           $response->assertDontSee('value="dark"', false);
           $response->assertDontSee('Tối (Dark)', false);
       }
   }
   ```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter ZenToolbarInteractivityTest ; php artisan test --filter ReaderThemeControlsTest`
Expected: FAIL on both test files due to existing dark mode buttons and labels.

- [ ] **Step 3: Update Blade templates to remove dark controls**

1. In `resources/views/reader/chapters/partials/zen_toolbar.blade.php`:
   - Remove `<button type="button" class="theme-btn ... data-theme="dark" ...></button>`.
   - Update sepia button title/aria-label: `title="Giao diện Màu Vàng (Giấy Cổ)" aria-label="Chuyển sang giao diện màu vàng giấy cổ"`.
2. In `resources/views/reader/chapters/read_3d.blade.php`:
   - Line 59: change `<body class="theme-dark ...">` to `<body class="theme-sepia sepia min-h-screen flex flex-col antialiased transition-colors">`.
   - Line 162: change grid from `grid-cols-3` to `grid-cols-2`.
   - Line 163: remove `<button type="button" onclick="... window.setReaderTheme('dark')" ...>Tối</button>`.
   - Line 164: update button text from `Sepia` to `Màu Vàng (Giấy Cổ)`.
3. In `resources/views/reader/user/settings.blade.php`:
   - Remove the radio option for `'dark'` (`<input type="radio" name="theme" value="dark" ...>`).
   - Update sepia radio label to `Màu Vàng (Giấy Cổ)` (`value="sepia"`).
   - Keep `Sáng (Light)` (`value="light"`).
4. In `resources/views/layouts/partials/header.blade.php`:
   - Update theme switcher button:
     `aria-label="Đổi giao diện Sáng / Vàng (Sepia)"`
     `title="Đổi giao diện (Sáng / Vàng)"`
   - Update indicator SVG elements:
     Show sun icon when in sepia (to switch to light) and warm spark/coffee icon when in light (to switch to sepia).

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter ZenToolbarInteractivityTest ; php artisan test --filter ReaderThemeControlsTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/ tests/Feature/ZenToolbarInteractivityTest.php tests/Feature/ReaderThemeControlsTest.php ; git commit -m "feat(views): eliminate dark mode controls and present dual yellow-light theme selection"
```

---

### Task 4: Vite Asset Rebuild, Verification & Playwright Browser Validation

**Files:**
- Modify: `resources/js/reader-controls.js:140-155`
- Test: Full PHPUnit test suite
- Verification: Live Playwright browser testing and screenshot evidence

**Interfaces:**
- Consumes: Vite compiler and Playwright automation tools.
- Produces: Compiled JS/CSS bundle, clean automated test run, visual and interaction confirmation.

- [ ] **Step 1: Check `reader-controls.js` to ensure theme button ring active state works with 2 themes**

Verify lines 140-155 in `resources/js/reader-controls.js` correctly handle active theme buttons when `themeButtons` has 2 elements (`sepia` and `light`), removing any ring styling when not active.

- [ ] **Step 2: Rebuild Vite production bundle**

Run: `npm run build`
Expected: SUCCESS with zero compilation errors.

- [ ] **Step 3: Run complete PHPUnit test suite**

Run: `php artisan test`
Expected: 230+ tests PASS (100% pass rate, 0 failures).

- [ ] **Step 4: Execute Playwright browser verification**

1. Launch browser to `http://127.0.0.1:8000/`.
2. Inspect `document.documentElement.className` to verify `sepia` is active.
3. Click `#header-theme-toggle` and verify theme toggles to `light`.
4. Click `#header-theme-toggle` again and verify theme toggles back to `sepia`.
5. Navigate to a chapter reader `/truyen/tinh-tam-ky/chuong-1-tra-dao`.
6. Inspect `#zen-toolbar` to confirm only 2 theme buttons exist (`sepia` and `light`), with zero `dark` button.
7. Capture screenshot evidence for verification.

- [ ] **Step 5: Update session memory ledger**

Update `.agents/session_memory.md` recording the removal of dark mode and standardization on the yellow (`sepia`) theme.

- [ ] **Step 6: Commit**

```bash
git add resources/js/reader-controls.js .agents/session_memory.md ; git commit -m "chore: complete dark mode removal, asset rebuild, and documentation"
```
