# Active Session Memory Bank

> **Purpose:** This document is the single source of truth for persistent architectural state, domain rules, hard-earned engineering lessons, and session evolution across all AI chat sessions.

---

## 1. Project Architecture Snapshot

### 1.1 Core Modules & Subsystems
- **Novel Catalog & Reading Experience:** Full-featured online novel/comic platform with dual-theme reading system (Màu Vàng parchment / sepia default and Sáng / light mode), customizable typography, and reading progress synchronization.
- **Interactive 3D WebGL Exhibition & Stage:** Three.js-powered 3D stage embedded in chapter reader, rendering GLB models linked directly to story artifacts, weapons, and characters (`novel_3d_assets` and `chapter_3d_projections`).
- **Author Studio & Identity Verification:** Creator dashboard for publishing works, managing chapters, and undergoing e-KYC legal identity verification (`author_profiles`).
- **Legal Compliance & DMCA Notice System:** Full adherence to Vietnamese digital publishing laws:
  - Nghị định 17/2023/NĐ-CP (Bản quyền tác giả & cơ chế thông báo gỡ bỏ nội dung vi phạm DMCA).
  - Nghị định 13/2023/NĐ-CP (Bảo vệ dữ liệu cá nhân & mã hóa hồ sơ e-KYC).
  - Nghị định 147/2024/NĐ-CP (Quản lý, cung cấp, sử dụng dịch vụ Internet và thông tin trên mạng).
- **Admin & Moderation Desk:** Management center for approving author e-KYC submissions, moderating reported content, and managing platform taxonomies.

### 1.2 Technology Stack
- **Backend Framework:** Laravel 11.31 (PHP 8.2+).
- **Frontend Engine:** Blade Templates, Tailwind CSS v3, Alpine.js v3.14, Three.js 0.160.
- **Build Tool:** Vite 5.4.
- **Database:** SQLite (local dev & testing), PostgreSQL 16 (production container).
- **Test Suite:** PHPUnit 11 with 170+ automated feature and unit tests.

---

## 2. Core Invariants & Security Rules

1. **User Role Whitelist:**
   - Table `users` has a strict SQLite check constraint: `role IN ('reader', 'author', 'moderator', 'admin')`.
   - Never use `'role' => 'user'`.
2. **Author Studio Access:**
   - All `/tac-gia/*` routes enforce `EnsureAuthorVerified`.
   - Author profile requires `verification_status = 'verified'` to pass authorization.
3. **Storage Security & Asset URLs:**
   - Use `Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url)` to guarantee internal and external covers resolve cleanly without 403 Forbidden errors.
4. **WCAG 2.1/2.2 AA Conformance:**
   - Every interactive control must have an accessible name containing its visible text (WCAG 2.5.3).
   - Headings must follow strict order (`h1` -> `h2` -> `h3`).
   - Every `<img>` tag must include explicit `width` and `height` integer attributes to eliminate Cumulative Layout Shift (CLS).
   - Zero `transition-all` policy: always specify target properties (e.g. `transition-colors`, `transition-opacity`).
5. **Mandatory MCP Sequential Thinking:**
   - For every task, Gemini must invoke `sequentialthinking` (`sequential-thinking` MCP tool) before code edits and throughout execution to map edge cases and constraints.
6. **Mandatory Playwright & Chrome Browser Pre-Handoff Check:**
   - Prior to concluding any task or handoff, Gemini must verify the live application using `playwright` and/or `chrome-devtools-mcp` tools, test interactions, confirm zero JS console errors, and capture screenshot evidence.

---

## 3. Lessons Learned & Known Gotchas

| # | Topic / Area | Problem / Trap | Root Cause | Verified Solution |
|---|---|---|---|---|
| 1 | **PowerShell Chaining** | Command `cmd1 && cmd2` fails with syntax error in Windows PowerShell. | `&&` is not universally supported in PowerShell 5/7 tool calls. | Use semicolon `;` separator: `cmd1 ; cmd2`. |
| 2 | **SQLite Role Constraint** | Factory creates user with `'role' => 'user'`, causing `QueryException: CHECK constraint failed`. | DB migration defines `CHECK(role in ('reader', 'author', 'moderator', 'admin'))`. | Always use `'role' => 'reader'` for regular users, or `'role' => 'author'`. |
| 3 | **Author Studio Tests** | Test user receives 403 Forbidden when accessing `/tac-gia/truyen`. | `EnsureAuthorVerified` middleware checks for an approved `authorProfile`. | Create associated `AuthorProfile` with `'verification_status' => 'verified'` on author user. |
| 4 | **Cover Images 403** | Image URLs starting with `/storage/` generate 403 errors when passed to `Storage::url()`. | `Storage::url()` doubles the prefix resulting in `/storage//images/...`. | Use `Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url)`. |
| 5 | **WCAG 2.5.3 Label-in-Name** | Automated accessibility audits flag font resize buttons (`A-`, `A+`, `Aa`). | Accessible name did not contain the visible text on the button face. | Prefix aria-label with visible text: `aria-label="A- Giảm kích thước phông chữ"`. |
| 6 | **Windows View Testing** | Test `$this->blade("@include('...')")` throws PHP warning on Windows. | Blade compiler calls `tempnam()` which may fail on restricted Windows temp paths. | Render view directly using `view('path.to.view')->render()`. |
| 7 | **Password Managers** | Browser console warns "Password field is not contained in a form with username". | Password change forms only included password inputs. | Add hidden username field: `<input type="text" name="username" value="{{ auth()->user()?->email }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">`. |
| 8 | **Modal Drawer Trapping** | Screen readers don't recognize slide-out table of contents as a modal dialog. | Missing ARIA dialog semantics on drawer container. | Add `role="dialog" aria-modal="true" aria-label="Mục Lục Tác Phẩm" class="... overscroll-contain"`. |
| 9 | **Touch Target Sizing** | Inline chapter links flag touch target warnings (< 24px). | Hyperlinks nested inside tight flex columns had default inline line-height. | Add `py-0.5 min-h-[24px] flex items-center` to chapter links. |
| 10 | **Color Contrast (Sepia/Dark)** | Muted metadata text (`text-slate-400`) fails 4.5:1 contrast against light/sepia backgrounds. | Insufficient luminosity difference. | Upgrade to `text-slate-500 dark:text-slate-400 sepia:text-sepia-muted`. |
| 11 | **Password Reset Token Verification** | Password reset accepted arbitrary tokens without checking database record. | Missing verification against hashed tokens in `password_reset_tokens` table. | Query `password_reset_tokens` by email and verify with `Hash::check($token, $record->token)`. |
| 12 | **Bulk Chapter Unique Collision** | Splitting chapters into existing chapter numbers caused 500 SQLSTATE[23000] crash. | SQLite UNIQUE index on `(novel_id, chapter_number)`. | Detect collisions upfront and offset incoming chapter numbers starting from `(max_existing + 1.0)`. |
| 13 | **Single-Pass Keyword Highlighting** | Highlighting multiple keywords in loops corrupted `<mark class="...">` attributes. | Later replacement passes matched class names (e.g. `bg-rose-500/20`). | Use unified regex alternation `/(word1|word2|...)/ui` in a single pass with preg_replace_callback. |
| 14 | **Mailable Queuing Architecture** | Transactional emails dispatched synchronously, causing HTTP latency spikes. | Missing `ShouldQueue` contract on Mailable classes. | Implement `Illuminate\Contracts\Queue\ShouldQueue` on all legal/KYC mailables. |
| 15 | **OWASP CSV Formula Injection** | Cells starting with `=`, `+`, `-`, `@`, `\t`, `\r` execute formulas or trigger DDE in Excel. | Exporting raw user text fields without formula sanitization. | Use `FormulaSanitizerHelper::escapeFormula()` prefixing sensitive tokens with single quote `'` and handling RFC 4180 quotes. |
| 16 | **Audit Log Target ID Integrity** | Entity-less admin activities (e.g. standalone media upload) crash audit logger. | `audit_logs.target_id` is an unsigned bigint NOT NULL column. | Set `'target_id' => 0` for non-entity or global operational logging events. |
| 17 | **Novel & Chapter Soft-Deletes** | Hard deletion of novel cascades to author statistics, comments, and chapter histories. | Cascade constraint on foreign keys deletes related reading history records permanently. | Implement `Illuminate\Database\Eloquent\SoftDeletes` on `Novel` and `Chapter` models with restore action in Admin Desk. |
| 18 | **Author Chapter Route Binding** | Navigating to `/tac-gia/truyen/{slug}/chuong/them-moi` returns 404. | Route parameter `{truyen}` in `routes/web.php` uses default Laravel primary key binding `id`, not `slug`. | Use novel ID in author studio management routes: `/tac-gia/truyen/{id}/chuong/them-moi`. |
| 19 | **3D Exhibition Three.js Bundling** | 3D gallery `/phong-trung-bay-3d` fails to render WebGL models (`hasThree: false`). | View was missing Three.js/GLTFLoader/OrbitControls CDN scripts and did not instantiate `ProjectionStage`. | Include Three.js bundle in `<head>`, initialize `new window.Novel3D.ProjectionStage(container, { interactive: true })`, wrap `model_file_url` in `asset()`, and auto-load the first asset. |

---

## 4. Session History & Evolution Ledger

| Date | Milestone / Goal | Key Commits | Test Suite Status |
|---|---|---|---|
| 2026-10-21 | Foundation DB, Legal Schema & Models | `23a812e`, `a1084ef` | 42 passed (100%) |
| 2026-10-21 | Author Studio & Novel Publishing CRUD | `5f096d1`, `4d6b701` | 68 passed (100%) |
| 2026-10-21 | Interactive 3D WebGL Reader (Three.js) | `6c1092a`, `39785ff` | 94 passed (100%) |
| 2026-10-21 | Legal DMCA & Admin Moderation Desk | `88390b1`, `08b8b5e` | 118 passed (100%) |
| 2026-10-22 | Docker Packaging & Environment Setup | `e35791c`, `3e36125` | 134 passed (100%) |
| 2026-10-23 | UI Consistency, Modals & Sepia Theme | `68c3409`, `60bc729` | 158 passed (100%) |
| 2026-10-23 | Accessibility & SEO Guidelines Remediation | `c738344`..`679b8d0` | 172 passed (100%) |
| 2026-10-23 | Senior Code Review Patches (C1-C2, I1-I4) & Playwright Verification | Applied & Verified | 179 passed (100%) |
| 2026-10-24 | BRIEF-6 CMS Admin Masterplan (Phases 1-6 Full Implementation) | `1545adb`..`00dfeae` | 222 passed (100%) |
| 2026-10-24 | Remove Dark Mode & Standardize Dual Yellow/Light Theme | `994c0ea`..`dcf8048` | 235 passed (100%) |
| 2026-10-24 | Fix 3D Exhibition Stage Model Rendering & Three.js Init | Applied & Verified | 236 passed (100%) |

---

## 5. Active Backlog & Next Roadmap

1. **Real-Time Interactive Commenting & Chapter Reactions:**
   - Implement WebSocket / polling for reader chapter comments and community discourse.
2. **Text-To-Speech (TTS) Voiceover Reading Mode:**
   - Integrate Web Speech API / synthesized audio narration for visually impaired readers.
3. **Advanced Three.js Shaders & Atmospheric Lighting:**
   - Expand 3D exhibition stage with dynamic environmental HDRIs, volumetric fog, and weapon particles.
4. **Automated DMCA Email Notification Dispatch:**
   - Queue automated email receipts to complainants and authors upon filing copyright reports.
