# Comprehensive Accessibility, SEO & UI Guidelines Remediation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Resolve all identified accessibility, WCAG 2.5.3 (Label in Name), heading order, image CLS, contrast ratio, SEO meta description, password manager autocomplete, and CSS transition issues across 13 views in the platform.

**Architecture:** Update Blade view templates to ensure strict accessibility (`aria-label`, `aria-hidden="true"`, `aria-modal="true"`, `overscroll-behavior: contain`), robust form-label binding (`for`/`id`), accurate internal/external asset URL handling without 403 Forbidden errors, explicit `width`/`height` attributes on all images, WCAG AA 4.5:1 color contrast, visible focus rings (`focus-visible:ring-2`), and replace unconstrained `transition-all` with targeted transition properties.

**Tech Stack:** Laravel 11, Blade Templates, Tailwind CSS v3.4, Alpine.js v3, PHPUnit 11.

**Spec:** Remediation requirements identified during platform UI/UX, SEO, and WCAG audit across author studio, legal reporting, reader views, authentication, and core layouts.

## Global Constraints

- **Accessibility & Label in Name (WCAG 2.5.3):** Any element displaying visible text within a button must have that visual text included at the start of its `aria-label` (e.g., `A- Giảm kích thước phông chữ`, `Aa Cỡ Chữ - Cài đặt giao diện đọc`).
- **Decorative Media (WCAG 1.1.1):** All decorative emojis and icons must have `aria-hidden="true"` to prevent screen reader noise.
- **Form Controls & Labels (WCAG 1.3.1):** Every form control (`<input>`, `<select>`, `<textarea>`) must be associated with an explicit `<label for="...">` matching the element's `id="..."`.
- **Target Size (WCAG 2.5.5 / Lighthouse):** Interactive touch targets must have at least 24px minimum hit height and sufficient padding.
- **Color Contrast (WCAG 1.4.3):** Text and UI indicators must achieve at least 4.5:1 contrast against their background.
- **Layout Stability (CLS):** All `<img>` tags must specify intrinsic `width` and `height` attributes to prevent layout shifts.
- **Animation Hygiene:** Replace all instances of `transition-all` with explicit transition properties (e.g., `transition-colors`, `transition-transform`, `transition-opacity`).
- **Typography Standards:** Always use typographic ellipsis `…` instead of triple ASCII dots `...`.
- **Backwards Compatibility:** All existing 159 automated tests must continue passing without regression.

## Review Focus

1. **Cover Image URL Integrity:** Ensure author novels index safely handles relative paths (`covers/...`), absolute asset paths (`/images/covers/...`), and external URLs (`http://`, `https://`) without producing broken `/storage//images/covers/...` 403 Forbidden URLs.
2. **WCAG 2.5.3 Label-in-Name Verification:** Confirm that screen readers announce the exact visible button text before additional descriptions for `A-`, `A+`, and `Aa Cỡ Chữ`.
3. **Password Manager Autocomplete:** Verify that the password change form includes an accessible hidden username field so password managers correctly identify and update account credentials.
4. **Modal Dialog Focus Containment:** Ensure the 3D chapter table of contents drawer sets `role="dialog"`, `aria-modal="true"`, and contains scroll chaining with `overscroll-behavior: contain`.
5. **SEO Default Meta Description:** Ensure that all pages extending `layouts.reader` without an explicit `$metaDescription` render a meaningful default meta description.

---

### Task 1: Author Novels Management Index (`author/novels/index.blade.php`)

**Files:**
- Modify: `resources/views/author/novels/index.blade.php:1-102`
- Test: `tests/Feature/AuthorNovelsGuidelinesTest.php`

**Interfaces:**
- Consumes: `$novel->cover_image`, `$novels` paginator.
- Produces: Safe cover URL rendering, explicit `width="64" height="88"`, decorative emojis with `aria-hidden="true"`, accessible reader link with `aria-label` & `rel="noopener noreferrer"`, and explicit transition properties.

- [ ] **Step 1: Write failing test in `tests/Feature/AuthorNovelsGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorNovelsGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_novels_index_renders_compliant_markup(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Kiếm Đạo Độc Tôn',
            'slug' => 'kiem-dao-doc-ton',
            'synopsis' => 'Một tác phẩm kiếm hiệp đặc sắc.',
            'cover_image' => '/images/covers/tien-hiep.webp',
            'status' => 'published',
            'total_words' => 10000,
            'total_chapters' => 1,
            'age_rating' => 'all',
            'copyright_type' => 'original',
        ]);

        $response = $this->actingAs($author)->get('/tac-gia/truyen');

        $response->assertStatus(200);
        $response->assertDontSee('/storage//images/covers/', false);
        $response->assertSee('src="/images/covers/tien-hiep.webp"', false);
        $response->assertSee('width="64" height="88"', false);
        $response->assertSee('aria-label="Xem trên giao diện người đọc"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
        $response->assertSee('aria-hidden="true">✍️</span>', false);
        $response->assertSee('aria-hidden="true">📖</span>', false);
        $response->assertDontSee('transition-all', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuthorNovelsGuidelinesTest`
Expected: FAIL (contains `/storage//images/covers/`, missing `width="64"`, `aria-label`, `rel="noopener noreferrer"`, or has `transition-all`).

- [ ] **Step 3: Update `resources/views/author/novels/index.blade.php`**

1. Line 19: Wrap emoji: `<span aria-hidden="true">✍️</span> Studio Tác Giả Chuyên Nghiệp`
2. Line 29: Replace `transition-all` with `transition-colors shadow-md`
3. Line 37: Update empty state emoji: `<span class="text-4xl" aria-hidden="true">📚</span>`
4. Line 42: Replace `transition-all` with `transition-colors`
5. Line 50: Replace `transition-all` with `transition-colors shadow-sm`
6. Line 54: Update cover image src logic and dimensions:
   ```html
   <img src="{{ \Illuminate\Support\Str::startsWith($novel->cover_image, ['/', 'http://', 'https://']) ? $novel->cover_image : \Illuminate\Support\Facades\Storage::url($novel->cover_image) }}" alt="{{ $novel->title }}" width="64" height="88" class="w-16 h-22 object-cover rounded-xl border border-slate-200 dark:border-slate-700 sepia:border-sepia-border shadow-sm">
   ```
7. Line 57: Wrap fallback emoji: `<span aria-hidden="true">📖</span>`
8. Line 86: Replace `transition-all` with `transition-colors`
9. Line 89-91: Update reader eye link:
   ```html
   <a href="{{ route('reader.stories.show', $novel->slug) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 sepia:bg-sepia-input hover:bg-slate-100 dark:hover:bg-slate-800 sepia:hover:bg-sepia-100 text-xs text-slate-600 dark:text-slate-400 sepia:text-sepia-muted border border-slate-200 dark:border-slate-800 sepia:border-sepia-border transition-colors" title="Xem trên giao diện người đọc" aria-label="Xem trên giao diện người đọc">
       👁️
   </a>
   ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AuthorNovelsGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/author/novels/index.blade.php tests/Feature/AuthorNovelsGuidelinesTest.php
git commit -m "fix(author): resolve cover image 403, add CLS dimensions and accessible link attributes"
```

---

### Task 2: Author Novel Create / Edit Form (`author/novels/create_or_edit.blade.php`)

**Files:**
- Modify: `resources/views/author/novels/create_or_edit.blade.php:1-116`
- Test: `tests/Feature/AuthorNovelFormGuidelinesTest.php`

**Interfaces:**
- Consumes: `$novel`, `$genres`.
- Produces: Accessible `<label for="...">` pairings for all inputs, typographic ellipsis `…` in placeholders, hidden decorative error emoji `❌`, and explicit transition properties.

- [ ] **Step 1: Write failing test in `tests/Feature/AuthorNovelFormGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorNovelFormGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_novel_form_has_labels_ids_and_typographic_ellipsis(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        Genre::create(['name' => 'Tiên Hiệp', 'slug' => 'tien-hiep']);

        $response = $this->actingAs($author)->get('/tac-gia/truyen/create');

        $response->assertStatus(200);
        $response->assertSee('<label for="title"', false);
        $response->assertSee('id="title"', false);
        $response->assertSee('<label for="synopsis"', false);
        $response->assertSee('id="synopsis"', false);
        $response->assertSee('<label for="age_rating"', false);
        $response->assertSee('id="age_rating"', false);
        $response->assertSee('<label for="copyright_type"', false);
        $response->assertSee('id="copyright_type"', false);
        $response->assertSee('<label for="cover_image"', false);
        $response->assertSee('id="cover_image"', false);
        $response->assertSee('<label for="copyright_license_notes"', false);
        $response->assertSee('id="copyright_license_notes"', false);
        $response->assertSee('placeholder="VD: Vũ Động Càn Khôn 3D…"', false);
        $response->assertSee('placeholder="Giới thiệu cốt truyện lôi cuốn, thế giới quan, nhân vật chính…"', false);
        $response->assertSee('placeholder="Ghi rõ nguồn, số hợp đồng chuyển nhượng quyền tác giả…"', false);
        $response->assertDontSee('transition-all', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuthorNovelFormGuidelinesTest`
Expected: FAIL (missing `for`/`id` bindings or typographic ellipsis).

- [ ] **Step 3: Update `resources/views/author/novels/create_or_edit.blade.php`**

1. Line 26: Update error banner emoji: `<span aria-hidden="true">❌</span>`
2. Lines 43-46:
   ```html
   <label for="title" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
       Tên tác phẩm <span class="text-rose-500">*</span>
   </label>
   <input type="text" id="title" name="title" value="{{ old('title', $novel->title) }}" required placeholder="VD: Vũ Động Càn Khôn 3D…" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
   ```
3. Lines 50-53:
   ```html
   <label for="synopsis" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
       Tóm tắt tác phẩm (Tối thiểu 20 ký tự) <span class="text-rose-500">*</span>
   </label>
   <textarea id="synopsis" name="synopsis" rows="4" required placeholder="Giới thiệu cốt truyện lôi cuốn, thế giới quan, nhân vật chính…" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 leading-relaxed transition-colors">{{ old('synopsis', $novel->synopsis) }}</textarea>
   ```
4. Lines 58-61:
   ```html
   <label for="age_rating" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
       Phân loại nhãn độ tuổi <span class="text-rose-500">*</span>
   </label>
   <select id="age_rating" name="age_rating" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
   ```
5. Lines 69-72:
   ```html
   <label for="copyright_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">
       Nguồn gốc tác quyền <span class="text-rose-500">*</span>
   </label>
   <select id="copyright_type" name="copyright_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">
   ```
6. Lines 96-97:
   ```html
   <label for="cover_image" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Ảnh bìa tác phẩm (WebP / PNG / JPG)</label>
   <input type="file" id="cover_image" name="cover_image" accept="image/*" class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 dark:file:bg-indigo-500/20 sepia:file:bg-amber-900/20 file:text-indigo-700 dark:file:text-indigo-400 sepia:file:text-amber-900 hover:file:opacity-80 transition-opacity">
   ```
7. Lines 101-103:
   ```html
   <label for="copyright_license_notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 sepia:text-sepia-text mb-1.5">Ghi chú giấy phép / Giấy ủy quyền bản quyền (nếu có)</label>
   <textarea id="copyright_license_notes" name="copyright_license_notes" rows="2" placeholder="Ghi rõ nguồn, số hợp đồng chuyển nhượng quyền tác giả…" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 sepia:bg-sepia-input border border-slate-200 dark:border-slate-800 sepia:border-sepia-border text-slate-900 dark:text-slate-100 sepia:text-sepia-text text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sepia:focus:ring-amber-800 transition-colors">{{ old('copyright_license_notes', $novel->copyright_license_notes) }}</textarea>
   ```
8. Line 109: Replace `transition-all` with `transition-colors shadow-md` on the submit button.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AuthorNovelFormGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/author/novels/create_or_edit.blade.php tests/Feature/AuthorNovelFormGuidelinesTest.php
git commit -m "fix(author): add label-for attributes, typographic ellipsis and explicit transitions to novel form"
```

---

### Task 3: Author KYC Registration Form (`author/kyc_registration.blade.php`)

**Files:**
- Modify: `resources/views/author/kyc_registration.blade.php:1-205`
- Test: `tests/Feature/AuthorKycGuidelinesTest.php`

**Interfaces:**
- Consumes: `$profile`, `$user`.
- Produces: Accessible `<label for="...">` pairings for all 10 identity and banking inputs, decorative emojis with `aria-hidden="true"`, and explicit transition classes.

- [ ] **Step 1: Write failing test in `tests/Feature/AuthorKycGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorKycGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_kyc_registration_view_has_complete_labels_ids_and_aria_hidden(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $response = $this->actingAs($user)->get('/tac-gia/dang-ky-dinh-danh');

        $response->assertStatus(200);
        $response->assertSee('<label for="pen_name"', false);
        $response->assertSee('id="pen_name"', false);
        $response->assertSee('<label for="legal_full_name"', false);
        $response->assertSee('id="legal_full_name"', false);
        $response->assertSee('<label for="identity_number"', false);
        $response->assertSee('id="identity_number"', false);
        $response->assertSee('<label for="phone"', false);
        $response->assertSee('id="phone"', false);
        $response->assertSee('<label for="identity_issued_date"', false);
        $response->assertSee('id="identity_issued_date"', false);
        $response->assertSee('<label for="identity_front_image"', false);
        $response->assertSee('id="identity_front_image"', false);
        $response->assertSee('<label for="identity_back_image"', false);
        $response->assertSee('id="identity_back_image"', false);
        $response->assertSee('<label for="tax_code"', false);
        $response->assertSee('id="tax_code"', false);
        $response->assertSee('<label for="bank_account_number"', false);
        $response->assertSee('id="bank_account_number"', false);
        $response->assertSee('<label for="bank_name"', false);
        $response->assertSee('id="bank_name"', false);
        $response->assertSee('aria-hidden="true">🔒</span>', false);
        $response->assertDontSee('transition-all', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuthorKycGuidelinesTest`
Expected: FAIL (missing label `for` / input `id` attributes).

- [ ] **Step 3: Update `resources/views/author/kyc_registration.blade.php`**

1. Lines 45, 62, 66, 70: Add `aria-hidden="true"` to emojis `❌`, `⏳`, `🛡️`, `❌`.
2. Lines 91-94: Add `for="pen_name"` to label and `id="pen_name"` to input.
3. Lines 97-100: Add `for="legal_full_name"` to label and `id="legal_full_name"` to input.
4. Lines 106-109: Add `for="identity_number"` to label and `id="identity_number"` to input.
5. Lines 112-115: Add `for="phone"` to label and `id="phone"` to input.
6. Lines 118-121: Add `for="identity_issued_date"` to label and `id="identity_issued_date"` to input.
7. Lines 137-140: Add `for="identity_front_image"` to label and `id="identity_front_image"` to file input; change `transition-all` to `transition-opacity`.
8. Lines 143-146: Add `for="identity_back_image"` to label and `id="identity_back_image"` to file input; change `transition-all` to `transition-opacity`.
9. Lines 162-163: Add `for="tax_code"` to label and `id="tax_code"` to input.
10. Lines 166-167: Add `for="bank_account_number"` to label and `id="bank_account_number"` to input.
11. Lines 170-171: Add `for="bank_name"` to label and `id="bank_name"` to input.
12. Line 195: Update security note emoji: `<span aria-hidden="true">🔒</span>`.
13. Line 197: Replace `transition-all` on submit button with `transition-colors shadow-md`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AuthorKycGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/author/kyc_registration.blade.php tests/Feature/AuthorKycGuidelinesTest.php
git commit -m "fix(kyc): add full label-id bindings, decorative aria-hidden and explicit transitions"
```

---

### Task 4: Legal Public Report Form (`legal/copyright_report_form.blade.php` & `legal/public_report.blade.php`)

**Files:**
- Modify: `resources/views/legal/copyright_report_form.blade.php`
- Create: `resources/views/legal/public_report.blade.php`
- Test: `tests/Feature/LegalReportGuidelinesTest.php`

**Interfaces:**
- Consumes: `$novel` (optional).
- Produces: Accessible report form with `for="reportable_type"`, `for="reportable_id"`, `for="reason"`, and typographic ellipsis `…`.

- [ ] **Step 1: Write failing test in `tests/Feature/LegalReportGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalReportGuidelinesTest extends TestCase
{
    public function test_copyright_report_form_has_labels_and_typographic_ellipsis(): void
    {
        $response = $this->get('/khieu-nai-ban-quyen');

        $response->assertStatus(200);
        $response->assertSee('<label for="reportable_type"', false);
        $response->assertSee('id="reportable_type"', false);
        $response->assertSee('<label for="reportable_id"', false);
        $response->assertSee('id="reportable_id"', false);
        $response->assertSee('<label for="reason"', false);
        $response->assertSee('id="reason"', false);
        $response->assertSee('placeholder="Nêu rõ các đoạn trích, chương hồi, hoặc tài liệu chứng minh quyền tác giả…"', false);
    }

    public function test_public_report_alias_view_renders_correctly(): void
    {
        $rendered = view('legal.public_report', ['novel' => null])->render();

        $this->assertStringContainsString('id="reportable_type"', $rendered);
        $this->assertStringContainsString('id="reason"', $rendered);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LegalReportGuidelinesTest`
Expected: FAIL (view `legal.public_report` not found or missing `id="reportable_type"`).

- [ ] **Step 3: Update `copyright_report_form.blade.php` and create `public_report.blade.php`**

1. In `resources/views/legal/copyright_report_form.blade.php`:
   - Line 46: `<label for="reportable_type" class="...">Loại đối tượng:</label>`
   - Line 47: `<select id="reportable_type" name="reportable_type" class="...">`
   - Line 54: `<label for="reportable_id" class="...">Mã định danh (ID tác phẩm / chương):</label>`
   - Line 55: `<input type="number" id="reportable_id" name="reportable_id" ...>`
   - Line 81: `<label for="reason" class="...">Phân loại vi phạm:</label>`
   - Line 82: `<select id="reason" name="violation_category" class="...">`
   - Line 93: `<textarea name="description" ... placeholder="Nêu rõ các đoạn trích, chương hồi, hoặc tài liệu chứng minh quyền tác giả…">`
   - Line 106: Replace `transition-all` with `transition-colors shadow-lg`
2. In `resources/views/legal/public_report.blade.php`:
   ```html
   @include('legal.copyright_report_form')
   ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LegalReportGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/legal/copyright_report_form.blade.php resources/views/legal/public_report.blade.php tests/Feature/LegalReportGuidelinesTest.php
git commit -m "fix(legal): add label-id associations, typographic ellipsis and public report alias"
```

---

### Task 5: Reader Layout SEO & Header Focus Indicators (`layouts/reader.blade.php` & `header.blade.php`)

**Files:**
- Modify: `resources/views/layouts/reader.blade.php:1-30`
- Modify: `resources/views/layouts/partials/header.blade.php:1-60`
- Test: `tests/Feature/LayoutGuidelinesTest.php`

**Interfaces:**
- Consumes: `$metaDescription` (optional), brand logo, search input kbd badge.
- Produces: 100% SEO fallback meta description, visible focus ring on brand logo (`focus-visible:ring-2`), and accessible high-contrast `⌘K` badge.

- [ ] **Step 1: Write failing test in `tests/Feature/LayoutGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutGuidelinesTest extends TestCase
{
    public function test_reader_layout_renders_default_meta_description_when_not_passed(): void
    {
        $response = $this->get('/settings/security');

        $response->assertStatus(302); // Redirects to login
        $loginResponse = $this->get('/login');
        $loginResponse->assertSee('<meta name="description" content="Novel Engine -', false);
    }

    public function test_header_logo_has_visible_focus_ring_and_kbd_has_high_contrast(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('group focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl', false);
        $response->assertSee('text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LayoutGuidelinesTest`
Expected: FAIL (missing default meta description or logo focus visible ring).

- [ ] **Step 3: Update `layouts/reader.blade.php` and `header.blade.php`**

1. In `resources/views/layouts/reader.blade.php`:
   - Replace lines 11-13 with:
     ```html
     <meta name="description" content="{{ $metaDescription ?? 'Novel Engine - Nền tảng đọc tiểu thuyết trực tuyến và trải nghiệm không gian 3D tương tác sống động.' }}">
     ```
2. In `resources/views/layouts/partials/header.blade.php`:
   - Line 5:
     ```html
     <a href="/" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl">
     ```
   - Line 54:
     ```html
     <kbd class="hidden md:inline-flex absolute right-2.5 top-2.5 px-1.5 py-0.5 text-[10px] font-mono font-medium text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 rounded border border-slate-300 dark:border-slate-600">
         ⌘K
     </kbd>
     ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LayoutGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/layouts/reader.blade.php resources/views/layouts/partials/header.blade.php tests/Feature/LayoutGuidelinesTest.php
git commit -m "fix(layout): add default meta description for SEO 100, logo focus ring and kbd contrast"
```

---

### Task 6: Novel Detail Heading Order & Zen Toolbar WCAG 2.5.3 (`show.blade.php` & `zen_toolbar.blade.php`)

**Files:**
- Modify: `resources/views/reader/novels/show.blade.php:80-90,160-170`
- Modify: `resources/views/reader/chapters/partials/zen_toolbar.blade.php:10-20`
- Test: `tests/Feature/ZenToolbarGuidelinesTest.php`
- Test: `tests/Feature/NovelDetailAndZenGuidelinesTest.php`

**Interfaces:**
- Consumes: Novel details page, zen floating toolbar.
- Produces: Correct `<h1> -> <h2>` heading hierarchy, `aria-hidden="true"` on `🗡️`, and WCAG 2.5.3 compliant labels (`A- Giảm kích thước phông chữ`, `A+ Tăng kích thước phông chữ`).

- [ ] **Step 1: Write failing test in `tests/Feature/NovelDetailAndZenGuidelinesTest.php` and update `ZenToolbarGuidelinesTest.php`**

In `tests/Feature/NovelDetailAndZenGuidelinesTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelDetailAndZenGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_show_has_correct_heading_order_and_hidden_artifact_icon(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Long Thần Quyết',
            'slug' => 'long-than-quyet',
            'synopsis' => 'Truyện dài tu chân huyền ảo.',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);

        $novel->assets3d()->create([
            'name' => 'Thần Binh Trảm Long',
            'file_path' => 'assets/sword.glb',
            'asset_type' => 'weapon',
        ]);

        $response = $this->get(route('reader.novels.show', $novel->slug));

        $response->assertStatus(200);
        $response->assertSee('<h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h2>', false);
        $response->assertDontSee('<h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h3>', false);
        $response->assertSee('aria-hidden="true">🗡️</span>', false);
    }
}
```

In `tests/Feature/ZenToolbarGuidelinesTest.php`:
Update `$this->blade(...)` to `view('reader.chapters.partials.zen_toolbar')->render()` to fix Windows `tempnam()` notice and verify `aria-label="A- Giảm kích thước phông chữ"` and `aria-label="A+ Tăng kích thước phông chữ"`.

- [ ] **Step 2: Run tests to verify failure**

Run: `php artisan test --filter=NovelDetailAndZenGuidelinesTest`
Expected: FAIL.

- [ ] **Step 3: Update `resources/views/reader/novels/show.blade.php` and `zen_toolbar.blade.php`**

1. In `resources/views/reader/novels/show.blade.php`:
   - Line 82: Change `<h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h3>` to:
     ```html
     <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tóm Tắt Tác Phẩm</h2>
     ```
   - Line 164: Change `🗡️` to:
     ```html
     <span aria-hidden="true">🗡️</span>
     ```
2. In `resources/views/reader/chapters/partials/zen_toolbar.blade.php`:
   - Line 12: Change `aria-label="Giảm kích thước phông chữ"` to:
     ```html
     <button type="button" id="btn-font-decrease" aria-label="A- Giảm kích thước phông chữ" class="px-2.5 py-1 text-slate-300 hover:text-white font-bold transition touch-manipulation rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">A-</button>
     ```
   - Line 14: Change `aria-label="Tăng kích thước phông chữ"` to:
     ```html
     <button type="button" id="btn-font-increase" aria-label="A+ Tăng kích thước phông chữ" class="px-2.5 py-1 text-slate-300 hover:text-white font-bold transition touch-manipulation rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">A+</button>
     ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=NovelDetailAndZenGuidelinesTest`
Run: `php artisan test --filter=ZenToolbarGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/reader/novels/show.blade.php resources/views/reader/chapters/partials/zen_toolbar.blade.php tests/Feature/NovelDetailAndZenGuidelinesTest.php tests/Feature/ZenToolbarGuidelinesTest.php
git commit -m "fix(reader): correct heading level order, hide weapon emoji and satisfy WCAG 2.5.3 label-in-name"
```

---

### Task 7: 3D Reader Modal Drawer & User Security Password Management (`read_3d.blade.php` & `security.blade.php`)

**Files:**
- Modify: `resources/views/reader/chapters/read_3d.blade.php:90-135`
- Modify: `resources/views/reader/user/security.blade.php:30-45`
- Test: `tests/Feature/Reader3dAndSecurityGuidelinesTest.php`

**Interfaces:**
- Consumes: `#toc-drawer`, font settings button, password change form.
- Produces: WCAG 2.5.3 compliant `aria-label="Aa Cỡ Chữ - Cài đặt giao diện đọc"`, decorative emojis with `aria-hidden="true"`, `role="dialog" aria-modal="true" overscroll-contain` on `#toc-drawer`, and accessible hidden username field on security form.

- [ ] **Step 1: Write failing test in `tests/Feature/Reader3dAndSecurityGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Reader3dAndSecurityGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_3d_has_accessible_buttons_and_modal_drawer(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Đại Lục Khải Huyền',
            'slug' => 'dai-luc-khai-huyen',
            'synopsis' => 'Thế giới mở 3D',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Khởi Đầu Mới',
            'slug' => 'khoi-dau-moi',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('reader.chapters.read', [$novel->slug, 1]));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Aa Cỡ Chữ - Cài đặt giao diện đọc"', false);
        $response->assertSee('aria-hidden="true">📑</span>', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('overscroll-contain', false);
    }

    public function test_security_form_includes_hidden_username_field(): void
    {
        $user = User::factory()->create(['status' => 'active', 'email' => 'reader@novel3d.vn']);

        $response = $this->actingAs($user)->get('/settings/security');

        $response->assertStatus(200);
        $response->assertSee('name="username"', false);
        $response->assertSee('autocomplete="username"', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Reader3dAndSecurityGuidelinesTest`
Expected: FAIL.

- [ ] **Step 3: Update `read_3d.blade.php` and `security.blade.php`**

1. In `resources/views/reader/chapters/read_3d.blade.php`:
   - Line 96: Change `<span>📑</span>` to `<span aria-hidden="true">📑</span>`.
   - Line 105: Change `aria-label="Cài đặt cỡ chữ và giao diện đọc"` to:
     ```html
     aria-label="Aa Cỡ Chữ - Cài đặt giao diện đọc"
     ```
   - Line 114: Change `<span>🔄</span>` to `<span aria-hidden="true">🔄</span>`.
   - Line 123: Update `#toc-drawer` container:
     ```html
     <div id="toc-drawer" role="dialog" aria-modal="true" aria-label="Mục Lục Tác Phẩm" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex justify-start overscroll-contain">
     ```
2. In `resources/views/reader/user/security.blade.php`:
   - Add immediately inside `<form action="/settings/security" method="POST" ...>`:
     ```html
     <input type="text" name="username" value="{{ auth()->user()?->email ?? '' }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">
     ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=Reader3dAndSecurityGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/reader/chapters/read_3d.blade.php resources/views/reader/user/security.blade.php tests/Feature/Reader3dAndSecurityGuidelinesTest.php
git commit -m "fix(a11y): update Aa label-in-name, drawer overscroll containment and password manager username input"
```

---

### Task 8: Reader Home, User Library & Auth Login Improvements (`home.blade.php`, `library.blade.php`, `login.blade.php`)

**Files:**
- Modify: `resources/views/reader/home.blade.php:135-215`
- Modify: `resources/views/reader/user/library.blade.php:35-70`
- Modify: `resources/views/auth/login.blade.php:65-95`
- Test: `tests/Feature/HomeAndAuthGuidelinesTest.php`

**Interfaces:**
- Consumes: Continue reading history, latest chapters list, bookmark quick removal, login form.
- Produces: Explicit image dimensions for CLS prevention (`56x80`, `48x64`), touch targets >= 24px, 4.5:1 text contrast, accessible password reveal button focus ring, and clean CSS transitions.

- [ ] **Step 1: Write failing test in `tests/Feature/HomeAndAuthGuidelinesTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAndAuthGuidelinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_has_image_dimensions_and_high_contrast_chapter_feed(): void
    {
        $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);
        $novel = Novel::create([
            'author_id' => $author->id,
            'title' => 'Chúa Tể Chi Vương',
            'slug' => 'chua-te-chi-vuong',
            'synopsis' => 'Tu luyện huyền ảo.',
            'status' => 'published',
            'total_words' => 5000,
            'total_chapters' => 1,
        ]);
        $chapter = Chapter::create([
            'novel_id' => $novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Thần Đồng',
            'slug' => 'chuong-1-than-dong',
            'content' => '<p>Chương một</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        ReadingHistory::create([
            'user_id' => $user->id,
            'novel_id' => $novel->id,
            'last_chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('width="56" height="80"', false);
        $response->assertSee('width="48" height="64"', false);
        $response->assertSee('text-slate-500 dark:text-slate-400 sepia:text-sepia-muted', false);
    }

    public function test_login_and_library_views_have_explicit_transitions_and_focus_ring(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $libResponse = $this->actingAs($user)->get('/library');
        $libResponse->assertStatus(200);
        $libResponse->assertSee('aria-hidden="true">📚</span>', false);
        $libResponse->assertDontSee('transition-all duration-200', false);

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('focus-visible:ring-2 focus-visible:ring-indigo-500', false);
        $loginResponse->assertSee('transition-transform transition-colors', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HomeAndAuthGuidelinesTest`
Expected: FAIL.

- [ ] **Step 3: Update `home.blade.php`, `library.blade.php`, and `login.blade.php`**

1. In `resources/views/reader/home.blade.php`:
   - Line 141: Add `width="56" height="80"`:
     ```html
     <img src="{{ $history->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=150&q=80' }}" alt="{{ $history->novel->title }}" width="56" height="80" class="w-14 h-20 object-cover rounded-xl shrink-0">
     ```
   - Line 195: Add `width="48" height="64"`:
     ```html
     <img src="{{ $chapter->novel->cover_image ?? 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&w=100&q=80' }}" alt="{{ $chapter->novel->title }}" width="48" height="64" class="w-12 h-16 rounded-lg object-cover shrink-0">
     ```
   - Lines 197-202: Add touch target padding `py-0.5 min-h-[24px]` and `flex items-center` to stacked links:
     ```html
     <a href="/stories/{{ $chapter->novel->slug }}" class="font-bold text-xs truncate block py-0.5 min-h-[24px] flex items-center text-slate-900 dark:text-white sepia:text-sepia-text hover:text-indigo-600 transition-colors">
         {{ $chapter->novel->title }}
     </a>
     <a href="/stories/{{ $chapter->novel->slug }}/read/{{ $chapter->chapter_number }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold truncate block py-0.5 min-h-[24px] flex items-center hover:underline">
         {{ $chapter->title }}
     </a>
     ```
   - Line 203: Update text color for contrast >= 4.5:1:
     ```html
     <span class="text-[10px] text-slate-500 dark:text-slate-400 sepia:text-sepia-muted block">
         {{ $chapter->novel->genres->first()->name ?? 'Truyện' }} • {{ $chapter->published_at?->diffForHumans() ?? 'Vừa xong' }}
     </span>
     ```
2. In `resources/views/reader/user/library.blade.php`:
   - Line 39: Replace `transition-all duration-200` with `transition-opacity transition-colors duration-200`.
   - Line 65: Update empty state emoji: `<span class="text-4xl block" aria-hidden="true">📚</span>`.
3. In `resources/views/auth/login.blade.php`:
   - Line 70: Update password visibility button to include focus ring:
     ```html
     <button
         type="button"
         onclick="togglePasswordVisibility('password', this)"
         class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg p-0.5"
         aria-label="Ẩn hiện mật khẩu"
     >
         👁️
     </button>
     ```
   - Line 87: Replace `transition-all transform hover:-translate-y-0.5` with:
     ```html
     transition-transform transition-colors transform hover:-translate-y-0.5
     ```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=HomeAndAuthGuidelinesTest`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/reader/home.blade.php resources/views/reader/user/library.blade.php resources/views/auth/login.blade.php tests/Feature/HomeAndAuthGuidelinesTest.php
git commit -m "fix(ui): add image dimensions for CLS, expand touch targets, fix contrast and focus ring"
```

---

### Task 9: Comprehensive Regression Test & Build Verification

**Files:**
- Modify: (None, verification only)
- Test: Full PHPUnit test suite & Vite production build

**Interfaces:**
- Consumes: All 13 updated Blade templates, CSS rules, and feature tests.
- Produces: 100% green automated test suite (>= 165 tests) and validated production bundle.

- [ ] **Step 1: Run complete PHPUnit test suite**

Run: `php artisan test`
Expected: 100% PASS across all feature and unit tests with 0 failures.

- [ ] **Step 2: Run Vite production asset build**

Run: `npm run build`
Expected: Build succeeds with 0 errors.

- [ ] **Step 3: Final release commit**

```bash
git add -A
git commit -m "chore: complete comprehensive accessibility, SEO and UI guidelines remediation"
```
