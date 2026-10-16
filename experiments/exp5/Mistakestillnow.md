# Mistakes Log & Learnings

This document records past oversights, edge-cases, and corrections to ensure continuous quality improvement and prevent regressions.

---

### Initial Observations & Principles
1. **Environment Verification**: Always confirm PHP runtime and active extension availability (PDO, SQLite, etc.) beforehand.
2. **Path & Script Execution**: Be vigilant with Windows PowerShell escaping rules (e.g. `$_` getting consumed if not properly escaped).
3. **Design Principles (Don Norman + Apple)**:
   - Provide clear signifiers, affordances, and immediate feedback for every action (CRUD, search, filtering).
   - Conceptual models: A mental model that matches real-world student grading and result cards.
   - Forgiving design: Confirmations before destructive actions (e.g., delete with undo or clear modal alert), intuitive error prevention.
   - Apple Aesthetics: Typography hierarchy (Inter / SF-style clean grotesque font), subtle blur backgrounds, smooth ease-in-out micro-interactions, uncluttered layouts, high contrast and elegance.

---

### Mistake 1: PowerShell string expansion with `$_`
- **What happened**: Passing `Where-Object { $_.href ... }` inside a double-quoted PowerShell string (`powershell -Command "..."`) caused the subshell to expand `$_` to empty, turning it into `.href` which threw `CommandNotFoundException`.
- **Root cause**: Double quotes in shell commands expand variables before execution unless escaped.
- **Remedy**: Always use single quotes for the PowerShell `-Command` string or escape `$_` as `` `$_ `` or write a dedicated script.

---

### Mistake 2: Unix `grep` instead of PowerShell `Select-String`
- **What happened**: Piped curl output to `grep -i` on a Windows PowerShell environment where GNU coreutils grep is not in default PATH.
- **Root cause**: Assumed POSIX environment utility in PowerShell.
- **Remedy**: Use PowerShell native `Select-String` or `findstr.exe` on Windows.

---

### Mistake 3: PowerShell `-replace` operator with unescaped regex characters
- **What happened**: Used `-replace ';extension_dir = "ext"'` which treated the pattern as a regular expression with unescaped quotes/backslashes.
- **Root cause**: PowerShell's `-replace` operator uses regex pattern matching rather than literal substring matching.
- **Remedy**: Use `[string]::Replace()` for literal string replacement, or escape regex meta-characters.



