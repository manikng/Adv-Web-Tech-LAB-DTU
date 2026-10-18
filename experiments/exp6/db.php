<?php
/**
 * Experiment 6: Database Connectivity and CRUD Operations
 * Resilient Database Engine (PDO MySQL with SQLite Fallback)
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;
    private string $activeDriver = 'unknown';
    private bool $isFallback = false;
    private ?string $mysqlError = null;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Attempts MySQL connection first; gracefully fails over to SQLite if MySQL is offline.
     */
    private function connect(): void {
        // 1. Try MySQL if pdo_mysql extension is loaded
        if (extension_loaded('pdo_mysql')) {
            try {
                // Step A: Connect to MySQL server (without specifying DB first to create it if absent)
                $serverDsn = sprintf(
                    'mysql:host=%s;port=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_CHARSET
                );
                
                $initPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT            => 2, // 2 second timeout for local check
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                ]);

                // Create database if not exists
                $initPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Step B: Connect to the specific database
                $dbDsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );

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
            $this->mysqlError = "The PHP 'pdo_mysql' extension is not enabled.";
        }

        // 2. Resilient Fallback to SQLite
        $this->initSqliteFallback();
    }

    private function initSqliteFallback(): void {
        $dataDir = dirname(SQLITE_PATH);
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0777, true);
        }

        $dsn = 'sqlite:' . SQLITE_PATH;
        $this->pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON;');
        $this->pdo->exec('PRAGMA journal_mode = WAL;');

        $this->activeDriver = 'sqlite';
        $this->isFallback = true;
        $this->initSchemaSqlite();
    }

    private function initSchemaMysql(): void {
        $sql = "CREATE TABLE IF NOT EXISTS `students` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `roll_no` VARCHAR(32) NOT NULL,
            `name` VARCHAR(128) NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `department` VARCHAR(64) NOT NULL DEFAULT 'Computer Science & Engineering',
            `semester` VARCHAR(32) NOT NULL DEFAULT 'Semester 5',
            `maths` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `physics` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `cs` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `web_tech` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `english` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `total_marks` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
            `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `gpa` DECIMAL(3,1) NOT NULL DEFAULT 0.0,
            `grade` VARCHAR(4) NOT NULL DEFAULT 'F',
            `status` VARCHAR(64) NOT NULL DEFAULT 'Enrolled',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_student_roll` (`roll_no`),
            KEY `idx_student_email` (`email`),
            KEY `idx_student_semester` (`semester`),
            KEY `idx_student_grade` (`grade`),
            KEY `idx_student_percentage` (`percentage`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->exec($sql);
        $this->ensureSeedData();
        $this->migrateInstitutionalEmails();
    }

    private function initSchemaSqlite(): void {
        $sql = "CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            roll_no TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            department TEXT NOT NULL DEFAULT 'Computer Science & Engineering',
            semester TEXT NOT NULL DEFAULT 'Semester 5',
            maths REAL NOT NULL DEFAULT 0.0,
            physics REAL NOT NULL DEFAULT 0.0,
            cs REAL NOT NULL DEFAULT 0.0,
            web_tech REAL NOT NULL DEFAULT 0.0,
            english REAL NOT NULL DEFAULT 0.0,
            total_marks REAL NOT NULL DEFAULT 0.0,
            percentage REAL NOT NULL DEFAULT 0.0,
            gpa REAL NOT NULL DEFAULT 0.0,
            grade TEXT NOT NULL DEFAULT 'F',
            status TEXT NOT NULL DEFAULT 'Enrolled',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_students_semester ON students(semester);
        CREATE INDEX IF NOT EXISTS idx_students_grade ON students(grade);
        ";
        $this->pdo->exec($sql);
        $this->ensureSeedData();
        $this->migrateInstitutionalEmails();
    }

    private function migrateInstitutionalEmails(): void {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS app_migrations (version INTEGER PRIMARY KEY)');
        $migration = $this->pdo->query('SELECT version FROM app_migrations WHERE version = 1')->fetch();
        if ($migration) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $students = $this->pdo->query('SELECT id, name, roll_no, email FROM students')->fetchAll();
            $update = $this->pdo->prepare(
                'UPDATE students SET email = :email, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );

            foreach ($students as $student) {
                $email = generateInstitutionalEmail($student['name'], $student['roll_no']);
                if ($student['email'] !== $email) {
                    $update->execute([':email' => $email, ':id' => $student['id']]);
                }
            }

            $this->pdo->exec('INSERT INTO app_migrations (version) VALUES (1)');
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function ensureSeedData(): void {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS count FROM students');
        $row = $stmt->fetch();
        if ($row && (int)$row['count'] === 0) {
            $this->seedBenchmarkData();
        }
    }

    public function seedBenchmarkData(): int {
        $benchmarkRecords = [
            [
                'roll_no' => '23/CS/244',
                'name' => 'Manish Singh',
                'email' => 'manish.singh.23cs244@dtu.ac.in',
                'department' => 'Computer Science & Engineering',
                'semester' => 'Semester 5',
                'maths' => 98.0,
                'physics' => 94.0,
                'cs' => 99.0,
                'web_tech' => 97.0,
                'english' => 92.0,
            ],
            [
                'roll_no' => '23/CS/101',
                'name' => 'Aarav Sharma',
                'email' => 'aarav.sharma.23cs101@dtu.ac.in',
                'department' => 'Computer Science & Engineering',
                'semester' => 'Semester 5',
                'maths' => 92.0,
                'physics' => 88.0,
                'cs' => 94.0,
                'web_tech' => 91.0,
                'english' => 89.0,
            ],
            [
                'roll_no' => '23/CS/102',
                'name' => 'Diya Patel',
                'email' => 'diya.patel.23cs102@dtu.ac.in',
                'department' => 'Information Technology',
                'semester' => 'Semester 5',
                'maths' => 85.0,
                'physics' => 89.0,
                'cs' => 88.0,
                'web_tech' => 92.0,
                'english' => 86.0,
            ],
            [
                'roll_no' => '23/CS/105',
                'name' => 'Rohan Gupta',
                'email' => 'rohan.gupta.23cs105@dtu.ac.in',
                'department' => 'Computer Science & Engineering',
                'semester' => 'Semester 5',
                'maths' => 78.0,
                'physics' => 74.0,
                'cs' => 82.0,
                'web_tech' => 80.0,
                'english' => 76.0,
            ],
            [
                'roll_no' => '23/CS/112',
                'name' => 'Ananya Iyer',
                'email' => 'ananya.iyer.23cs112@dtu.ac.in',
                'department' => 'Artificial Intelligence',
                'semester' => 'Semester 5',
                'maths' => 95.0,
                'physics' => 91.0,
                'cs' => 96.0,
                'web_tech' => 98.0,
                'english' => 90.0,
            ],
            [
                'roll_no' => '23/CS/118',
                'name' => 'Vikram Malhotra',
                'email' => 'vikram.malhotra.23cs118@dtu.ac.in',
                'department' => 'Computer Science & Engineering',
                'semester' => 'Semester 5',
                'maths' => 68.0,
                'physics' => 62.0,
                'cs' => 71.0,
                'web_tech' => 65.0,
                'english' => 64.0,
            ],
            [
                'roll_no' => '23/CS/124',
                'name' => 'Neha Kapoor',
                'email' => 'neha.kapoor.23cs124@dtu.ac.in',
                'department' => 'Information Technology',
                'semester' => 'Semester 5',
                'maths' => 54.0,
                'physics' => 58.0,
                'cs' => 52.0,
                'web_tech' => 59.0,
                'english' => 55.0,
            ],
            [
                'roll_no' => '23/CS/130',
                'name' => 'Kabir Sen',
                'email' => 'kabir.sen.23cs130@dtu.ac.in',
                'department' => 'Computer Science & Engineering',
                'semester' => 'Semester 5',
                'maths' => 38.0,
                'physics' => 42.0,
                'cs' => 35.0,
                'web_tech' => 39.0,
                'english' => 40.0,
            ]
        ];

        $inserted = 0;
        foreach ($benchmarkRecords as $record) {
            try {
                $this->create($record);
                $inserted++;
            } catch (Throwable $e) {
                // Ignore duplicates if any
            }
        }
        return $inserted;
    }

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * CREATE: Insert a new student record
     */
    public function create(array $data): array {
        // Validate required fields
        $rollNo = trim($data['roll_no'] ?? '');
        $name   = trim($data['name'] ?? '');
        $dept   = trim($data['department'] ?? 'Computer Science & Engineering');
        $sem    = trim($data['semester'] ?? 'Semester 5');

        if ($rollNo === '' || $name === '') {
            throw new InvalidArgumentException("Roll Number and Full Name are mandatory fields.");
        }
        $email = generateInstitutionalEmail($name, $rollNo);

        // Check uniqueness of Roll Number
        $checkStmt = $this->pdo->prepare('SELECT id FROM students WHERE roll_no = :roll_no');
        $checkStmt->execute([':roll_no' => $rollNo]);
        if ($checkStmt->fetch()) {
            throw new RuntimeException("Student with Roll Number '{$rollNo}' already exists.");
        }

        // Parse & compute marks
        $marks = [
            'maths'    => (float)($data['maths'] ?? 0),
            'physics'  => (float)($data['physics'] ?? 0),
            'cs'       => (float)($data['cs'] ?? 0),
            'web_tech' => (float)($data['web_tech'] ?? 0),
            'english'  => (float)($data['english'] ?? 0),
        ];

        $metrics = calculateAcademicMetrics($marks);

        $sql = "INSERT INTO students 
            (roll_no, name, email, department, semester, maths, physics, cs, web_tech, english, total_marks, percentage, gpa, grade, status, created_at, updated_at)
            VALUES 
            (:roll_no, :name, :email, :dept, :sem, :maths, :physics, :cs, :web_tech, :english, :total_marks, :percentage, :gpa, :grade, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':roll_no'     => $rollNo,
            ':name'        => $name,
            ':email'       => $email,
            ':dept'        => $dept,
            ':sem'         => $sem,
            ':maths'       => $marks['maths'],
            ':physics'     => $marks['physics'],
            ':cs'          => $marks['cs'],
            ':web_tech'    => $marks['web_tech'],
            ':english'     => $marks['english'],
            ':total_marks' => $metrics['total_marks'],
            ':percentage'  => $metrics['percentage'],
            ':gpa'         => $metrics['gpa'],
            ':grade'       => $metrics['grade'],
            ':status'      => $metrics['status'],
        ]);

        $newId = (int)$this->pdo->lastInsertId();
        return $this->readOne($newId);
    }

    /**
     * READ ALL: Filter, search, and sort records
     */
    public function readAll(string $query = '', string $semester = '', string $grade = '', string $sortBy = 'id', string $sortDir = 'DESC'): array {
        $allowedSortCols = ['id', 'roll_no', 'name', 'percentage', 'gpa', 'created_at', 'total_marks'];
        if (!in_array($sortBy, $allowedSortCols, true)) {
            $sortBy = 'id';
        }
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $where = [];
        $params = [];

        if (trim($query) !== '') {
            $where[] = '(roll_no LIKE :q OR name LIKE :q OR email LIKE :q OR department LIKE :q)';
            $params[':q'] = '%' . trim($query) . '%';
        }

        if (trim($semester) !== '' && $semester !== 'All') {
            $where[] = 'semester = :sem';
            $params[':sem'] = trim($semester);
        }

        if (trim($grade) !== '' && $grade !== 'All') {
            $where[] = 'grade = :grade';
            $params[':grade'] = trim($grade);
        }

        $sql = "SELECT * FROM students";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY {$sortBy} {$sortDir}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * READ ONE: Retrieve single record by primary key ID
     */
    public function readOne(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM students WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * UPDATE: Modify existing student record
     */
    public function update(int $id, array $data): bool {
        $existing = $this->readOne($id);
        if (!$existing) {
            throw new RuntimeException("Student record #{$id} not found.");
        }

        $rollNo = trim($data['roll_no'] ?? $existing['roll_no']);
        $name   = trim($data['name'] ?? $existing['name']);
        $dept   = trim($data['department'] ?? $existing['department']);
        $sem    = trim($data['semester'] ?? $existing['semester']);
        $email  = generateInstitutionalEmail($name, $rollNo);

        // Check if new roll_no conflicts with another student
        if ($rollNo !== $existing['roll_no']) {
            $checkStmt = $this->pdo->prepare('SELECT id FROM students WHERE roll_no = :roll_no AND id != :id');
            $checkStmt->execute([':roll_no' => $rollNo, ':id' => $id]);
            if ($checkStmt->fetch()) {
                throw new RuntimeException("Roll Number '{$rollNo}' is already taken by another student.");
            }
        }

        // Marks
        $marks = [
            'maths'    => isset($data['maths']) ? (float)$data['maths'] : (float)$existing['maths'],
            'physics'  => isset($data['physics']) ? (float)$data['physics'] : (float)$existing['physics'],
            'cs'       => isset($data['cs']) ? (float)$data['cs'] : (float)$existing['cs'],
            'web_tech' => isset($data['web_tech']) ? (float)$data['web_tech'] : (float)$existing['web_tech'],
            'english'  => isset($data['english']) ? (float)$data['english'] : (float)$existing['english'],
        ];

        $metrics = calculateAcademicMetrics($marks);

        $sql = "UPDATE students SET
            roll_no = :roll_no,
            name = :name,
            email = :email,
            department = :dept,
            semester = :sem,
            maths = :maths,
            physics = :physics,
            cs = :cs,
            web_tech = :web_tech,
            english = :english,
            total_marks = :total_marks,
            percentage = :percentage,
            gpa = :gpa,
            grade = :grade,
            status = :status,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':roll_no'     => $rollNo,
            ':name'        => $name,
            ':email'       => $email,
            ':dept'        => $dept,
            ':sem'         => $sem,
            ':maths'       => $marks['maths'],
            ':physics'     => $marks['physics'],
            ':cs'          => $marks['cs'],
            ':web_tech'    => $marks['web_tech'],
            ':english'     => $marks['english'],
            ':total_marks' => $metrics['total_marks'],
            ':percentage'  => $metrics['percentage'],
            ':gpa'         => $metrics['gpa'],
            ':grade'       => $metrics['grade'],
            ':status'      => $metrics['status'],
            ':id'          => $id,
        ]);
    }

    /**
     * DELETE: Remove student record
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM students WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * ANALYTICS / STATS
     */
    public function getAnalytics(): array {
        $countStmt = $this->pdo->query('SELECT COUNT(*) AS total, AVG(percentage) AS avg_pct, MAX(percentage) AS max_pct, MIN(percentage) AS min_pct, AVG(gpa) AS avg_gpa FROM students');
        $overall = $countStmt->fetch() ?: [];

        $passedStmt = $this->pdo->query("SELECT COUNT(*) AS passed FROM students WHERE grade != 'F'");
        $passedRow = $passedStmt->fetch() ?: ['passed' => 0];

        $gradesStmt = $this->pdo->query("SELECT grade, COUNT(*) AS count FROM students GROUP BY grade ORDER BY count DESC");
        $grades = $gradesStmt->fetchAll();

        $topStmt = $this->pdo->query("SELECT name, roll_no, percentage, grade, gpa FROM students ORDER BY percentage DESC LIMIT 1");
        $topScorer = $topStmt->fetch() ?: null;

        $totalCount = (int)($overall['total'] ?? 0);
        $passedCount = (int)($passedRow['passed'] ?? 0);
        $passRate = $totalCount > 0 ? round(($passedCount / $totalCount) * 100, 1) : 0.0;

        return [
            'total_students' => $totalCount,
            'passed_count'   => $passedCount,
            'pass_rate'      => $passRate,
            'average_pct'    => round((float)($overall['avg_pct'] ?? 0), 1),
            'highest_pct'    => round((float)($overall['max_pct'] ?? 0), 1),
            'lowest_pct'     => round((float)($overall['min_pct'] ?? 0), 1),
            'average_gpa'    => round((float)($overall['avg_gpa'] ?? 0), 2),
            'top_scorer'     => $topScorer,
            'grade_distribution' => $grades,
        ];
    }

    /**
     * Reset table records
     */
    public function resetData(): bool {
        $this->pdo->exec('DELETE FROM students');
        $this->seedBenchmarkData();
        return true;
    }

    /**
     * Diagnostic and connection info
     */
    public function getConnectionStatus(): array {
        return [
            'active_driver'     => $this->activeDriver,
            'is_fallback'       => $this->isFallback,
            'mysql_host'        => DB_HOST . ':' . DB_PORT,
            'mysql_database'    => DB_NAME,
            'mysql_user'        => DB_USER,
            'mysql_error'       => $this->mysqlError,
            'sqlite_file'       => SQLITE_PATH,
            'available_drivers' => PDO::getAvailableDrivers(),
            'server_version'    => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        ];
    }

    /**
     * Test connection to MySQL directly with given or default credentials
     */
    public static function testMysqlConnection(string $host = DB_HOST, string $port = DB_PORT, string $user = DB_USER, string $pass = DB_PASS): array {
        if (!extension_loaded('pdo_mysql')) {
            return [
                'success' => false,
                'message' => "Extension 'pdo_mysql' is not loaded in PHP runtime."
            ];
        }

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            return [
                'success' => true,
                'message' => "Successfully connected to MySQL Server (v{$version})!",
                'version' => $version
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
