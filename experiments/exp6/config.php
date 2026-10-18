<?php
/**
 * Experiment 6: Database Connectivity and CRUD Operations
 * Configuration File
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies (AWT)
 */

declare(strict_types=1);

// Error reporting for robust development
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Database Configuration (MySQL Primary)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'exp6_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// SQLite Resilient Fallback Storage
define('SQLITE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'exp6_database.sqlite');

// Academic System Rubrics & Metadata
define('APP_NAME', 'Delhi Technological University');
define('APP_SUBTITLE', 'Enterprise Student Information System & MySQL CRUD Engine');
define('APP_VERSION', '2.4.0');
define('STUDENT_NAME', 'Manish Singh');
define('ROLL_NUMBER', '23/CS/244');
define('SUBJECT_NAME', 'Advanced Web Technologies');
define('EXPERIMENT_NO', '06');

function generateInstitutionalEmail(string $name, string $rollNo): string {
    $namePart = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '.', $name), '.'));
    $rollPart = strtolower((string)preg_replace('/[^a-z0-9]+/i', '', $rollNo));

    return $namePart . '.' . $rollPart . '@dtu.ac.in';
}

// Curriculum Subjects & Maximum Marks
const SUBJECTS = [
    'maths'    => ['label' => 'Discrete Mathematics', 'code' => 'CS-301', 'max' => 100],
    'physics'  => ['label' => 'Applied Physics',       'code' => 'CS-302', 'max' => 100],
    'cs'       => ['label' => 'Data Structures & Algo','code' => 'CS-303', 'max' => 100],
    'web_tech' => ['label' => 'Advanced Web Tech',     'code' => 'CS-304', 'max' => 100],
    'english'  => ['label' => 'Technical Communication','code' => 'HS-301', 'max' => 100],
];

// Available Academic Semesters
const SEMESTERS = [
    'Semester 1',
    'Semester 2',
    'Semester 3',
    'Semester 4',
    'Semester 5',
    'Semester 6',
    'Semester 7',
    'Semester 8',
];

// Grade Rubric Calculation Helper
function calculateAcademicMetrics(array $marks): array {
    $total = 0;
    $count = count(SUBJECTS);
    
    foreach (array_keys(SUBJECTS) as $key) {
        $score = isset($marks[$key]) && is_numeric($marks[$key]) ? (float)$marks[$key] : 0.0;
        $total += max(0, min(100, $score));
    }
    
    $percentage = round($total / $count, 2);
    
    // Grading scale
    if ($percentage >= 90.0) {
        $grade = 'A+';
        $gpa = 10.0;
        $status = 'Distinction';
    } elseif ($percentage >= 80.0) {
        $grade = 'A';
        $gpa = 9.0;
        $status = 'First Class with Distinction';
    } elseif ($percentage >= 70.0) {
        $grade = 'B+';
        $gpa = 8.0;
        $status = 'First Class';
    } elseif ($percentage >= 60.0) {
        $grade = 'B';
        $gpa = 7.0;
        $status = 'Second Class';
    } elseif ($percentage >= 50.0) {
        $grade = 'C';
        $gpa = 6.0;
        $status = 'Pass Class';
    } elseif ($percentage >= 40.0) {
        $grade = 'P';
        $gpa = 5.0;
        $status = 'Conditional Pass';
    } else {
        $grade = 'F';
        $gpa = 0.0;
        $status = 'Remedial Required';
    }

    return [
        'total_marks' => $total,
        'percentage'  => $percentage,
        'gpa'         => $gpa,
        'grade'       => $grade,
        'status'      => $status,
    ];
}
