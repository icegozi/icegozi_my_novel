# Operational Protocol: Session Lifecycle for Gemini

> **Target Agent:** Google Gemini (Antigravity IDE / Gemini CLI / Code Assist).
> **Purpose:** Defines the standardized 3-step operational workflow for every conversational session.

---

### Step 1: Session Boot (Pre-flight)

When entering a new conversation session, Gemini **MUST** complete this checklist before proposing or executing changes:

1. **Rule & Memory Ingestion:**
   - Automatically read [`AGENTS.md`](file:///AGENTS.md) and [`GEMINI.md`](file:///GEMINI.md) at the repository root.
   - Inspect [`.agents/session_memory.md`](file:///.agents/session_memory.md) to load the active architectural snapshot, database invariants, and recently learned gotchas.
2. **Repository State Verification:**
   - Run `git status` to determine:
     - The current working branch.
     - Any unstaged or untracked modifications left from previous sessions.
3. **Situational Briefing:**
   - Greet the user with a concise, 1-2 sentence status summary:
     - Identify current branch and state.
     - Affirm readiness to proceed with the active backlog or user instructions.

---

### Step 2: In-flight Execution

During task execution within the session, Gemini **MUST** operate under these quality rules:

1. **Test-Driven Rigor (TDD):**
   - Follow Red -> Green -> Refactor for all functional or bug fix tasks.
   - Run verification commands via the terminal and verify output before declaring success.
2. **Shell Syntax Hygiene:**
   - Use PowerShell semicolon `;` for command chaining. Never use `&&`.
   - Never use `cd`; specify target paths in `Cwd`.
3. **Continuous Knowledge Recording:**
   - If an unexpected bug, framework quirk, or platform constraint is diagnosed and resolved, **immediately append the finding** to Section 3 (*Lessons Learned & Known Gotchas*) in [`.agents/session_memory.md`](file:///.agents/session_memory.md).

---

### Step 3: Session Handoff (Post-flight)

When a task, plan, or session milestone is completed, Gemini **MUST** perform the handoff ritual:

1. **Comprehensive Verification:**
   - Run targeted feature tests: `php artisan test --filter=<TestName>`.
   - Run global regression test suite: `php artisan test` (confirm 100% green).
   - If frontend assets were modified, run `npm run build` to confirm 0 compilation errors.
2. **Session Ledger Update:**
   - Append a new entry to Section 4 (*Session History & Evolution Ledger*) in [`.agents/session_memory.md`](file:///.agents/session_memory.md):
     - Date
     - Milestone / Feature summary
     - Key commit hashes
     - Automated test results
3. **User Handoff Report:**
   - Present a concise, bulleted summary to the user highlighting completed items, test evidence, and commit IDs.
   - Outline recommended next steps from the active backlog.
