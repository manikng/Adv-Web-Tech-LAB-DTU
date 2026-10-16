<?php
/**
 * Experiment 7: User Authentication using Sessions and Cookies
 * Core Authentication Manager & Security Governance
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class AuthManager {
    private static ?AuthManager $instance = null;
    private AuthDB $db;

    private function __construct() {
        initSecureSession();
        $this->db = AuthDB::getInstance();
        $this->checkSessionExpiry();
        $this->checkRememberMe();
    }

    public static function getInstance(): AuthManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate or retrieve CSRF token stored in session
     */
    public function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token using constant-time comparison
     */
    public function validateCsrfToken(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if current session has expired due to inactivity
     */
    private function checkSessionExpiry(): void {
        if (isset($_SESSION['user']) && isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - (int)$_SESSION['last_activity'];
            if ($inactiveTime > SESSION_LIFETIME) {
                // Session expired
                $this->db->logAuthEvent(
                    (int)($_SESSION['user']['id'] ?? null),
                    'SESSION_TIMEOUT',
                    $this->getClientIp(),
                    $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                );
                $this->logout();
                return;
            }
        }
        // Refresh activity timestamp
        if (isset($_SESSION['user'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * Split-Token persistent cookie handler: auto-authenticates returning user
     */
    private function checkRememberMe(): void {
        if (isset($_SESSION['user'])) {
            return; // Already authenticated
        }

        if (empty($_COOKIE[COOKIE_REMEMBER_NAME])) {
            return;
        }

        $cookieVal = $_COOKIE[COOKIE_REMEMBER_NAME];
        $parts = explode(':', $cookieVal, 2);
        if (count($parts) !== 2) {
            $this->clearRememberCookie();
            return;
        }

        [$selector, $validator] = $parts;
        $token = $this->db->findRememberToken($selector);

        if (!$token) {
            $this->clearRememberCookie();
            return;
        }

        // Constant-time hash verification
        $calculatedHash = hash('sha256', $validator);
        if (!hash_equals($token['validator_hash'], $calculatedHash)) {
            // Possible token tampering or theft
            $this->db->deleteAllUserRememberTokens((int)$token['user_id']);
            $this->clearRememberCookie();
            $this->db->logAuthEvent((int)$token['user_id'], 'REMEMBER_TOKEN_TAMPER_ALERT', $this->getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
            return;
        }

        // Token valid! Retrieve user
        $user = $this->db->findUserById((int)$token['user_id']);
        if (!$user) {
            $this->clearRememberCookie();
            return;
        }

        // Establish session with session regeneration
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['auth_method'] = 'remember_cookie';

        // Rotate token (single-use persistent token pattern)
        $this->db->deleteRememberToken($selector);
        $this->issueRememberMeCookie((int)$user['id']);

        $this->db->logAuthEvent((int)$user['id'], 'AUTO_LOGIN_VIA_COOKIE', $this->getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
    }

    /**
     * User Registration
     */
    public function register(string $username, string $email, string $password, string $fullName, string $role = 'student', string $department = 'Computer Science & Engineering'): array {
        $username = trim($username);
        $email    = strtolower(trim($email));
        $fullName = trim($fullName);

        if (strlen($username) < 3 || strlen($username) > 30) {
            throw new InvalidArgumentException("Username must be between 3 and 30 characters.");
        }
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            throw new InvalidArgumentException("Username may only contain letters, numbers, dashes, underscores, and dots.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Please provide a valid institutional email address.");
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException("Password must be at least 8 characters long.");
        }
        if ($fullName === '') {
            throw new InvalidArgumentException("Full name is required.");
        }

        $user = $this->db->createUser($username, $email, $password, $fullName, $role, $department);

        $this->db->logAuthEvent(
            (int)$user['id'],
            'USER_REGISTERED',
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        return $user;
    }

    /**
     * User Login with password verification and session fixation defense
     */
    public function login(string $identifier, string $password, bool $rememberMe = false): array {
        $identifier = trim($identifier);

        if ($identifier === '' || $password === '') {
            throw new InvalidArgumentException("Username/email and password cannot be blank.");
        }

        $user = $this->db->findUserWithPassword($identifier);

        // Account lock check
        if ($user && !empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remaining = strtotime($user['locked_until']) - time();
            throw new RuntimeException("Account temporarily locked due to consecutive failed attempts. Try again in {$remaining} seconds.");
        }

        // Verify credentials
        if (!$user || !password_verify($password, $user['password_hash'])) {
            if ($user) {
                $newFailed = (int)$user['failed_attempts'] + 1;
                $lockTime = null;
                if ($newFailed >= 5) {
                    $lockTime = date('Y-m-d H:i:s', time() + 300); // 5 min lockout
                }
                $this->db->updateFailedAttempts((int)$user['id'], $newFailed, $lockTime);
                $this->db->logAuthEvent((int)$user['id'], 'LOGIN_FAILED', $this->getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
            } else {
                $this->db->logAuthEvent(null, "LOGIN_FAILED_UNKNOWN:{$identifier}", $this->getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
            }
            throw new RuntimeException("Invalid credentials. Please verify your username/email and password.");
        }

        // Reset failed attempts on success
        $this->db->updateFailedAttempts((int)$user['id'], 0, null);

        // Session fixation protection: regenerate ID
        session_regenerate_id(true);

        // Sanitized session payload
        $safeUser = [
            'id'           => (int)$user['id'],
            'username'     => $user['username'],
            'email'        => $user['email'],
            'full_name'    => $user['full_name'],
            'role'         => $user['role'],
            'department'   => $user['department'],
            'avatar_color' => $user['avatar_color'],
            'created_at'   => $user['created_at'],
        ];

        $_SESSION['user'] = $safeUser;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['auth_method'] = 'password';

        // Remember Me Cookie
        if ($rememberMe) {
            $this->issueRememberMeCookie((int)$user['id']);
        } else {
            $this->clearRememberCookie();
        }

        $this->db->logAuthEvent(
            (int)$user['id'],
            'LOGIN_SUCCESS',
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        return $safeUser;
    }

    /**
     * Issues secure Split-Token Remember-Me Cookie
     */
    private function issueRememberMeCookie(int $userId): void {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $validatorHash = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + (86400 * REMEMBER_ME_DAYS));

        $this->db->createRememberToken($userId, $selector, $validatorHash, $expiresAt);

        $cookieValue = $selector . ':' . $validator;
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        setcookie(COOKIE_REMEMBER_NAME, $cookieValue, [
            'expires'  => time() + (86400 * REMEMBER_ME_DAYS),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Clear and revoke Remember-Me cookie
     */
    public function clearRememberCookie(): void {
        if (!empty($_COOKIE[COOKIE_REMEMBER_NAME])) {
            $parts = explode(':', $_COOKIE[COOKIE_REMEMBER_NAME], 2);
            if (count($parts) === 2) {
                $this->db->deleteRememberToken($parts[0]);
            }
        }
        setcookie(COOKIE_REMEMBER_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[COOKIE_REMEMBER_NAME]);
    }

    /**
     * Secure Logout: destroys session, unsets cookie, and revokes tokens
     */
    public function logout(): void {
        $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

        if ($userId) {
            $this->db->logAuthEvent($userId, 'LOGOUT', $this->getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
        }

        $this->clearRememberCookie();

        // Wipe session superglobal
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // Destroy server session storage
        session_destroy();
    }

    public function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public function isAuthenticated(): bool {
        return isset($_SESSION['user']);
    }

    public function getSessionInfo(): array {
        if (!$this->isAuthenticated()) {
            return ['authenticated' => false];
        }

        $timeRemaining = SESSION_LIFETIME - (time() - (int)($_SESSION['last_activity'] ?? time()));

        return [
            'authenticated'  => true,
            'user'           => $_SESSION['user'],
            'session_id'     => session_id(),
            'login_time'     => date('Y-m-d H:i:s', $_SESSION['login_time'] ?? time()),
            'last_activity'  => date('Y-m-d H:i:s', $_SESSION['last_activity'] ?? time()),
            'time_remaining' => max(0, $timeRemaining),
            'auth_method'    => $_SESSION['auth_method'] ?? 'password',
            'has_remember'   => !empty($_COOKIE[COOKIE_REMEMBER_NAME]),
        ];
    }

    private function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
