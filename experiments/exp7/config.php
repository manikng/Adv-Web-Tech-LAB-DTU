<?php
/**
 * Experiment 7: User Authentication using Sessions and Cookies
 * Configuration File & Security Headers
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies (AWT)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

// Database Settings (MySQL Primary)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'exp7_auth_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// SQLite Resilient Fallback Storage
define('SQLITE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'exp7_auth.sqlite');

// Session & Cookie Hardening Parameters
define('SESSION_LIFETIME', 1800); // 30 minutes inactivity timeout
define('REMEMBER_ME_DAYS', 30);    // 30 days remember me token lifetime
define('COOKIE_REMEMBER_NAME', 'lumina_remember_token');

// Application Metadata
define('APP_NAME', 'Lumina Guard • AuthEngine');
define('APP_SUBTITLE', 'Enterprise Identity & Session Governance Platform');
define('APP_VERSION', '3.1.0');
define('STUDENT_NAME', 'Manish Singh');
define('ROLL_NUMBER', '23/CS/244');
define('SUBJECT_NAME', 'Advanced Web Technologies');
define('EXPERIMENT_NO', '07');

/**
 * Configure and start secure PHP session with strict cookies
 */
function initSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        session_set_cookie_params([
            'lifetime' => 0,             // Session cookie expires when browser is closed
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,      // Transmit only over HTTPS if available
            'httponly' => true,          // Mitigate XSS attacks by disallowing JS access
            'samesite' => 'Lax',         // Mitigate Cross-Site Request Forgery (CSRF)
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        session_start();
    }
}
