# AGENTS.md — Global Workspace Directives

> **Specification Standard:** The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

---

## 1. Environment & Shell Protocols

1. **Operating System:** Windows 11.
2. **Primary Shell:** Windows PowerShell.
3. **Command Chaining:**
   - You **MUST** use the semicolon `;` separator to sequence commands (e.g. `git add . ; git commit -m "..."`).
   - You **MUST NOT** use the POSIX bash `&&` separator, as it is invalid syntax in PowerShell.
4. **Directory Navigation:**
   - You **MUST NEVER** execute `cd` commands. Set the working directory directly via the execution tool's `Cwd` parameter.
5. **Daemon & Long-Running Services:**
   - Dev servers (`php artisan serve`, `npm run dev`) **MUST** be run with background task flags and bounded synchronous wait times.

---

## 2. Project Architecture Snapshot

- **Framework:** Laravel 11 (PHP 8.2+).
- **Frontend Stack:** Vanilla Blade views, Tailwind CSS v3, Alpine.js v3, Three.js (WebGL 3D Reader).
- **Asset Bundler:** Vite 5.
- **Database Engine:** SQLite (Local/Testing), PostgreSQL (Production target).
- **Core Domain Entities:** `Novel`, `Chapter`, `Novel3dAsset`, `Chapter3dProjection`, `User`, `AuthorProfile`, `ReadingHistory`, `CopyrightReport`.

---

## 3. Engineering Invariants & Coding Rules

1. **Test-Driven Development (TDD):**
   - Every feature or bug fix **MUST** follow the Red -> Green -> Refactor cycle.
   - You **MUST** write the failing test first, run it to verify failure, implement minimal code, and verify that the test passes.
2. **Database Integrity:**
   - `users.role` is guarded by an SQLite check constraint: `role IN ('reader', 'author', 'moderator', 'admin')`.
   - Tests and factories **MUST NOT** assign `'role' => 'user'`.
3. **Author Access Control:**
   - Author studio routes require `EnsureAuthorVerified` middleware.
   - Verified authors **MUST** have an associated `AuthorProfile` with `verification_status = 'verified'`.
4. **Local Asset & Storage URLs:**
   - Media URLs **MUST** check `Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url)` to prevent 403 Forbidden on internal cover paths.
5. **Accessibility & WCAG 2.1/2.2:**
   - Accessible labels on buttons and links **MUST** contain any visible text (WCAG 2.5.3 Label in Name).
   - Form inputs **MUST** have an explicit associated `<label for="id">` or `aria-label`.
   - Heading levels **MUST NOT** skip levels (e.g., `h1` directly to `h3`).
   - Decorative icons and emojis **MUST** have `aria-hidden="true"`.
   - All `<img>` tags **MUST** specify explicit `width` and `height` attributes to prevent Cumulative Layout Shift (CLS).
6. **CSS Transitions:**
   - Templates and stylesheets **MUST NOT** use `transition-all`. Specify explicit transitions (e.g., `transition-colors`, `transition-opacity`, `transition-transform`).
7. **Mandatory MCP Sequential Thinking:**
   - You **MUST** invoke the `sequential-thinking` MCP tool (`sequentialthinking`) on **every task** before code modification and during execution to analyze constraints, map edge cases, and reason step-by-step.
8. **Mandatory Playwright & Chrome Pre-Handoff Verification:**
   - Before handing off any task or milestone to the user, you **MUST** execute live browser testing using `playwright` and/or `chrome-devtools-mcp` tools (navigating, interacting with controls, capturing visual screenshots, and inspecting console errors) to guarantee 100% flawless user experience.

---

## 4. Cross-Session Memory & Rule Reference

- **Modular Domain Rules:** Explore dedicated rules in [`.agents/rules/`](file:///.agents/rules/):
  - [`.agents/rules/environment-windows-powershell.md`](file:///.agents/rules/environment-windows-powershell.md)
  - [`.agents/rules/laravel-backend-architecture.md`](file:///.agents/rules/laravel-backend-architecture.md)
  - [`.agents/rules/frontend-ui-a11y-performance.md`](file:///.agents/rules/frontend-ui-a11y-performance.md)
  - [`.agents/rules/testing-quality-standards.md`](file:///.agents/rules/testing-quality-standards.md)
- **Active Memory Bank:** Refer to and maintain [`.agents/session_memory.md`](file:///.agents/session_memory.md) for persistent state, accumulated lessons learned, and session evolution.
- **Session Execution Protocol:** Follow [`.agents/session_protocol.md`](file:///.agents/session_protocol.md) for session boot, in-flight work, and handoff rituals.
