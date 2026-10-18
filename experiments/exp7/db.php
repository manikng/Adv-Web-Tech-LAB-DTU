<?php
/**
 * Experiment 7: User Authentication using Sessions and Cookies
 * Resilient Database Engine (MySQL with SQLite Fallback)
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class AuthDB {
    private static ?AuthDB $instance = null;
    private PDO $pdo;
    private string $activeDriver = 'unknown';
    private bool $isFallback = false;
    private ?string $mysqlError = null;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance(): AuthDB {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void {
        if (extension_loaded('pdo_mysql')) {
            try {
                $serverDsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
                $initPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 2,
                ]);

                $initPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                $dbDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
                $this->pdo = new PDO($dbDsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                $this->activeDriver = 'mysql';
                $this->isFallback = false;
                $this->initSchemaMysql();
                return;
            } catch (Throwable $e) {
                $this->mysqlError = $e->getMessage();
            }
        } else {
            $this->mysqlError = "Extension 'pdo_mysql' is not loaded.";
        }

        $this->initSqliteFallback();
    }

    private function initSqliteFallback(): void {
        $dataDir = dirname(SQLITE_PATH);
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0777, true);
        }

        $this->pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON;');
        $this->activeDriver = 'sqlite';
        $this->isFallback = true;
        $this->initSchemaSqlite();
    }

    private function initSchemaMysql(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `username` VARCHAR(64) NOT NULL,
              `email` VARCHAR(191) NOT NULL,
              `password_hash` VARCHAR(255) NOT NULL,
              `full_name` VARCHAR(128) NOT NULL,
              `role` VARCHAR(32) NOT NULL DEFAULT 'student',
              `department` VARCHAR(64) NOT NULL DEFAULT 'Computer Science & Engineering',
              `avatar_color` VARCHAR(32) NOT NULL DEFAULT '#6366f1',
              `failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
              `locked_until` DATETIME NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY `uk_username` (`username`),
              UNIQUE KEY `uk_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `remember_tokens` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT UNSIGNED NOT NULL,
              `selector` VARCHAR(32) NOT NULL,
              `validator_hash` VARCHAR(64) NOT NULL,
              `expires_at` DATETIME NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              UNIQUE KEY `uk_selector` (`selector`),
              KEY `idx_user_token` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `auth_logs` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT UNSIGNED NULL,
              `action` VARCHAR(64) NOT NULL,
              `ip_address` VARCHAR(45) NOT NULL,
              `user_agent` TEXT NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $this->ensureSeedUsers();
    }

    private function initSchemaSqlite(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              username TEXT UNIQUE NOT NULL,
              email TEXT UNIQUE NOT NULL,
              password_hash TEXT NOT NULL,
              full_name TEXT NOT NULL,
              role TEXT NOT NULL DEFAULT 'student',
              department TEXT NOT NULL DEFAULT 'Computer Science & Engineering',
              avatar_color TEXT NOT NULL DEFAULT '#6366f1',
              failed_attempts INTEGER NOT NULL DEFAULT 0,
              locked_until DATETIME NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS remember_tokens (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              user_id INTEGER NOT NULL,
              selector TEXT UNIQUE NOT NULL,
              validator_hash TEXT NOT NULL,
              expires_at DATETIME NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS auth_logs (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              user_id INTEGER NULL,
              action TEXT NOT NULL,
              ip_address TEXT NOT NULL,
              user_agent TEXT NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $this->ensureSeedUsers();
    }

    private function ensureSeedUsers(): void {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS count FROM users');
        $row = $stmt->fetch();
        if ($row && (int)$row['count'] === 0) {
            $defaultHash = password_hash('Password@123', PASSWORD_BCRYPT);

            $seeds = [
                [
                    'username' => 'manish',
                    'email' => 'manish.singhdtu.ac.in',
                    'password_hash' => $defaultHash,
                    'full_name' => 'Manish Singh',
                    'role' => 'admin',
                    'department' => 'Computer Science & Engineering',
                    'avatar_color' => '#6366f1'
                ],
                [
                    'username' => 'aarav',
                    'email' => 'aarav.sharmadtu.ac.in',
                    'password_hash' => $defaultHash,
                    'full_name' => 'Aarav Sharma',
                    'role' => 'student',
                    'department' => 'Computer Science & Engineering',
                    'avatar_color' => '#10b981'
                ],
                [
                    'username' => 'sneha',
                    'email' => 'prof.snehadtu.ac.in',
                    'password_hash' => $defaultHash,
                    'full_name' => 'Prof. Sneha Sen',
                    'role' => 'faculty',
                    'department' => 'Artificial Intelligence',
                    'avatar_color' => '#ec4899'
                ]
            ];

            foreach ($seeds as $u) {
                $ins = $this->pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, role, department, avatar_color) VALUES (:username, :email, :pass, :name, :role, :dept, :color)');
                $ins->execute([
                    ':username' => $u['username'],
                    ':email'    => $u['email'],
                    ':pass'     => $u['password_hash'],
                    ':name'     => $u['full_name'],
                    ':role'     => $u['role'],
                    ':dept'     => $u['department'],
                    ':color'    => $u['avatar_color'],
                ]);
            }
        }
    }

    // =========================================================================
    // USER METHODS
    // =========================================================================

    public function findUserById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT id, username, email, full_name, role, department, avatar_color, failed_attempts, locked_until, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public function findUserWithPassword(string $identifier): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :ident OR email = :ident LIMIT 1');
        $stmt->execute([':ident' => $identifier]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public function createUser(string $username, string $email, string $password, string $fullName, string $role = 'student', string $department = 'Computer Science & Engineering'): array {
        // Check uniqueness
        $check = $this->pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
        $check->execute([':u' => $username, ':e' => $email]);
        if ($check->fetch()) {
            throw new RuntimeException("Username or email already exists in the system.");
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $colors = ['#6366f1', '#10b981', '#ec4899', '#f59e0b', '#06b6d4', '#8b5cf6'];
        $color = $colors[array_rand($colors)];

        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password_hash, full_name, role, department, avatar_color, created_at) VALUES (:u, :e, :p, :n, :r, :d, :c, CURRENT_TIMESTAMP)');
        $stmt->execute([
            ':u' => $username,
            ':e' => $email,
            ':p' => $hash,
            ':n' => $fullName,
            ':r' => $role,
            ':d' => $department,
            ':c' => $color,
        ]);

        $newId = (int)$this->pdo->lastInsertId();
        return $this->findUserById($newId);
    }

    public function updateFailedAttempts(int $id, int $count, ?string $lockedUntil = null): void {
        $stmt = $this->pdo->prepare('UPDATE users SET failed_attempts = :c, locked_until = :l WHERE id = :id');
        $stmt->execute([':c' => $count, ':l' => $lockedUntil, ':id' => $id]);
    }

    // =========================================================================
    // REMEMBER ME TOKENS
    // =========================================================================

    public function createRememberToken(int $userId, string $selector, string $validatorHash, string $expiresAt): void {
        $stmt = $this->pdo->prepare('INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) VALUES (:u, :s, :v, :e)');
        $stmt->execute([
            ':u' => $userId,
            ':s' => $selector,
            ':v' => $validatorHash,
            ':e' => $expiresAt,
        ]);
    }

    public function findRememberToken(string $selector): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM remember_tokens WHERE selector = :s AND expires_at > CURRENT_TIMESTAMP LIMIT 1');
        $stmt->execute([':s' => $selector]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteRememberToken(string $selector): void {
        $stmt = $this->pdo->prepare('DELETE FROM remember_tokens WHERE selector = :s');
        $stmt->execute([':s' => $selector]);
    }

    public function deleteAllUserRememberTokens(int $userId): void {
        $stmt = $this->pdo->prepare('DELETE FROM remember_tokens WHERE user_id = :u');
        $stmt->execute([':u' => $userId]);
    }

    // =========================================================================
    // AUDIT LOGS
    // =========================================================================

    public function logAuthEvent(?int $userId, string $action, string $ip, string $userAgent): void {
        $stmt = $this->pdo->prepare('INSERT INTO auth_logs (user_id, action, ip_address, user_agent, created_at) VALUES (:u, :a, :i, :ua, CURRENT_TIMESTAMP)');
        $stmt->execute([
            ':u'  => $userId,
            ':a'  => $action,
            ':i'  => $ip,
            ':ua' => substr($userAgent, 0, 255),
        ]);
    }

    public function getRecentLogs(?int $userId = null, int $limit = 10): array {
        if ($userId !== null) {
            $stmt = $this->pdo->prepare('SELECT * FROM auth_logs WHERE user_id = :u ORDER BY id DESC LIMIT ' . (int)$limit);
            $stmt->execute([':u' => $userId]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM auth_logs ORDER BY id DESC LIMIT ' . (int)$limit);
        }
        return $stmt->fetchAll();
    }

    public function getAllUsers(): array {
        $stmt = $this->pdo->query('SELECT id, username, email, full_name, role, department, avatar_color, created_at FROM users ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public function getConnectionStatus(): array {
        return [
            'active_driver'  => $this->activeDriver,
            'is_fallback'    => $this->isFallback,
            'mysql_host'     => DB_HOST . ':' . DB_PORT,
            'mysql_database' => DB_NAME,
            'mysql_error'    => $this->mysqlError,
            'sqlite_file'    => SQLITE_PATH,
        ];
    }
}
