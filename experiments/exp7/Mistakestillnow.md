# Mistakes Log & Learnings - Experiment 7 (Authentication with Sessions & Cookies)

This document records past oversights, edge-cases, and corrections to ensure continuous quality improvement and prevent regressions.

---

### Initial Observations & Principles
1. **Session Security & Fixation**:
   - Always call `session_regenerate_id(true)` immediately after successful password verification to prevent session fixation attacks.
   - Configure session cookies with `HttpOnly`, `SameSite=Lax`, and `UseOnlyCookies=1`.
2. **Remember-Me Cookie Pattern (Split-Token)**:
   - Never store plain user IDs, password hashes, or reversible encryption in persistent cookies.
   - Always use the Split-Token approach: `selector` (public lookup key) + `validator` (hashed using SHA-256 in the database) to prevent timing attacks and database leak impersonations.
3. **Environment Verification**: Always confirm PHP runtime and active extension availability (PDO, MySQL, SQLite, curl, mbstring, etc.) beforehand.
4. **Path & Script Execution**: Be vigilant with Windows PowerShell escaping rules (e.g. `$_` and `$variable` getting consumed inside double-quoted strings).
5. **Design Principles (Don Norman + Apple)**:
   - Provide clear signifiers, affordances, and immediate feedback for auth actions (login, registration, logout).
   - Password visibility toggles, strength meters, and descriptive error badges.
   - Forgiving design: clear guidance on requirements, session expiry warnings.
   - Apple Aesthetics: Typography hierarchy, subtle blur backgrounds, smooth ease-in-out micro-interactions, uncluttered layouts, high contrast and elegance.

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

### Mistake 6: Unconfigured PDO Extensions in WinGet PHP Installation
- **What happened**: Running `php index.php` or `php -r "..."` threw `PDOException: could not find driver` despite PHP 8.3 CLI binary being available in PATH.
- **Root cause**: The fresh WinGet PHP distribution has `extension_dir = "ext"` as well as `extension=pdo_sqlite` and `extension=pdo_mysql` commented out by default in `php.ini`.
- **Remedy**: Explicitly uncomment `extension_dir = "ext"`, `extension=pdo_sqlite`, `extension=pdo_mysql`, `extension=openssl`, and `extension=mbstring` in `php.ini` using a script, enabling zero-configuration execution via standard `php -S localhost:8000`.

