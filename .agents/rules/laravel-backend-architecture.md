# Workspace Rule: Laravel 11 Backend Architecture

> **Scope:** Governs models, migrations, controllers, middleware, business logic, and database schemas.

---

## 1. Database Schema & Migration Invariants

1. **User Role Constraint:**
   - The `users` table has an explicit SQLite check constraint:
     ```sql
     CHECK (role IN ('reader', 'author', 'moderator', 'admin'))
     ```
   - You **MUST NOT** use `'role' => 'user'` in test factories, seeders, or feature code. Use `'role' => 'reader'` for normal users or `'role' => 'author'` for creator accounts.
2. **Author Profile & Verification:**
   - Author studio routes (`/tac-gia/*`) enforce `EnsureAuthorVerified` middleware.
   - Any test exercising author management **MUST** establish an associated `AuthorProfile` with `'verification_status' => 'verified'`:
     ```php
     $author = User::factory()->create(['role' => 'author', 'status' => 'active']);
     $author->authorProfile()->create([
         'pen_name' => 'Bút Danh Mẫu',
         'verification_status' => 'verified',
     ]);
     ```
3. **3D Asset Schema (`novel_3d_assets`):**
   - The file URL attribute **MUST** be named `model_file_url` (not `file_path`).
   - JSON casting: `camera_init_position` is cast to `'array'`.
   - Decimal casting: `scale_factor` is cast to `'decimal:2'`.

---

## 2. Media Asset & Storage URL Handling

1. **Cover Image URLs:**
   - When resolving cover URLs in Blade or Eloquent accessors, you **MUST** handle both external URLs (e.g. Unsplash) and local storage paths without generating 403 Forbidden:
     ```php
     \Illuminate\Support\Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url)
     ```
   - You **SHOULD NOT** call `Storage::url(...)` on raw relative paths starting with `/storage/`, as this doubles the path prefix (`/storage//images/...`) and triggers 403 errors.

---

## 3. Legal Compliance Architecture

1. **Decree 17/2023/NĐ-CP (Copyright & DMCA):**
   - Copyright reports route: `/bao-cao-vi-pham` (`legal.reports.create`).
   - Report form view: `resources/views/legal/copyright_report_form.blade.php`.
   - Backward-compatible alias view: `resources/views/legal/public_report.blade.php`.
2. **Decree 13/2023/NĐ-CP (Personal Data Protection) & Decree 147/2024/NĐ-CP (Cybersecurity):**
   - User e-KYC records in `author_profiles` (CCCD/CMND, phone, tax code, banking info) **MUST** remain protected and never exposed to public endpoints.
