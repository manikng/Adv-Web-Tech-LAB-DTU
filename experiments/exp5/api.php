<?php
/**
 * Lumina Academy - RESTful & Progressive API Gateway
 * Designed with Norman error-resilience and Apple-grade responsiveness.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$db = LuminaDB::getInstance();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $students = $db->getAll();
            echo json_encode([
                'success' => true,
                'data' => $students,
                'stats' => calculateStats($students)
            ]);
            break;

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $student = $db->getById($id);
            if (!$student) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Student record not found.']);
                exit;
            }
            echo json_encode(['success' => true, 'data' => $student]);
            break;

        case 'create':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
                exit;
            }

            $input = getRequestPayload();
            validateStudentPayload($input);

            $newId = $db->create($input);
            $student = $db->getById($newId);

            echo json_encode([
                'success' => true,
                'message' => "Student {$student['name']} enrolled successfully.",
                'data' => $student
            ]);
            break;

        case 'update':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
                exit;
            }

            $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("Invalid student identifier provided.");
            }

            $input = getRequestPayload();
            validateStudentPayload($input);

            $updated = $db->update($id, $input);
            if (!$updated) {
                throw new Exception("Failed to update student record.");
            }

            $student = $db->getById($id);
            echo json_encode([
                'success' => true,
                'message' => "Academic record for {$student['name']} updated.",
                'data' => $student
            ]);
            break;

        case 'delete':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
                exit;
            }

            $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            $existing = $db->getById($id);
            if (!$existing) {
                throw new Exception("Student record not found or already deleted.");
            }

            $deleted = $db->delete($id);
            echo json_encode([
                'success' => $deleted,
                'message' => "Record for {$existing['name']} successfully removed."
            ]);
            break;

        case 'export_csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="dtu_students_results_' . date('Ymd_His') . '.csv"');
            
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID', 'Roll Number', 'Student Name', 'Email', 'Semester',
                'Mathematics', 'Physics', 'Computer Science', 'Web Technology', 'English',
                'Total Marks', 'Max Marks', 'Percentage', 'Grade', 'GPA', 'Classification'
            ]);

            $students = $db->getAll();
            foreach ($students as $s) {
                fputcsv($out, [
                    $s['id'],
                    $s['roll_no'],
                    $s['name'],
                    $s['email'],
                    $s['semester'],
                    $s['maths'],
                    $s['physics'],
                    $s['cs'],
                    $s['web_tech'],
                    $s['english'],
                    $s['total_marks'],
                    $s['max_marks'],
                    $s['percentage'] . '%',
                    $s['grade'],
                    $s['gpa'],
                    $s['status']
                ]);
            }
            fclose($out);
            exit;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action requested.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function getRequestPayload(): array {
    $raw = file_get_contents('php://input');
    $jsonData = json_decode($raw, true);
    if (is_array($jsonData) && !empty($jsonData)) {
        return $jsonData;
    }
    return $_POST;
}

function validateStudentPayload(array &$payload): void {
    if (empty(trim($payload['roll_no'] ?? ''))) {
        throw new Exception("Student ID / Roll No is required.");
    }
    if (empty(trim($payload['name'] ?? ''))) {
        throw new Exception("Student Name is required.");
    }
    if (empty(trim($payload['email'] ?? '')) || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("A valid student email address is required.");
    }

    $payload['semester'] = trim($payload['semester'] ?? SEMESTER_DEFAULT);

    $subjectKeys = ['maths', 'physics', 'cs', 'web_tech', 'english'];
    foreach ($subjectKeys as $sub) {
        if (!isset($payload[$sub]) || $payload[$sub] === '') {
            throw new Exception("Marks for all 5 subjects are mandatory.");
        }
        $val = (int)$payload[$sub];
        if ($val < 0 || $val > 100) {
            throw new Exception("Subject scores must be between 0 and 100. Received {$val}.");
        }
        $payload[$sub] = $val;
    }
}

function calculateStats(array $students): array {
    $count = count($students);
    if ($count === 0) {
        return [
            'total_students' => 0,
            'class_average' => 0,
            'pass_percentage' => 0,
            'distinction_count' => 0,
            'top_student' => null
        ];
    }

    $totalPerc = 0;
    $passed = 0;
    $distinctions = 0;
    $topStudent = $students[0];

    foreach ($students as $s) {
        $totalPerc += $s['percentage'];
        if ($s['percentage'] >= 50 && empty($s['failed_subjects'])) {
            $passed++;
        }
        if ($s['percentage'] >= 80) {
            $distinctions++;
        }
        if ($s['percentage'] > $topStudent['percentage']) {
            $topStudent = $s;
        }
    }

    return [
        'total_students' => $count,
        'class_average' => round($totalPerc / $count, 1),
        'pass_percentage' => round(($passed / $count) * 100, 1),
        'distinction_count' => $distinctions,
        'top_student' => $topStudent
    ];
}
