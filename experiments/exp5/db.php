<?php
/**
 * Delhi Technological University - Resilient Storage Driver
 * Automatically leverages PDO SQLite if available, or seamlessly
 * falls back to a high-performance JSON store if SQLite extension is disabled.
 */

class LuminaDB {
    private static ?LuminaDB $instance = null;
    private $pdo = null;
    private bool $useSqlite = false;
    private string $jsonFile;

    private function __construct() {
        $dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0777, true);
        }

        $this->jsonFile = $dataDir . DIRECTORY_SEPARATOR . 'students.json';

        // Check if PDO SQLite extension is loaded
        if (extension_loaded('pdo_sqlite')) {
            try {
                $dbPath = $dataDir . DIRECTORY_SEPARATOR . 'results.sqlite';
                $this->pdo = new PDO("sqlite:" . $dbPath);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->initSqlite();
                $this->useSqlite = true;
                return;
            } catch (Exception $e) {
                // Fallback to JSON store
                $this->useSqlite = false;
            }
        }

        $this->initJson();
    }

    public static function getInstance(): LuminaDB {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initSqlite(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                roll_no TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                semester TEXT NOT NULL,
                maths INTEGER NOT NULL,
                physics INTEGER NOT NULL,
                cs INTEGER NOT NULL,
                web_tech INTEGER NOT NULL,
                english INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $count = (int)$this->pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        if ($count === 0) {
            $this->seedInitialData();
        }
    }

    private function initJson(): void {
        if (!file_exists($this->jsonFile)) {
            $initial = $this->getInitialSeed();
            file_put_contents($this->jsonFile, json_encode($initial, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    private function getInitialSeed(): array {
        return [
            [
                'id' => 1,
                'roll_no' => 'LA-2026-001',
                'name' => 'Sophia Montgomery',
                'email' => 'sophia.m@dtu.ac.in',
                'semester' => 'Semester 6',
                'maths' => 96,
                'physics' => 92,
                'cs' => 98,
                'web_tech' => 99,
                'english' => 94,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'roll_no' => 'LA-2026-002',
                'name' => 'Alexander Vance',
                'email' => 'alex.v@dtu.ac.in',
                'semester' => 'Semester 6',
                'maths' => 88,
                'physics' => 84,
                'cs' => 90,
                'web_tech' => 95,
                'english' => 89,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'roll_no' => 'LA-2026-003',
                'name' => 'Elena Rostova',
                'email' => 'elena.r@dtu.ac.in',
                'semester' => 'Semester 6',
                'maths' => 74,
                'physics' => 78,
                'cs' => 85,
                'web_tech' => 88,
                'english' => 82,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'roll_no' => 'LA-2026-004',
                'name' => 'Marcus Chen',
                'email' => 'marcus.c@dtu.ac.in',
                'semester' => 'Semester 6',
                'maths' => 62,
                'physics' => 68,
                'cs' => 70,
                'web_tech' => 74,
                'english' => 65,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 5,
                'roll_no' => 'LA-2026-005',
                'name' => 'Aria Sterling',
                'email' => 'aria.s@dtu.ac.in',
                'semester' => 'Semester 6',
                'maths' => 45,
                'physics' => 52,
                'cs' => 58,
                'web_tech' => 62,
                'english' => 55,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function seedInitialData(): void {
        $seeds = $this->getInitialSeed();
        $stmt = $this->pdo->prepare("
            INSERT INTO students (id, roll_no, name, email, semester, maths, physics, cs, web_tech, english, created_at)
            VALUES (:id, :roll_no, :name, :email, :semester, :maths, :physics, :cs, :web_tech, :english, :created_at)
        ");
        foreach ($seeds as $s) {
            $stmt->execute($s);
        }
    }

    public function getAll(): array {
        if ($this->useSqlite) {
            $stmt = $this->pdo->query("SELECT * FROM students ORDER BY id DESC");
            $rows = $stmt->fetchAll();
        } else {
            $data = json_decode(file_get_contents($this->jsonFile), true) ?: [];
            usort($data, fn($a, $b) => $b['id'] <=> $a['id']);
            $rows = $data;
        }

        return array_map([$this, 'enrichStudentData'], $rows);
    }

    public function getById(int $id): ?array {
        if ($this->useSqlite) {
            $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ? $this->enrichStudentData($row) : null;
        } else {
            $data = json_decode(file_get_contents($this->jsonFile), true) ?: [];
            foreach ($data as $item) {
                if ((int)$item['id'] === $id) {
                    return $this->enrichStudentData($item);
                }
            }
            return null;
        }
    }

    public function create(array $data): int {
        // Validation check for duplicate roll_no
        if ($this->rollNumberExists($data['roll_no'])) {
            throw new Exception("Student ID / Roll No '" . htmlspecialchars($data['roll_no']) . "' is already enrolled.");
        }

        if ($this->useSqlite) {
            $stmt = $this->pdo->prepare("
                INSERT INTO students (roll_no, name, email, semester, maths, physics, cs, web_tech, english)
                VALUES (:roll_no, :name, :email, :semester, :maths, :physics, :cs, :web_tech, :english)
            ");
            $stmt->execute([
                ':roll_no'  => trim($data['roll_no']),
                ':name'     => trim($data['name']),
                ':email'    => trim($data['email']),
                ':semester' => trim($data['semester']),
                ':maths'    => (int)$data['maths'],
                ':physics'  => (int)$data['physics'],
                ':cs'       => (int)$data['cs'],
                ':web_tech' => (int)$data['web_tech'],
                ':english'  => (int)$data['english']
            ]);
            return (int)$this->pdo->lastInsertId();
        } else {
            $items = json_decode(file_get_contents($this->jsonFile), true) ?: [];
            $maxId = 0;
            foreach ($items as $item) {
                if ($item['id'] > $maxId) $maxId = $item['id'];
            }
            $newId = $maxId + 1;
            $record = [
                'id' => $newId,
                'roll_no'  => trim($data['roll_no']),
                'name'     => trim($data['name']),
                'email'    => trim($data['email']),
                'semester' => trim($data['semester']),
                'maths'    => (int)$data['maths'],
                'physics'  => (int)$data['physics'],
                'cs'       => (int)$data['cs'],
                'web_tech' => (int)$data['web_tech'],
                'english'  => (int)$data['english'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            $items[] = $record;
            file_put_contents($this->jsonFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $newId;
        }
    }

    public function update(int $id, array $data): bool {
        // Check duplicate roll number for other ids
        if ($this->rollNumberExists($data['roll_no'], $id)) {
            throw new Exception("Student ID / Roll No '" . htmlspecialchars($data['roll_no']) . "' is already enrolled by another student.");
        }

        if ($this->useSqlite) {
            $stmt = $this->pdo->prepare("
                UPDATE students SET
                    roll_no = :roll_no,
                    name = :name,
                    email = :email,
                    semester = :semester,
                    maths = :maths,
                    physics = :physics,
                    cs = :cs,
                    web_tech = :web_tech,
                    english = :english
                WHERE id = :id
            ");
            return $stmt->execute([
                ':id'       => $id,
                ':roll_no'  => trim($data['roll_no']),
                ':name'     => trim($data['name']),
                ':email'    => trim($data['email']),
                ':semester' => trim($data['semester']),
                ':maths'    => (int)$data['maths'],
                ':physics'  => (int)$data['physics'],
                ':cs'       => (int)$data['cs'],
                ':web_tech' => (int)$data['web_tech'],
                ':english'  => (int)$data['english']
            ]);
        } else {
            $items = json_decode(file_get_contents($this->jsonFile), true) ?: [];
            $found = false;
            foreach ($items as &$item) {
                if ((int)$item['id'] === $id) {
                    $item['roll_no']  = trim($data['roll_no']);
                    $item['name']     = trim($data['name']);
                    $item['email']    = trim($data['email']);
                    $item['semester'] = trim($data['semester']);
                    $item['maths']    = (int)$data['maths'];
                    $item['physics']  = (int)$data['physics'];
                    $item['cs']       = (int)$data['cs'];
                    $item['web_tech'] = (int)$data['web_tech'];
                    $item['english']  = (int)$data['english'];
                    $found = true;
                    break;
                }
            }
            if ($found) {
                file_put_contents($this->jsonFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            return $found;
        }
    }

    public function delete(int $id): bool {
        if ($this->useSqlite) {
            $stmt = $this->pdo->prepare("DELETE FROM students WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } else {
            $items = json_decode(file_get_contents($this->jsonFile), true) ?: [];
            $originalCount = count($items);
            $items = array_filter($items, fn($item) => (int)$item['id'] !== $id);
            if (count($items) !== $originalCount) {
                file_put_contents($this->jsonFile, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                return true;
            }
            return false;
        }
    }

    private function rollNumberExists(string $rollNo, int $excludeId = 0): bool {
        $rollNo = trim($rollNo);
        if ($this->useSqlite) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM students WHERE LOWER(roll_no) = LOWER(:roll_no) AND id != :excludeId");
            $stmt->execute([':roll_no' => $rollNo, ':excludeId' => $excludeId]);
            return (int)$stmt->fetchColumn() > 0;
        } else {
            $items = json_decode(file_get_contents($this->jsonFile), true) ?: [];
            foreach ($items as $item) {
                if (strcasecmp($item['roll_no'], $rollNo) === 0 && (int)$item['id'] !== $excludeId) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Compute totals, percentage, GPA, grade & classification
     */
    public function enrichStudentData(array $student): array {
        $subjects = [
            'Mathematics' => (int)$student['maths'],
            'Applied Physics' => (int)$student['physics'],
            'Computer Science' => (int)$student['cs'],
            'Advanced Web Tech' => (int)$student['web_tech'],
            'Professional English' => (int)$student['english']
        ];

        $total = array_sum($subjects);
        $maxTotal = count($subjects) * 100;
        $percentage = round(($total / $maxTotal) * 100, 1);

        // Standard academic grading & GPA
        if ($percentage >= 90) {
            $grade = 'A+';
            $gpa = 4.0;
            $status = 'Distinction';
            $color = 'emerald';
        } elseif ($percentage >= 80) {
            $grade = 'A';
            $gpa = 3.7;
            $status = 'Distinction';
            $color = 'emerald';
        } elseif ($percentage >= 70) {
            $grade = 'B';
            $gpa = 3.0;
            $status = 'First Class';
            $color = 'blue';
        } elseif ($percentage >= 60) {
            $grade = 'C';
            $gpa = 2.5;
            $status = 'Second Class';
            $color = 'indigo';
        } elseif ($percentage >= 50) {
            $grade = 'D';
            $gpa = 2.0;
            $status = 'Pass';
            $color = 'amber';
        } else {
            $grade = 'F';
            $gpa = 0.0;
            $status = 'Needs Review';
            $color = 'rose';
        }

        // Check if any single subject failed (< 40)
        $failedSubjects = [];
        foreach ($subjects as $name => $score) {
            if ($score < 40) {
                $failedSubjects[] = $name;
            }
        }

        $student['total_marks'] = $total;
        $student['max_marks'] = $maxTotal;
        $student['percentage'] = $percentage;
        $student['grade'] = $grade;
        $student['gpa'] = number_format($gpa, 2);
        $student['status'] = count($failedSubjects) > 0 ? 'Backlog / Arrear' : $status;
        $student['status_color'] = count($failedSubjects) > 0 ? 'rose' : $color;
        $student['failed_subjects'] = $failedSubjects;
        $student['subjects_breakdown'] = $subjects;

        return $student;
    }
}
