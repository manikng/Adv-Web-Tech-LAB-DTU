<?php
/**
 * DTU Academy - Student Result Management System
 * System Configuration & Academic Rubrics
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'DTU Result Cloud');
define('INSTITUTION_NAME', 'Delhi Technological University');
define('ACADEMIC_YEAR', '2025 – 2026');
define('SEMESTER_DEFAULT', 'Semester 6');

// Core subject registry
define('COURSES', [
    'maths' => [
        'code' => 'CS-601',
        'title' => 'Advanced Applied Mathematics',
        'credits' => 4,
        'max' => 100,
        'pass' => 40
    ],
    'physics' => [
        'code' => 'CS-602',
        'title' => 'Quantum & Applied Physics',
        'credits' => 3,
        'max' => 100,
        'pass' => 40
    ],
    'cs' => [
        'code' => 'CS-603',
        'title' => 'Data Structures & Algorithms',
        'credits' => 4,
        'max' => 100,
        'pass' => 40
    ],
    'web_tech' => [
        'code' => 'CS-604',
        'title' => 'Modern Dynamic Web Architectures',
        'credits' => 4,
        'max' => 100,
        'pass' => 40
    ],
    'english' => [
        'code' => 'CS-605',
        'title' => 'Technical & Professional Communication',
        'credits' => 2,
        'max' => 100,
        'pass' => 40
    ]
]);

// Helper for XSS safe output
function h($string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function verify_csrf(?string $token): bool {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
