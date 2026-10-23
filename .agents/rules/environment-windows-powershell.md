# Workspace Rule: Windows PowerShell Environment

> **Scope:** Governs all CLI commands, shell executions, and terminal interactions in this repository.

---

## 1. Shell & Operating System Directives

1. **Host Environment:**
   - Operating System: Windows 11.
   - Shell: PowerShell 7+ / Windows PowerShell.
2. **Command Sequencing & Chaining:**
   - You **MUST** use the semicolon `;` operator to chain commands:
     ```powershell
     # Correct (PowerShell syntax)
     git add -A ; git commit -m "feat: description"
     ```
   - You **MUST NOT** use `&&` or `||` in PowerShell tool calls, as this frequently fails or behaves unpredictably across Windows PowerShell environments.
3. **Directory Navigation & Working Directory:**
   - You **MUST NEVER** execute `cd <path>` commands.
   - You **MUST** specify the absolute or relative target directory in the tool's `Cwd` parameter (e.g. `Cwd: "d:\\My Project\\Github\\icegozi_my_novel"`).

---

## 2. Path Handling & Quotation

1. **File Paths:**
   - Both forward slashes `/` and escaped backslashes `\\` are supported in PowerShell, but forward slashes `/` are strongly **RECOMMENDED** for consistency with Git and Laravel tooling.
2. **Quotation Rules:**
   - When executing commands with strings containing spaces or special characters, wrap arguments in double quotes `"` or single quotes `'`:
     ```powershell
     php artisan test --filter="AuthorNovelsGuidelinesTest"
     ```

---

## 3. Daemon Services & Long-Running Tasks

1. **Dev Servers & File Watchers:**
   - When launching persistent processes (`php artisan serve`, `npm run dev`), set `IsDaemon: true` or allow the command to run asynchronously after a bounded wait (`WaitMsBeforeAsync: 500-1000`).
   - You **MUST NOT** block indefinitely waiting for a background daemon to exit synchronously.
2. **Process Management:**
   - Use `manage_task` with action `'status'` or `'kill'` to inspect or terminate running background tasks. Never start a duplicate instance of an already running server.
