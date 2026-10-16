# Thinking Workflow: Exp6 (CRUD) & Exp7 (Auth)

## Persona: Linus (Systems) + Uncle Bob (Architecture) + Don Norman (UX)

```
DISCOVER ──► ARCHITECT ──► IMPLEMENT ──► VERIFY ──► DOCUMENT
   │              │              │            │           │
   ▼              ▼              ▼            ▼           ▼
 PHP runtimes   Resilient     exp6: CRUD    php -l ×8   Lab reports
 pdo_mysql off  MySQL→SQLite  exp7: Auth    CLI login   + this file
 Port 3306 down fallback      Sessions      BCrypt OK
 php.ini fixed  pattern       Cookies       SSR render
```

## Key Decisions (5-Liner)
- **MySQL offline** → Built dual-engine driver (MySQL primary + SQLite fallback) = zero crash risk
- **pdo_mysql disabled** → Appended `extension=pdo_mysql` & `extension=mysqli` to `C:\Users\Mani\php\php.ini`
- **exp6 CRUD** → PDO prepared statements, academic grade calculator, CSV/JSON export, glassmorphic UI
- **exp7 Auth** → BCrypt(cost=12), `session_regenerate_id(true)`, Split-Token cookie, 30min sliding expiry
- **PowerShell escaping** → Mistake #5: Use backtick (`` `$var ``), not backslash (`\$var`)

## Files Created
```
exp6/  config.php, schema.sql, db.php, api.php, index.php, assets/{style.css, app.js}
exp7/  config.php, schema.sql, db.php, auth.php, api.php, index.php, assets/{style.css, app.js}
Both/  Mistakestillnow.md, Lab Report .md
```
