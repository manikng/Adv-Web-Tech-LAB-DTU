<?php
/**
 * Experiment 7: User Authentication using Sessions and Cookies
 * Main Application View & Auth Dashboard
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$auth = AuthManager::getInstance();
$db = AuthDB::getInstance();

$isAuth = $auth->isAuthenticated();
$currentUser = $auth->getCurrentUser();
$sessionInfo = $auth->getSessionInfo();
$csrfToken = $auth->getCsrfToken();
$dbStatus = $db->getConnectionStatus();
$recentLogs = $isAuth ? $db->getRecentLogs((int)$currentUser['id'], 8) : [];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumina Guard • User Authentication with Sessions & Cookies | Experiment 07</title>
  <meta name="description" content="Experiment 07: User Authentication using Sessions and Cookies. Designed by Manish Singh (23/CS/244) with BCrypt hashing, split-token persistent cookies, session fixation defense, and CSRF governance.">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220%22%200%22%20100%22%20100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
</head>
<body>
  <div class="app-wrapper">
    <!-- Navbar -->
    <header class="navbar" id="main-navbar">
      <a href="index.php" class="brand" id="brand-link" aria-label="Lumina Guard Home">
        <div class="brand-icon">🛡️</div>
        <div class="brand-text">
          <h1><?= htmlspecialchars(APP_NAME) ?></h1>
          <span class="tagline">Exp 07 • Authentication via Sessions & Persistent Cookies</span>
        </div>
      </a>

      <div class="nav-actions">
        <!-- DB Engine Status Pill -->
        <div class="db-status-pill" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: var(--radius-full); background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); color: var(--text-secondary);">
          <span><?= $dbStatus['active_driver'] === 'mysql' ? '🟢 MySQL Database' : '🟡 SQLite Sandbox' ?></span>
        </div>

        <!-- Dark/Light Mode Switcher -->
        <button class="btn btn-secondary btn-icon" id="theme-toggle" aria-label="Toggle Theme">
          ☀️
        </button>

        <?php if ($isAuth): ?>
          <button class="btn btn-danger" id="logout-btn">
            <span>🔒</span> Secure Logout
          </button>
        <?php endif; ?>
      </div>
    </header>

    <main>
      <?php if (!$isAuth): ?>
        <!-- =============================================================
             UNAUTHENTICATED: SLEEK AUTHENTICATION PORTAL
             ============================================================= -->
        <div class="auth-container">
          <div class="auth-card">
            <div class="auth-header">
              <div class="auth-badge">Security Hardened</div>
              <h2 class="auth-title">Identity & Access Portal</h2>
              <p class="auth-subtitle">Academic Session Governance & Persistent Cookie Architecture</p>
            </div>

            <!-- Tab Switcher -->
            <div class="auth-tabs" role="tablist">
              <button class="tab-btn active" id="tab-login" role="tab">Sign In</button>
              <button class="tab-btn" id="tab-register" role="tab">Register Account</button>
            </div>

            <!-- Form 1: Login Form -->
            <form id="login-form">
              <input type="hidden" id="login-csrf" value="<?= htmlspecialchars($csrfToken) ?>">

              <div class="form-group">
                <label for="login-identifier">Username or Institutional Email</label>
                <input type="text" id="login-identifier" class="form-control" placeholder="e.g. manish or manish.singhdtu.ac.in" required autofocus>
              </div>

              <div class="form-group">
                <label for="login-password">Master Password</label>
                <div class="input-wrapper">
                  <input type="password" id="login-password" class="form-control" placeholder="Enter password" required>
                  <button type="button" class="password-toggle" data-target="login-password" aria-label="Toggle Password Visibility">👁️</button>
                </div>
              </div>

              <div class="remember-row">
                <label class="checkbox-label">
                  <input type="checkbox" id="login-remember">
                  <span>Remember me on this workstation (30 Days Cookie)</span>
                </label>
              </div>

              <button type="submit" class="btn btn-primary" id="login-submit-btn" style="width: 100%;">
                Authenticate & Open Session
              </button>
            </form>

            <!-- Form 2: Register Form -->
            <form id="register-form" style="display: none;">
              <input type="hidden" id="reg-csrf" value="<?= htmlspecialchars($csrfToken) ?>">

              <div class="form-group">
                <label for="reg-fullname">Full Legal Name *</label>
                <input type="text" id="reg-fullname" class="form-control" placeholder="e.g. Priyanshu Sharma" required>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="reg-username">Username *</label>
                  <input type="text" id="reg-username" class="form-control" placeholder="e.g. priyanshu" required>
                </div>
                <div class="form-group">
                  <label for="reg-role">Account Role</label>
                  <select id="reg-role" class="form-control">
                    <option value="student">Student Scholar</option>
                    <option value="faculty">Academic Faculty</option>
                    <option value="admin">System Admin</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="reg-email">Institutional Email *</label>
                <input type="email" id="reg-email" class="form-control" placeholder="e.g. p.sharmadtu.ac.in" required>
              </div>

              <div class="form-group">
                <label for="reg-department">Department</label>
                <select id="reg-department" class="form-control">
                  <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                  <option value="Information Technology">Information Technology</option>
                  <option value="Artificial Intelligence">Artificial Intelligence</option>
                </select>
              </div>

              <div class="form-group">
                <label for="reg-password">Create Strong Password *</label>
                <div class="input-wrapper">
                  <input type="password" id="reg-password" class="form-control" placeholder="Minimum 8 characters" required>
                  <button type="button" class="password-toggle" data-target="reg-password" aria-label="Toggle Password Visibility">👁️</button>
                </div>
                <div class="strength-meter">
                  <div class="strength-bars">
                    <div class="strength-bar" id="bar-1"></div>
                    <div class="strength-bar" id="bar-2"></div>
                    <div class="strength-bar" id="bar-3"></div>
                  </div>
                  <span class="strength-label" id="strength-text">Password strength</span>
                </div>
              </div>

              <button type="submit" class="btn btn-primary" id="register-submit-btn" style="width: 100%;">
                Create Account & Establish Session
              </button>
            </form>

            <!-- Evaluator Quick Demo Accounts -->
            <div class="quick-demo-section">
              <div class="quick-demo-title">⚡ Evaluator 1-Click Fast Login</div>
              <div class="quick-demo-grid">
                <button type="button" class="demo-account-btn" data-role="admin">
                  👑 Admin<br><small style="color: var(--text-muted); font-size: 0.65rem;">(Manish Singh)</small>
                </button>
                <button type="button" class="demo-account-btn" data-role="student">
                  🎓 Student<br><small style="color: var(--text-muted); font-size: 0.65rem;">(Aarav)</small>
                </button>
                <button type="button" class="demo-account-btn" data-role="faculty">
                  🔬 Faculty<br><small style="color: var(--text-muted); font-size: 0.65rem;">(Prof. Sneha)</small>
                </button>
              </div>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- =============================================================
             AUTHENTICATED: MODERN IDENTITY & SESSION DASHBOARD
             ============================================================= -->
        <div class="dashboard-grid">
          <!-- Left Column: User Profile & Session Governance -->
          <div>
            <!-- Profile Card -->
            <div class="card profile-card">
              <div class="profile-avatar" style="background: <?= htmlspecialchars($currentUser['avatar_color']) ?>;">
                <?= htmlspecialchars(strtoupper(substr($currentUser['full_name'], 0, 2))) ?>
              </div>
              <h2 class="profile-name"><?= htmlspecialchars($currentUser['full_name']) ?></h2>
              <p class="profile-email">@<?= htmlspecialchars($currentUser['username']) ?> • <?= htmlspecialchars($currentUser['email']) ?></p>
              <div>
                <span class="role-badge role-<?= htmlspecialchars($currentUser['role']) ?>">
                  <?= htmlspecialchars($currentUser['role']) ?>
                </span>
              </div>
              <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.75rem;">
                <?= htmlspecialchars($currentUser['department']) ?>
              </p>
            </div>

            <!-- Session Telemetry Widget -->
            <div class="card">
              <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem;">🔐 Active Session Telemetry</h3>
              
              <div class="telemetry-row">
                <span class="telemetry-label">PHP Session ID:</span>
                <span class="telemetry-val" style="font-size: 0.72rem;"><?= htmlspecialchars(substr($sessionInfo['session_id'], 0, 16)) ?>...</span>
              </div>
              <div class="telemetry-row">
                <span class="telemetry-label">Auth Method:</span>
                <span class="telemetry-val" style="text-transform: capitalize; color: #34d399;"><?= htmlspecialchars(str_replace('_', ' ', $sessionInfo['auth_method'])) ?></span>
              </div>
              <div class="telemetry-row">
                <span class="telemetry-label">Remember Cookie:</span>
                <span class="telemetry-val" style="color: <?= $sessionInfo['has_remember'] ? '#34d399' : 'var(--text-muted)' ?>;">
                  <?= $sessionInfo['has_remember'] ? 'Active (30 Days)' : 'None (Session Only)' ?>
                </span>
              </div>
              <div class="telemetry-row">
                <span class="telemetry-label">Session Initiated:</span>
                <span class="telemetry-val"><?= htmlspecialchars($sessionInfo['login_time']) ?></span>
              </div>
              <div class="telemetry-row">
                <span class="telemetry-label">Cookie Flags:</span>
                <span class="telemetry-val" style="font-size: 0.72rem; color: #818cf8;">HttpOnly • SameSite=Lax</span>
              </div>

              <!-- Live Countdown Box -->
              <div class="countdown-box">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Inactivity Auto-Logout Countdown</div>
                <div class="countdown-time" id="session-countdown" data-seconds="<?= $sessionInfo['time_remaining'] ?>">
                  <?= sprintf('%02d:%02d', floor($sessionInfo['time_remaining'] / 60), $sessionInfo['time_remaining'] % 60) ?>
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Refreshes automatically upon user interaction</div>
              </div>
            </div>
          </div>

          <!-- Right Column: Security Audits & Technical Explanation -->
          <div>
            <!-- Technical Overview Card -->
            <div class="card">
              <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">State Management & Security Mechanics</h3>
              <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                This application fulfills all Experiment 07 requirements by utilizing an enterprise authentication architecture:
              </p>
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.82rem;">
                <div style="background: rgba(255,255,255,0.03); padding: 0.85rem; border-radius: 8px; border: 1px solid var(--border-color);">
                  <strong style="color: #818cf8;">1. Session Fixation Defense</strong>
                  <p style="color: var(--text-secondary); margin-top: 0.25rem;">
                    Calls <code>session_regenerate_id(true)</code> on login to immediately invalidate pre-auth session identifiers.
                  </p>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 0.85rem; border-radius: 8px; border: 1px solid var(--border-color);">
                  <strong style="color: #34d399;">2. BCrypt Cryptographic Hashes</strong>
                  <p style="color: var(--text-secondary); margin-top: 0.25rem;">
                    Passwords are salted and hashed using <code>password_hash(PASSWORD_BCRYPT)</code> with adaptive cost factor 12.
                  </p>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 0.85rem; border-radius: 8px; border: 1px solid var(--border-color);">
                  <strong style="color: #f472b6;">3. Split-Token Persistent Cookie</strong>
                  <p style="color: var(--text-secondary); margin-top: 0.25rem;">
                    Remember-Me uses a 12-byte selector + 32-byte validator hashed via SHA-256 in DB, defeating timing attacks.
                  </p>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 0.85rem; border-radius: 8px; border: 1px solid var(--border-color);">
                  <strong style="color: #f59e0b;">4. Comprehensive Logout</strong>
                  <p style="color: var(--text-secondary); margin-top: 0.25rem;">
                    Erases <code>$_SESSION</code>, terminates cookie in browser with expired timestamp, and deletes DB token.
                  </p>
                </div>
              </div>
            </div>

            <!-- Security Audit Trail -->
            <div class="card">
              <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.25rem;">Recent Security & Auth Activity Trail</h3>
              <p style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 1rem;">Immutable audit log of recent authentication transactions for this identity:</p>

              <div style="overflow-x: auto;">
                <table class="audit-table">
                  <thead>
                    <tr>
                      <th>Event Action</th>
                      <th>IP Address</th>
                      <th>User Agent</th>
                      <th>Timestamp</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($recentLogs)): ?>
                      <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">No audit logs recorded yet.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($recentLogs as $log): ?>
                        <tr>
                          <td>
                            <span class="action-pill action-<?= htmlspecialchars($log['action']) ?>">
                              <?= htmlspecialchars($log['action']) ?>
                            </span>
                          </td>
                          <td style="font-family: 'JetBrains Mono', monospace;"><?= htmlspecialchars($log['ip_address']) ?></td>
                          <td style="color: var(--text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($log['user_agent']) ?>">
                            <?= htmlspecialchars($log['user_agent']) ?>
                          </td>
                          <td style="color: var(--text-secondary);"><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- Toast Notification Stack -->
  <div class="toast-container" id="toast-container"></div>

  <script src="assets/app.js"></script>
</body>
</html>
