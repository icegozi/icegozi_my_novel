# Default Warm Sepia Theme Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Configure the warm parchment / sepia reading aesthetic (cream background `#f5efe6`, warm surfaces `#fdfbf7`, dark brown typography `#2d2216`, and warm amber accents) as the platform-wide default across all pages for guests and initial sessions.

**Architecture:** Register `sepia` as a first-class Tailwind CSS variant (`.sepia &`), align `:root` design tokens and SSR HTML layout classes to default to `sepia`, configure `theme-manager.js` to return `'sepia'` when no preference is saved in `localStorage`, and ensure all 20 views render with high-contrast warm paper styling.

**Tech Stack:** Laravel 11, Tailwind CSS v3.4 (`tailwindcss/plugin`), Blade Templates, Vite v5, Vanilla JS `theme-manager.js`, PHPUnit.

**Spec:** User instruction — "mặc định màu này hết cho các page giúp tôi" with screenshot of the 404 page rendered in the warm paper sepia theme.

## Global Constraints

- Retain existing theme toggle functionality (users can still switch to dark or light mode if desired).
- Preserve all 124 passing PHPUnit tests; update test expectations that checked for default `dark` class to `sepia`.
- Support Vietnamese diacritics across all typography.
- Comply with WCAG 2.1 AA contrast ratios for all text in sepia mode (dark brown `#2d2216` on cream `#f5efe6` exceeds 10:1 ratio).
- Zero layout shift and zero flash of wrong theme (FOUC) on initial load.

## Review Focus

1. **Initial Page Load Without LocalStorage:** First-time visitors must immediately see the sepia palette on the initial HTML render without any flash of dark or white.
2. **SSR `<meta name="theme-color">` alignment:** The browser address bar and PWA status bar must receive `#f5efe6` by default.
3. **Tailwind Variant Generation:** Verify that `sepia:` utility classes (`sepia:bg-sepia-50`, `sepia:text-sepia-text`, `sepia:border-sepia-border`) are generated in the CSS bundle by adding the `sepia` variant plugin.
4. **Consistency across all pages:** Verify Home, Catalog, Novel Detail, Chapter Reader, Search, Auth, and User Area have cohesive warm paper background and surface styling.
5. **Theme Switcher Cycle:** Toggling themes must cycle cleanly (`sepia` -> `dark` -> `light` -> `sepia`).

---

### Task 1: Register Tailwind Variant and Configure Base Sepia Tokens

**Files:**
- Modify: `tailwind.config.js`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/LayoutAndThemeTest.php`

**Interfaces:**
- Consumes: `tailwindcss/plugin`.
- Produces: First-class `.sepia &` Tailwind variant, and `:root` CSS variables defaulting to warm parchment values.

- [x] **Step 1: Write failing test in `LayoutAndThemeTest.php` asserting default sepia layout attributes**

```php
// tests/Feature/LayoutAndThemeTest.php
public function test_reader_layout_defaults_to_sepia_theme(): void
{
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('class="sepia"', false);
    $response->assertSee('content="#f5efe6"', false);
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter LayoutAndThemeTest`
Expected: FAIL (currently sees `class="dark"` and `content="#0b0f19"`).

- [x] **Step 3: Update `tailwind.config.js` and `resources/css/app.css`**

1. In `tailwind.config.js`:
   Import `plugin` from `'tailwindcss/plugin'`.
   Add plugin: `addVariant('sepia', '.sepia &')`.
2. In `resources/css/app.css`:
   Configure `:root` CSS variables to warm sepia defaults:
   `--color-bg: #f5efe6;`
   `--color-surface: #fdfbf7;`
   `--color-surface-muted: #eae1d2;`
   `--color-border: #e2d5c2;`
   `--color-text: #2d2216;`
   `--color-text-muted: #725e49;`
   `--color-primary: #92400e;`
   `--color-primary-hover: #b45309;`
   `--color-accent: #d97706;`

- [x] **Step 4: Update `resources/views/layouts/reader.blade.php`**

1. Set `<html lang="vi" class="sepia">`.
2. Set `<meta name="theme-color" content="#f5efe6">`.
3. Set inline blocking script fallback to `'sepia'`:
   `var theme = localStorage.getItem('novel_reader_theme') || 'sepia';`

- [x] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter LayoutAndThemeTest`
Expected: PASS.

- [x] **Step 6: Commit**

```bash
git add tailwind.config.js resources/css/app.css resources/views/layouts/reader.blade.php tests/Feature/LayoutAndThemeTest.php
git commit -m "feat(theme): configure warm sepia as default platform theme"
```

---

### Task 2: Configure Client-Side `theme-manager.js` and Cycle Flow

**Files:**
- Modify: `resources/js/theme-manager.js`
- Modify: `resources/views/reader/user/settings.blade.php`

**Interfaces:**
- Consumes: Browser `localStorage` and `CustomEvent('themechange')`.
- Produces: Default fallback to `'sepia'`, and cycle mapping `sepia` -> `dark` -> `light` -> `sepia`.

- [x] **Step 1: Update `resources/js/theme-manager.js`**

1. In `getPreferredTheme()`:
   If no saved theme in `localStorage`, return `'sepia'`.
2. In `toggleReaderTheme()`:
   Update `nextMap = { sepia: 'dark', dark: 'light', light: 'sepia' }`.
3. Ensure `applyTheme('sepia')` sets meta theme-color `#f5efe6`.

- [x] **Step 2: Update default radio selection in `settings.blade.php`**

Ensure that when user has no stored preference, the default radio checked is `sepia`:
`{{ ($preferences['theme'] ?? 'sepia') === 'sepia' ? 'checked' : '' }}`.

- [x] **Step 3: Rebuild Vite assets**

Run: `npm run build`
Expected: Asset bundling succeeds and compiled CSS contains `.sepia` variant definitions.

- [x] **Step 4: Commit**

```bash
git add resources/js/theme-manager.js resources/views/reader/user/settings.blade.php
git commit -m "feat(theme): update theme-manager to default to sepia with smooth rotation"
```

---

### Task 3: Comprehensive View Polish for Sepia Harmony

**Files:**
- Modify: `resources/views/layouts/partials/header.blade.php`
- Modify: `resources/views/layouts/partials/footer.blade.php`
- Modify: `resources/views/reader/home.blade.php`
- Modify: `resources/views/reader/catalog/latest.blade.php`
- Modify: `resources/views/reader/catalog/popular.blade.php`
- Modify: `resources/views/reader/genres/index.blade.php`
- Modify: `resources/views/reader/genres/show.blade.php`
- Modify: `resources/views/reader/search.blade.php`
- Modify: `resources/views/reader/novels/show.blade.php`
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/views/auth/register.blade.php`

**Interfaces:**
- Consumes: Tailwind `sepia:` and CSS variables.
- Produces: Polished, editorial warm paper appearance with zero clash or dark patches.

- [x] **Step 1: Check and refine all header/footer and discovery view elements**

Ensure badges, navigation pills, book card containers, search inputs, and buttons use warm sepia backgrounds and borders with deep roasted coffee text when `sepia` is active.

- [x] **Step 2: Run all PHPUnit tests**

Run: `php artisan test`
Expected: 124 passed (0 failures).

- [x] **Step 3: Rebuild Vite assets**

Run: `npm run build`
Expected: Clean build.

- [x] **Step 4: Commit**

```bash
git add resources/views/
git commit -m "style(views): refine sepia styling across all pages for cohesive reading aesthetic"
```
