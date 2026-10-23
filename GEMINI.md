# GEMINI.md — Gemini Engine System Directives

> **System Scope:** This directive specifically steers Google Gemini (Antigravity IDE, Gemini CLI, Gemini Code Assist) when operating in this workspace.

---

## 1. Gemini Pair Programming Persona & Behavior

1. **Concise & Evidence-First:**
   - Keep conversational narration concise and direct.
   - Present verifiable test commands and real execution outputs before asserting task completion.
   - You **MUST** format file and symbol links using the GitHub-style markdown syntax `[filename](file:///path/to/file)`.
2. **Autonomous Execution:**
   - When executing an agreed plan, work continuously through tasks without pausing to ask "Should I continue?" or "Do you want me to proceed?".
   - Stop only for destructive operations, security-critical choices, or unresolvable plan conflicts.
3. **Tool Execution Discipline:**
   - Prefer `replace_file_content` for single contiguous edits.
   - Use `multi_replace_file_content` when making non-contiguous edits in a single file.
   - For temporary scratch test scripts or data, use `<artifactDir>/scratch/`.

---

## 2. Mandatory Session Lifecycle Protocol

1. **Boot Sequence (Pre-flight):**
   - At the beginning of each session, read this file and inspect [`.agents/session_memory.md`](file:///.agents/session_memory.md).
   - Ingest the current architectural state, core invariants, and recently learned lessons.
   - Confirm current git status before initiating edits.
2. **In-flight Execution:**
   - Always invoke `sequentialthinking` (`sequential-thinking` MCP server) on every task to plan and reason systematically through requirements and edge cases.
   - Always adhere to TDD (Red -> Green -> Refactor).
   - If a new framework quirk or bug pattern is solved, immediately document it in Section 3 of [`.agents/session_memory.md`](file:///.agents/session_memory.md).
3. **Session Handoff (Post-flight):**
   - Execute verification test suite (`php artisan test`).
   - Execute visual & interactive browser verification using `playwright` and `chrome-devtools-mcp` tools before concluding to ensure zero frontend regression.
   - Append completed milestone commits to Section 4 of [`.agents/session_memory.md`](file:///.agents/session_memory.md).

---

## 3. High-Priority Workspace Pointers

- **Global Invariants & Rules:** See [`AGENTS.md`](file:///AGENTS.md).
- **Domain-Specific Rules:**
  - PowerShell syntax: [`.agents/rules/environment-windows-powershell.md`](file:///.agents/rules/environment-windows-powershell.md)
  - Laravel 11 patterns: [`.agents/rules/laravel-backend-architecture.md`](file:///.agents/rules/laravel-backend-architecture.md)
  - Frontend accessibility & CLS: [`.agents/rules/frontend-ui-a11y-performance.md`](file:///.agents/rules/frontend-ui-a11y-performance.md)
  - Testing & Blade rendering: [`.agents/rules/testing-quality-standards.md`](file:///.agents/rules/testing-quality-standards.md)
- **Active Memory Bank:** [`.agents/session_memory.md`](file:///.agents/session_memory.md)
- **Operational Protocol:** [`.agents/session_protocol.md`](file:///.agents/session_protocol.md)
