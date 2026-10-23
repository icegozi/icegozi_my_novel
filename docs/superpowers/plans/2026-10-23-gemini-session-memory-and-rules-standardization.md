# Gemini Session Memory & Rules Standardization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish an international-standard, robust, automated Memory & Rule System for Gemini (Antigravity IDE / Gemini CLI / Code Assist) that persists project context, enforces engineering constraints, catalogues hard-earned experience, and standardizes session lifecycle protocols.

**Architecture:** A 4-tier hierarchical context system: (1) Root directives `AGENTS.md` and `GEMINI.md` discovered unconditionally at session start; (2) Modular specialized rules in `.agents/rules/` covering Windows PowerShell, Laravel 11 architecture, frontend a11y/performance, and testing standards; (3) A structured, persistent Session Memory Bank in `.agents/session_memory.md` tracking architecture snapshots, active state, lessons learned, and session history; (4) A standardized session lifecycle protocol in `.agents/session_protocol.md` guiding Gemini on boot, execution, and handoff.

**Tech Stack:** Markdown (RFC 2119 specification style), Antigravity Customization Architecture (`.agents/`), Laravel 11 / PHPUnit test suite, Windows PowerShell environment.

**Spec:** User request to create a cross-session memory file, standardize rules, project structure, operational experience, conforming to international standards and optimized for Gemini.

## Global Constraints

- Root rule files MUST be placed at project root: `AGENTS.md` and `GEMINI.md`.
- Workspace customizations MUST reside in `.agents/` and be version-controlled in Git.
- Rules MUST use RFC 2119 keyword standards (`MUST`, `MUST NOT`, `SHOULD`, `RECOMMENDED`).
- Commands in rules MUST reflect Windows PowerShell shell syntax (separator `;`, never `&&`; no unix-only utilities).
- Memory bank MUST include: Architecture Snapshot, Core Invariants, Accumulated Lessons Learned, Active Backlog, and Session Evolution History.
- System integrity MUST be verifiable via an automated PHPUnit feature test (`tests/Feature/AgentRulesAndMemoryIntegrityTest.php`).

## Review Focus

1. **Context Window Token Efficiency:** Rule files must be high-signal and structured with tables and concise bullet points to avoid consuming excessive LLM context tokens.
2. **Windows PowerShell Compatibility:** Rule documentation must prevent agents from attempting `&&` command chaining or non-existent Unix utilities.
3. **Database Check Constraints:** SQLite `users.role` constraint (`reader`, `author`, `moderator`, `admin`) must be documented prominently to prevent factory/test errors.
4. **WCAG & SEO Standards:** Guidelines for `aria-label` matching visible text (WCAG 2.5.3), image CLS prevention (`width`/`height`), touch targets (>= 24px min), and explicit CSS transitions must be codified.
5. **Session Continuity Protocol:** Clear instructions for how Gemini ingests session memory at start and records new lessons at session end.

---

### Task 1: Automated Agent Rules & Memory Integrity Test

**Files:**
- Create: `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`
- Test: `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`

**Interfaces:**
- Consumes: Filesystem check on project root and `.agents/` directory.
- Produces: Test class asserting presence, structure, and required sections of all rule and memory files.

- [ ] **Step 1: Write the failing test in `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class AgentRulesAndMemoryIntegrityTest extends TestCase
{
    public function test_root_rule_files_exist_and_contain_core_directives(): void
    {
        $this->assertFileExists(base_path('AGENTS.md'));
        $this->assertFileExists(base_path('GEMINI.md'));

        $agentsContent = file_get_contents(base_path('AGENTS.md'));
        $geminiContent = file_get_contents(base_path('GEMINI.md'));

        $this->assertStringContainsString('# AGENTS.md — Global Workspace Directives', $agentsContent);
        $this->assertStringContainsString('Windows PowerShell', $agentsContent);
        $this->assertStringContainsString('RFC 2119', $agentsContent);

        $this->assertStringContainsString('# GEMINI.md — Gemini Engine System Directives', $geminiContent);
        $this->assertStringContainsString('.agents/session_memory.md', $geminiContent);
    }

    public function test_modular_rule_files_exist_in_agents_directory(): void
    {
        $rulesDir = base_path('.agents/rules');
        $this->assertDirectoryExists($rulesDir);

        $expectedRules = [
            'environment-windows-powershell.md',
            'laravel-backend-architecture.md',
            'frontend-ui-a11y-performance.md',
            'testing-quality-standards.md',
        ];

        foreach ($expectedRules as $ruleFile) {
            $path = $rulesDir . DIRECTORY_SEPARATOR . $ruleFile;
            $this->assertFileExists($path, "Rule file {$ruleFile} must exist");
            $content = file_get_contents($path);
            $this->assertNotEmpty($content);
            $this->assertStringContainsString('MUST', $content);
        }
    }

    public function test_session_memory_and_protocol_exist_with_required_sections(): void
    {
        $memoryPath = base_path('.agents/session_memory.md');
        $protocolPath = base_path('.agents/session_protocol.md');

        $this->assertFileExists($memoryPath);
        $this->assertFileExists($protocolPath);

        $memoryContent = file_get_contents($memoryPath);
        $this->assertStringContainsString('## 1. Project Architecture Snapshot', $memoryContent);
        $this->assertStringContainsString('## 2. Core Invariants & Security Rules', $memoryContent);
        $this->assertStringContainsString('## 3. Lessons Learned & Known Gotchas', $memoryContent);
        $this->assertStringContainsString('## 4. Session History & Evolution Ledger', $memoryContent);

        $protocolContent = file_get_contents($protocolPath);
        $this->assertStringContainsString('### Step 1: Session Boot (Pre-flight)', $protocolContent);
        $this->assertStringContainsString('### Step 2: In-flight Execution', $protocolContent);
        $this->assertStringContainsString('### Step 3: Session Handoff (Post-flight)', $protocolContent);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest`
Expected: FAIL (files `AGENTS.md`, `GEMINI.md`, `.agents/` do not exist yet).

- [ ] **Step 3: Placeholder setup (will be fulfilled by subsequent tasks)**

Verify test failure message shows missing `AGENTS.md`.

- [ ] **Step 4: Keep test for subsequent task execution**

Proceed to implement Task 2 through Task 5.

- [ ] **Step 5: Commit initial test**

```bash
git add tests/Feature/AgentRulesAndMemoryIntegrityTest.php
git commit -m "test(agents): add automated integrity test for agent rules and memory system"
```

---

### Task 2: Root Directive Standards (`AGENTS.md` & `GEMINI.md`)

**Files:**
- Create: `AGENTS.md`
- Create: `GEMINI.md`
- Test: `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`

**Interfaces:**
- Consumes: Antigravity / Gemini root file discovery mechanism.
- Produces: Global top-priority workspace directives for every chat session.

- [ ] **Step 1: Write failing test confirmation**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest::test_root_rule_files_exist_and_contain_core_directives`
Expected: FAIL.

- [ ] **Step 2: Create `AGENTS.md` at project root**

Create `AGENTS.md` with:
- Specification standard: RFC 2119 (`MUST`, `MUST NOT`, `SHOULD`, `RECOMMENDED`).
- Workspace Environment: OS Windows 11, Shell PowerShell, separator `;`, never `&&`.
- Architecture Map: Laravel 11, Vite, Tailwind v3, Three.js 3D WebGL reader, SQLite database.
- Coding & Security Rules: TDD requirement, DB constraints, Legal/DMCA compliance (NĐ 17/2023, NĐ 13/2023, NĐ 147/2024).
- Pointers to `.agents/rules/` and `.agents/session_memory.md`.

- [ ] **Step 3: Create `GEMINI.md` at project root**

Create `GEMINI.md` with:
- Specific optimizations for Gemini in Antigravity IDE:
  - System persona and pairing behavior.
  - Mandatory session boot sequence reading `.agents/session_memory.md`.
  - Guidelines on tool usage (replace_file_content vs multi_replace_file_content, run_command timeout rules).
  - Progressive disclosure pointers to `.agents/rules/*.md`.

- [ ] **Step 4: Run test to verify root rule check passes**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest::test_root_rule_files_exist_and_contain_core_directives`
Expected: PASS.

- [ ] **Step 5: Commit changes**

```bash
git add AGENTS.md GEMINI.md
git commit -m "docs(rules): create standardized AGENTS.md and GEMINI.md root directives"
```

---

### Task 3: Modular Workspace Rules Engine (`.agents/rules/`)

**Files:**
- Create: `.agents/rules/environment-windows-powershell.md`
- Create: `.agents/rules/laravel-backend-architecture.md`
- Create: `.agents/rules/frontend-ui-a11y-performance.md`
- Create: `.agents/rules/testing-quality-standards.md`
- Test: `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`

**Interfaces:**
- Consumes: Antigravity progressive rule discovery in `.agents/rules/`.
- Produces: Specialized, modular domain rules for backend, frontend, environment, and testing.

- [ ] **Step 1: Write failing test confirmation**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest::test_modular_rule_files_exist_in_agents_directory`
Expected: FAIL.

- [ ] **Step 2: Create `.agents/rules/environment-windows-powershell.md`**

Document:
- Shell: PowerShell on Windows.
- Command chaining: MUST use `;` (e.g. `git add . ; git commit -m "..."`), MUST NOT use `&&`.
- Paths: Use forward slashes `/` or escaped backslashes `\\`.
- Daemon commands: Mark dev servers/watchers as background tasks with appropriate `WaitMsBeforeAsync`.
- Forbidden: NEVER propose `cd` commands.

- [ ] **Step 3: Create `.agents/rules/laravel-backend-architecture.md`**

Document:
- Model & Migration conventions: SQLite check constraint `users.role IN ('reader', 'author', 'moderator', 'admin')` (never `'user'`).
- Author verification: `EnsureAuthorVerified` middleware requires `AuthorProfile` with `verification_status = 'verified'`.
- Storage URLs: Internal assets MUST use `Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url)` to prevent 403 Forbidden.
- Relationships & Eloquent: explicit typing, casts, foreign keys.

- [ ] **Step 4: Create `.agents/rules/frontend-ui-a11y-performance.md`**

Document:
- WCAG 2.5.3 (Label in Name): Accessible name (`aria-label`) MUST contain visible text.
- WCAG 1.3.1 (Info & Relationships): Strict heading hierarchy (`h1` -> `h2` -> `h3`), form `<label for>` paired with `<input id>`.
- WCAG 1.1.1 (Non-text Content): Alt text on meaningful images, `aria-hidden="true"` on decorative icons and emojis.
- Performance & CLS: All `<img>` tags MUST have explicit `width` and `height` attributes.
- CSS Transitions: MUST NOT use `transition-all`; specify explicit transition properties (`transition-colors`, `transition-opacity`, `transition-transform`).
- Touch Targets & Contrast: Minimum touch target 24px x 24px (44px for primary CTAs); text contrast ratio >= 4.5:1 (normal) / 3:1 (large).

- [ ] **Step 5: Create `.agents/rules/testing-quality-standards.md`**

Document:
- TDD Red-Green-Refactor cycle: Always write failing test first, verify failure, implement minimal code, verify pass.
- Windows View Rendering: In Blade unit/feature tests, use `view('...')->render()` instead of `$this->blade()` to avoid Windows `tempnam()` notice.
- Database isolation: Always use `use RefreshDatabase;` on tests that interact with database queries.

- [ ] **Step 6: Run test to verify modular rules pass**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest::test_modular_rule_files_exist_in_agents_directory`
Expected: PASS.

- [ ] **Step 7: Commit changes**

```bash
git add .agents/rules/
git commit -m "docs(rules): add modular domain rules for PowerShell, Laravel, Frontend a11y and TDD"
```

---

### Task 4: Persistent Session Memory Bank (`.agents/session_memory.md`)

**Files:**
- Create: `.agents/session_memory.md`
- Test: `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`

**Interfaces:**
- Consumes: Cumulative technical discoveries, past plans, architectural decisions from all previous sessions.
- Produces: Comprehensive, single-source-of-truth project memory for Gemini.

- [ ] **Step 1: Write failing test confirmation**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest::test_session_memory_and_protocol_exist_with_required_sections`
Expected: FAIL.

- [ ] **Step 2: Create `.agents/session_memory.md`**

Structure:
- `## 1. Project Architecture Snapshot`:
  - Core Modules: Novel Catalog, Chapter Reader, 3D WebGL Exhibition & Stage, Author Studio & e-KYC, DMCA / Copyright Reports (NĐ 17/2023), Admin & Moderation.
  - Active Tech Stack: Laravel 11.31, PHP 8.2+, SQLite (local/testing) / PostgreSQL (production), Tailwind CSS v3, Three.js 0.160, Alpine.js 3.14.
- `## 2. Core Invariants & Security Rules`:
  - Roles, middleware, CSRF tokens, e-KYC verified status, legal compliance.
- `## 3. Lessons Learned & Known Gotchas`:
  - PowerShell `;` vs `&&`.
  - SQLite role check constraint.
  - Cover image 403 Forbidden resolution with `Str::startsWith`.
  - Password manager hidden username autocomplete pattern.
  - WCAG 2.5.3 visible label requirement.
  - Modal overscroll containment and `role="dialog" aria-modal="true"`.
- `## 4. Session History & Evolution Ledger`:
  - Chronological table of all milestone commits, plans, and features delivered from 2026-10-21 to present.
- `## 5. Active Backlog & Next Roadmap`:
  - Upcoming features and polish items for future sessions.

- [ ] **Step 3: Verify `.agents/session_memory.md` syntax and formatting**

Inspect file contents to ensure all headers and markdown tables are cleanly formatted.

- [ ] **Step 4: Commit changes**

```bash
git add .agents/session_memory.md
git commit -m "docs(memory): initialize persistent cross-session memory bank with architectural knowledge and gotchas"
```

---

### Task 5: Session Lifecycle Protocol (`.agents/session_protocol.md`)

**Files:**
- Create: `.agents/session_protocol.md`
- Test: `tests/Feature/AgentRulesAndMemoryIntegrityTest.php`

**Interfaces:**
- Consumes: Gemini prompt execution order.
- Produces: Actionable instructions for Gemini at Boot, In-flight, and Handoff stages.

- [ ] **Step 1: Create `.agents/session_protocol.md`**

Include:
- **Phase 1: Session Boot (Pre-flight)**:
  1. Inspect `AGENTS.md` / `GEMINI.md`.
  2. Inspect `.agents/session_memory.md` to load the current phase and known gotchas.
  3. Run `git status` to verify working branch and uncommitted changes.
  4. Greet user with a 2-sentence situational briefing (current state + ready to execute).
- **Phase 2: In-flight Execution**:
  1. Follow TDD (Red -> Green -> Refactor).
  2. Adhere to `.agents/rules/*.md`.
  3. If encountering a new bug or unexpected framework behavior, immediately log the fix in Section 3 of `.agents/session_memory.md`.
- **Phase 3: Session Handoff (Post-flight)**:
  1. Verify tests pass: `php artisan test`.
  2. Update Section 4 (Session History & Evolution Ledger) in `.agents/session_memory.md` with commit hashes and summary.
  3. Report summary with exact file links and test results.

- [ ] **Step 2: Run test to verify all memory and protocol tests pass**

Run: `php artisan test --filter=AgentRulesAndMemoryIntegrityTest`
Expected: PASS (all 3 tests, ~15 assertions).

- [ ] **Step 3: Commit changes**

```bash
git add .agents/session_protocol.md
git commit -m "docs(protocol): define Gemini session boot, in-flight and handoff lifecycle protocol"
```

---

### Task 6: Full Regression Verification & Release Commit

**Files:**
- Verify: Full test suite, Git branch status, Vite build.

**Interfaces:**
- Consumes: All 8 new/updated agent rules, memory files, and feature tests.
- Produces: 100% green automated test suite across all 175+ tests and verified clean repository.

- [ ] **Step 1: Run complete PHPUnit test suite**

Run: `php artisan test`
Expected: 100% PASS across all feature and unit tests with 0 failures (175+ tests).

- [ ] **Step 2: Run Vite build**

Run: `npm run build`
Expected: Build passes with 0 errors.

- [ ] **Step 3: Final release commit**

```bash
git add -A
git commit -m "chore: complete Gemini session memory, rules, and protocol standardization"
```

---
