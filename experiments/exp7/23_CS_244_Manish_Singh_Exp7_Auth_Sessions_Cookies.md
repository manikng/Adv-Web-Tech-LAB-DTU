# Experiment 7: User Authentication using Sessions and Cookies

**Subject:** Advanced Web Technologies (AWT)  
**Experiment No:** 07  
**Topic:** Implementation of a User Registration and Authentication System using PHP Sessions and Cookies with State Persistence and Secure Logout  
**Student Name:** `Manish Singh`  
**Roll Number:** `23/CS/244`  
**Date of Submission:** October 13, 2026  

---

## 1. Aim
To design, implement, and verify a secure **User Authentication System** using **PHP Sessions and Cookies** that supports user registration, credential verification, persistent session management via "Remember Me" tokens, session fixation mitigation, CSRF protection, and a clean logout mechanism.

---

## 2. Objectives
1. **Solve HTTP Statelessness**: To bridge the stateless nature of the HTTP protocol by employing server-side PHP sessions (`$_SESSION`) and client-side HTTP cookies (`$_COOKIE`).
2. **Implement Secure User Registration**:
   - Validate identity inputs (username format, unique email address, password complexity).
   - Compute salted one-way cryptographic hashes using PHP's native `password_hash()` with the **BCrypt** algorithm (adaptive cost factor 12).
3. **Establish Credential Verification & Session Initialization**:
   - Authenticate users via `password_verify()`.
   - Prevent **Session Fixation attacks** by regenerating session identifiers via `session_regenerate_id(true)` immediately upon authentication.
4. **Implement Persistent "Remember Me" Architecture**:
   - Replace insecure cookie designs with the **Split-Token Persistent Cookie Pattern** (Paragon Initiative / RFC 6265 standard).
   - Use a public `selector` (12-byte random hex) and a private `validator` (32-byte random hex hashed in the database with SHA-256) to resist database compromise and timing attacks.
5. **Session Lifecycle Governance & Inactivity Expiry**:
   - Enforce an automatic sliding inactivity timeout (30 minutes) to auto-terminate abandoned sessions.
   - Harden session cookies with `HttpOnly`, `SameSite=Lax`, and `Secure` attributes to neutralize Cross-Site Scripting (XSS) cookie theft.
6. **Robust Multi-Layer Logout Mechanism**:
   - Invalidate server-side session data (`$_SESSION = []`, `session_destroy()`).
   - Clear client session cookies with historical expiration timestamps (`time() - 3600`).
   - Revoke persistent database tokens to prevent replay attacks.
7. **Security Auditing & Interactive UI**:
   - Maintain an immutable `auth_logs` table recording all authentication attempts with IP addresses and user agents.
   - Design a glassmorphic user dashboard featuring live session telemetry, real-time inactivity countdown timers, and quick evaluator demo logins.

---

## 3. Theoretical Foundations

### 3.1 HTTP Statelessness & The Need for Sessions
The Hypertext Transfer Protocol (HTTP) is inherently stateless: every request from a client is executed independently with no built-in knowledge of preceding transactions. To maintain user identity across disparate page requests (e.g., browsing courses after logging in), the application layer must maintain state.

Two primary mechanisms enable stateful interaction:
1. **Cookies (Client-Side)**: Small key-value data fragments sent by the server via `Set-Cookie` response headers and echoed back by the browser in subsequent `Cookie` request headers.
2. **Sessions (Server-Side)**: Server-managed state containers mapped to a unique client identifier (usually `PHPSESSID`) transmitted through an HTTP-only session cookie.

```
+---------------------------------------------------------------------------------+
|                               HTTP REQUEST / RESPONSE                           |
+---------------------------------------------------------------------------------+
Client Browser                                                   PHP Web Server
      |                                                                 |
      | 1. POST /login (username, password)                            |
      | --------------------------------------------------------------> |
      |                                                                 | Verifies password
      |                                                                 | Calls session_regenerate_id(true)
      |                                                                 | Stores user in $_SESSION
      | 2. HTTP 200 OK + Set-Cookie: PHPSESSID=k3flu7...; HttpOnly      |
      | <-------------------------------------------------------------- |
      |                                                                 |
      | 3. GET /dashboard + Cookie: PHPSESSID=k3flu7...                 |
      | --------------------------------------------------------------> |
      |                                                                 | Looks up $_SESSION['user']
      |                                                                 | Checks inactivity timeout
      | 4. HTTP 200 OK (Authenticated Dashboard Payload)               |
      | <-------------------------------------------------------------- |
```

### 3.2 Session Fixation & Hijacking Vulnerabilities
- **Session Fixation**: An attacker tricks a victim into authenticating using a pre-determined session ID (e.g., via a link like `http://example.com/?PHPSESSID=attacker_id`). If the server does not regenerate the session ID upon login, the attacker uses the same ID to access the victim's account.
  - **Mitigation**: Calling `session_regenerate_id(true)` replaces the existing session identifier with a freshly generated cryptographic token and deletes the old session record.
- **Session Hijacking (XSS Cookie Theft)**: Malicious JavaScript injected via Cross-Site Scripting reads `document.cookie` and exfiltrates the session token.
  - **Mitigation**: Setting `session.cookie_httponly = 1` forbids JavaScript access to the cookie via Document Object Model APIs.

### 3.3 Cookie Security Attributes
A secure cookie header must include defensive flags:
```http
Set-Cookie: PHPSESSID=k3flu7sr3858ei0bg7pmfg0mmq; path=/; HttpOnly; SameSite=Lax; Secure
```
- **`HttpOnly`**: Blocks client-side JavaScript execution (`document.cookie`), mitigating XSS-driven token theft.
- **`SameSite=Lax`**: Restricts cookie transmission on cross-origin requests, defending against Cross-Site Request Forgery (CSRF).
- **`Secure`**: Ensures cookies are only transmitted across encrypted TLS/HTTPS channels.
- **`Path=/`**: Restricts the cookie scope to authorized application routes.

### 3.4 Persistent "Remember Me" via the Split-Token Pattern
Naive implementations often store usernames or reversible tokens in cookies, which are prone to tampering and timing attacks. Experiment 7 utilizes the industry-standard **Split-Token Persistent Cookie Pattern**:

1. Generate a 12-byte random hex string: **`selector`** (used solely for database index lookups).
2. Generate a 32-byte random hex string: **`validator`** (the secret key).
3. Hash the validator using SHA-256: `validator_hash = hash('sha256', validator)`.
4. Store in database: `(user_id, selector, validator_hash, expires_at)`.
5. Issue cookie: `remember_me = selector:validator`.
6. On returning requests: Look up the token by `selector`, then verify using constant-time comparison `hash_equals(validator_hash, hash('sha256', validator))`.
7. Once verified, rotate the token (single-use token rotation) to protect against replay attacks.

### 3.5 Password Cryptography (BCrypt)
Passwords must never be stored in plaintext or reversible encryption. Experiment 7 uses **BCrypt** via PHP's `password_hash()` and `password_verify()`:
- Incorporates a cryptographically strong 16-byte random salt automatically.
- Utilizes the Eksblowfish key schedule with an adjustable work factor (cost = 12), ensuring resistance against FPGA and GPU brute-force cracking.

---

## 4. System Architecture & Workflows

### 4.1 Authentication & Session Workflows

```mermaid
sequenceDiagram
    autonumber
    actor User as Client Browser
    participant Auth as AuthManager (PHP)
    participant DB as AuthDB (MySQL/SQLite)

    Note over User, DB: Scenario A: User Login & Session Establishment
    User->>Auth: POST /api.php?action=login (identifier, password, remember_me, csrf)
    Auth->>Auth: Validate CSRF Token
    Auth->>DB: Query user by username or email
    DB-->>Auth: Return user record & password_hash
    Auth->>Auth: password_verify(password, password_hash)
    alt Password Valid
        Auth->>Auth: session_regenerate_id(true)
        Auth->>Auth: Set $_SESSION['user'] & last_activity
        opt Remember Me Checked
            Auth->>DB: Store (selector, hash(validator), expires_at)
            Auth-->>User: Set-Cookie: lumina_remember_token=selector:validator (HttpOnly)
        end
        Auth->>DB: Log LOGIN_SUCCESS in auth_logs
        Auth-->>User: JSON 200 (Success, user profile)
    else Password Invalid
        Auth->>DB: Increment failed_attempts / Log LOGIN_FAILED
        Auth-->>User: JSON 400 (Invalid credentials)
    end

    Note over User, DB: Scenario B: Secure Logout
    User->>Auth: POST /api.php?action=logout
    Auth->>DB: Revoke remember_tokens in database
    Auth-->>User: Set-Cookie: lumina_remember_token (Expired)
    Auth-->>User: Set-Cookie: PHPSESSID (Expired)
    Auth->>Auth: $_SESSION = []; session_destroy()
    Auth-->>User: JSON 200 (Logged out successfully)
```

---

## 5. Implementation Code Structure

### 5.1 Configuration & Hardening (`config.php`)
[config.php](file:///c:/Users/Mani/Desktop/adv%20web%20tech%20repo/exp7/config.php) initializes secure session cookie parameters (`HttpOnly`, `SameSite=Lax`, strict mode) and defines database connection settings.

### 5.2 Resilient Database Driver (`db.php`)
[db.php](file:///c:/Users/Mani/Desktop/adv%20web%20tech%20repo/exp7/db.php) manages MySQL database creation, user creation with BCrypt hashes, token persistence, and audit logging with automatic SQLite fallback.

### 5.3 Authentication Manager (`auth.php`)
[auth.php](file:///c:/Users/Mani/Desktop/adv%20web%20tech%20repo/exp7/auth.php) implements login, registration, password verification, session fixation prevention, split-token cookie issuance, and session destruction.

### 5.4 RESTful API Controller (`api.php`)
[api.php](file:///c:/Users/Mani/Desktop/adv%20web%20tech%20repo/exp7/api.php) exposes JSON endpoints for login, registration, logout, telemetry status, audit logs, and one-click evaluator demo accounts.

### 5.5 Visual Interface & Telemetry Dashboard (`index.php`)
[index.php](file:///c:/Users/Mani/Desktop/adv%20web%20tech%20repo/exp7/index.php) provides the dual-state interface: a modern glassmorphic login/registration card when unauthenticated, and a full session governance dashboard when authenticated.

---

## 6. Execution and Verification Guide

### 6.1 Running with PHP Development Server
1. Open PowerShell and navigate to `exp7`:
   ```powershell
   cd "c:\Users\Mani\Desktop\adv web tech repo\exp7"
   ```
2. Start the PHP server on port 8001:
   ```powershell
   C:\Users\Mani\php\php.exe -S 127.0.0.1:8001
   ```
3. Open your browser and navigate to:
   ```
   http://127.0.0.1:8001
   ```

### 6.2 Testing Scenarios
1. **Evaluator 1-Click Fast Login**: Click **👑 Admin (Manish Singh)** or **🎓 Student (Aarav)** to instantly log in and inspect the session telemetry.
2. **New User Registration**: Switch to the **Register Account** tab, enter details, and observe the live password strength meter. Upon submission, the account is created and automatically logged in.
3. **Session Inactivity Countdown**: Observe the live countdown timer under **Active Session Telemetry** on the dashboard.
4. **Remember Me Persistence**: Check the "Remember me" box during login. Open browser DevTools (F12) -> Application -> Cookies to verify that the `lumina_remember_token` cookie is present with the `HttpOnly` flag.
5. **Secure Logout**: Click **Secure Logout**; observe that the session is destroyed, the cookie is expired, and the user is redirected to the login portal.

---

## 7. Viva Voce Questions & Answers

**Q1: What is the purpose of `session_regenerate_id(true)` in authentication workflows?**  
**Ans:** It replaces the current session ID with a new cryptographically random ID and deletes the previous session record on the server. This prevents Session Fixation attacks where an attacker pre-allocates a session ID to hijack the authenticated session.

**Q2: Why should the `HttpOnly` flag always be enabled on session cookies?**  
**Ans:** The `HttpOnly` flag instructs the browser that the cookie must not be accessible via client-side scripts (`document.cookie`). This mitigates session token theft resulting from Cross-Site Scripting (XSS) vulnerabilities.

**Q3: How does the Split-Token Persistent Cookie pattern improve security over storing user IDs in cookies?**  
**Ans:** Storing raw user IDs allows trivial identity impersonation. The Split-Token pattern separates the cookie into a public `selector` (used to index the record) and a random `validator`. The database only stores a SHA-256 hash of the validator, preventing an attacker who gains read access to the database from impersonating users.

**Q4: What is the difference between `session_unset()` and `session_destroy()`?**  
**Ans:** `session_unset()` (or `$_SESSION = []`) clears all session variables in memory for the current script execution. `session_destroy()` destroys all session data persisted in the server's storage handler (e.g., disk session file). A secure logout must perform both, along with expiring the client-side session cookie.

**Q5: Why is MD5 or SHA-256 insufficient for password storage, and why is BCrypt preferred?**  
**Ans:** MD5 and SHA-256 are general-purpose cryptographic hash functions optimized for speed. Modern GPUs can calculate billions of SHA-256 hashes per second, making brute-force and dictionary attacks trivial. BCrypt is a key derivation function that includes an adaptive cost parameter (work factor) and automatic random salting, intentionally introducing controlled computational overhead that renders mass cracking infeasible.

---

## 8. Conclusion
Experiment 7 was completed successfully. A secure, production-ready User Authentication System was developed using PHP Sessions and Cookies. The application implements BCrypt password hashing, session fixation prevention, persistent split-token cookies, sliding inactivity expiry, CSRF validation, and a multi-step logout mechanism.
