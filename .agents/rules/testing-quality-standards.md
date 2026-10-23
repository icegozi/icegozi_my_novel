# Workspace Rule: Testing & Code Quality Standards

> **Scope:** Governs PHPUnit test authoring, test execution, regression verification, and QA gating.

---

## 1. Test-Driven Development (TDD) Protocol

1. **Strict Three-Phase Cycle:**
   - **Phase 1 (RED):** Write a focused test asserting the new behavior or bug fix. Run the test and confirm it fails with the expected assertion error.
   - **Phase 2 (GREEN):** Implement the minimal code necessary to satisfy the test. Run the test and confirm it passes.
   - **Phase 3 (REFACTOR):** Clean up the code while keeping tests green.
2. **Evidence Before Assertions:**
   - You **MUST** run the test command via the terminal and inspect real output before claiming a task or bug fix is complete.
   - Never assume or guess test outcomes.

---

## 2. Windows Environment Testing Practices

1. **Blade Template Testing:**
   - On Windows, `$this->blade("@include('...')")` can trigger PHP `tempnam()` notices depending on system temp file permissions.
   - You **MUST** render Blade views directly in test cases:
     ```php
     // Preferred Windows-safe pattern:
     $rendered = view('reader.chapters.partials.zen_toolbar')->render();
     $this->assertStringContainsString('aria-label="A- Giảm kích thước phông chữ"', $rendered);
     ```
2. **Database Isolation & Trait Usage:**
   - Any test querying the database or using Eloquent models **MUST** include the `use RefreshDatabase;` trait.
   - SQLite in-memory tables are wiped and migrated cleanly for each test method when this trait is present.
3. **Session & Auth State Hygiene:**
   - In feature tests, `$this->actingAs($user)` persists throughout the current test method.
   - To test guest-only routes (e.g. `/login` with guest middleware) alongside authenticated flows, split them into distinct test methods:
     ```php
     public function test_authenticated_user_accesses_library(): void { ... }
     public function test_guest_views_login_page(): void { ... }
     ```

---

## 3. Suite Health & CI Verification

1. **Targeted Runs:**
   - During active development, run targeted tests using the `--filter` flag:
     ```powershell
     php artisan test --filter="NovelDetailAndZenGuidelinesTest"
     ```
2. **Global Pre-Commit Verification:**
   - Before finishing any feature or branch, you **MUST** run the full test suite (`php artisan test`) and confirm 0 failures across all 170+ tests.
   - You **MUST** execute `npm run build` to verify there are 0 Vite compilation errors.

---

## 4. Mandatory MCP Tooling & Browser QA Verification

1. **MCP Sequential Thinking Protocol:**
   - For **every task**, you **MUST** invoke `sequentialthinking` (`sequential-thinking` MCP server) before starting code changes and during critical decision points.
   - Use it to systematically unpack assumptions, list affected components, plan test scenarios, and trace side effects.
2. **Playwright & Chrome Browser Pre-Handoff Quality Gate:**
   - Before any task or pull request handoff, you **MUST** perform active browser checks using `playwright` and/or `chrome-devtools-mcp`.
   - The browser check **MUST** include:
     - Navigating to the relevant live URL (with `php artisan serve` running in the background).
     - Exercising the primary UI interactions (e.g. clicking buttons, toggling themes, adjusting responsive layouts).
     - Inspecting browser console messages to verify zero unhandled JavaScript errors.
     - Capturing visual screenshots (`browser_take_screenshot` / `take_screenshot`) as verifiable proof of quality.
