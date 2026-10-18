# Mistakes Log & Learnings - Experiment 6 (PHP & MySQL CRUD)

This document records past oversights, edge-cases, and corrections to ensure continuous quality improvement and prevent regressions.

---

### Initial Observations & Principles
1. **Environment Verification**: Always confirm PHP runtime and active extension availability (PDO, MySQL, SQLite, curl, mbstring, etc.) beforehand.
2. **Path & Script Execution**: Be vigilant with Windows PowerShell escaping rules (e.g. `$_` and `$variable` getting consumed inside double-quoted strings).
3. **Design Principles (Don Norman + Apple)**:
   - Provide clear signifiers, affordances, and immediate feedback for every action (CRUD, search, filtering).
   - Conceptual models: A mental model that matches real-world student grading and result cards.
   - Forgiving design: Confirmations before destructive actions (e.g., delete with modal alert), intuitive error prevention.
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

---

### Mistake 4: MySQL service absence during local execution
- **What happened**: Running a script expecting a live MySQL daemon on localhost port 3306 failed when the service was not started.
- **Root cause**: MySQL server is an external daemon that may not be running on development workstations.
- **Remedy**: Implement a resilient multi-engine driver using PDO MySQL as the primary target and automatic failover / fallback to SQLite, accompanied by clear visual database status indicators and connection diagnostics.

---

### Mistake 5: PowerShell variable escaping with backslash vs backtick
- **What happened**: Passed `\$var` to PHP inline scripts inside PowerShell double quotes, causing syntax errors.
- **Root cause**: In Windows PowerShell, the escape character is the backtick (`` ` ``), not backslash (`\`).
- **Remedy**: Use `` `$var `` in PowerShell double-quoted strings, or write a dedicated standalone test script.

---

### Mistake 6: PHP SQLite driver not loading on Windows
- **What happened**: Experiment 6 returned HTTP 500 with `PDOException: could not find driver` when PHP tried to open its SQLite database.
- **Root cause**: The PHP installation had the `pdo_sqlite` DLL, but it was not enabled and PHP's configured extension directory pointed to `C:\php\ext` instead of the PHP installation's `ext` folder.
- **Remedy**: Start the built-in server with the correct extension directory and SQLite PDO driver enabled:
  ```powershell
  $phpDir = Split-Path (Get-Command php).Source
  php -d "extension_dir=$phpDir\ext" -d extension=pdo_sqlite -S localhost:8000 -t "D:\7-sem\advWebTechRepo\experiments\exp6"
  ```
  Verify the driver with `php -d "extension_dir=$phpDir\ext" -d extension=pdo_sqlite -m | findstr /i "pdo sqlite"`; it should list `PDO` and `pdo_sqlite`. Open `http://localhost:8000` after starting the server.

### Mistake 7: DTU transcript and enrollment form
- **Problem faced:** Transcript still showed an incorrect university affiliation; old student emails were not DTU-formatted, and the long enrollment form could not scroll to its lower fields.
- **How fixed:** Use only “Delhi Technological University” on the transcript, generate/migrate emails as `firstname.lastname.rollno@dtu.ac.in`, and make the form a flex column with a scrollable body.
