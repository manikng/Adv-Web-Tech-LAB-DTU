<?php
/**
 * Experiment 6: Database Connectivity and CRUD Operations
 * RESTful JSON API Controller
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$db = Database::getInstance();

// Handle CSV / JSON File Exports
if ($action === 'export_csv') {
    $students = $db->readAll('', '', '', 'roll_no', 'ASC');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=dtu_students_exp6_' . date('Ymd_His') . '.csv');
    
    $out = fopen('php://output', 'w');
    // Header row
    fputcsv($out, ['ID', 'Roll No', 'Name', 'Email', 'Department', 'Semester', 'Maths', 'Physics', 'CS', 'Web Tech', 'English', 'Total Marks', 'Percentage', 'GPA', 'Grade', 'Status']);
    
    foreach ($students as $s) {
        fputcsv($out, [
            $s['id'],
            $s['roll_no'],
            $s['name'],
            $s['email'],
            $s['department'],
            $s['semester'],
            $s['maths'],
            $s['physics'],
            $s['cs'],
            $s['web_tech'],
            $s['english'],
            $s['total_marks'],
            $s['percentage'],
            $s['gpa'],
            $s['grade'],
            $s['status'],
        ]);
    }
    fclose($out);
    exit;
}

if ($action === 'export_json') {
    $students = $db->readAll('', '', '', 'roll_no', 'ASC');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=dtu_students_exp6_' . date('Ymd_His') . '.json');
    echo json_encode([
        'system' => APP_NAME,
        'experiment' => 'Experiment 6 - Database CRUD Operations (PHP & MySQL)',
        'timestamp' => date('c'),
        'total' => count($students),
        'records' => $students
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// All other requests respond with JSON
header('Content-Type: application/json; charset=utf-8');

try {
    switch ($action) {
        case 'list':
            $q = (string)($_GET['q'] ?? '');
            $sem = (string)($_GET['semester'] ?? '');
            $grade = (string)($_GET['grade'] ?? '');
            $sort = (string)($_GET['sort'] ?? 'id');
            $dir = (string)($_GET['dir'] ?? 'DESC');
            
            $students = $db->readAll($q, $sem, $grade, $sort, $dir);
            $stats = $db->getAnalytics();
            $dbInfo = $db->getConnectionStatus();

            echo json_encode([
                'success' => true,
                'count' => count($students),
                'students' => $students,
                'stats' => $stats,
                'db_status' => $dbInfo,
            ], JSON_UNESCAPED_SLASHES);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException("Invalid or missing student ID.");
            }
            $student = $db->readOne($id);
            if (!$student) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Student record not found.']);
                exit;
            }
            echo json_encode(['success' => true, 'student' => $student]);
            break;

        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }
            $created = $db->create($input);
            echo json_encode([
                'success' => true,
                'message' => "Student '{$created['name']}' ({$created['roll_no']}) registered successfully!",
                'student' => $created,
                'stats' => $db->getAnalytics(),
            ]);
            break;

        case 'update':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException("Valid student ID is required for update.");
            }
            $db->update($id, $input);
            $updated = $db->readOne($id);
            echo json_encode([
                'success' => true,
                'message' => "Student record #{$id} updated successfully!",
                'student' => $updated,
                'stats' => $db->getAnalytics(),
            ]);
            break;

        case 'delete':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException("Valid student ID is required for deletion.");
            }
            $student = $db->readOne($id);
            if (!$student) {
                throw new RuntimeException("Student record not found.");
            }
            $db->delete($id);
            echo json_encode([
                'success' => true,
                'message' => "Student '{$student['name']}' (#{$id}) was deleted.",
                'stats' => $db->getAnalytics(),
            ]);
            break;

        case 'stats':
            echo json_encode([
                'success' => true,
                'stats' => $db->getAnalytics(),
                'db_status' => $db->getConnectionStatus(),
            ]);
            break;

        case 'db_status':
            echo json_encode([
                'success' => true,
                'db_status' => $db->getConnectionStatus(),
            ]);
            break;

        case 'test_mysql':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $host = $input['host'] ?? DB_HOST;
            $port = $input['port'] ?? DB_PORT;
            $user = $input['user'] ?? DB_USER;
            $pass = $input['pass'] ?? DB_PASS;
            $testRes = Database::testMysqlConnection($host, (string)$port, $user, $pass);
            echo json_encode($testRes);
            break;

        case 'reset':
            $db->resetData();
            echo json_encode([
                'success' => true,
                'message' => "Academic database successfully reset and seeded with benchmark records.",
                'stats' => $db->getAnalytics(),
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Unknown action '{$action}'."]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
