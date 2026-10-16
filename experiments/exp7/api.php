<?php
/**
 * Experiment 7: User Authentication using Sessions and Cookies
 * RESTful Authentication API Controller
 * 
 * Student: Manish Singh (Roll No: 23/CS/244)
 * Subject: Advanced Web Technologies
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

$auth = AuthManager::getInstance();
$db = AuthDB::getInstance();

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

try {
    switch ($action) {
        case 'status':
            $status = $auth->getSessionInfo();
            $status['csrf_token'] = $auth->getCsrfToken();
            $status['db_status'] = $db->getConnectionStatus();
            echo json_encode([
                'success' => true,
                'session' => $status,
            ]);
            break;

        case 'login':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $ident = (string)($input['identifier'] ?? '');
            $pass  = (string)($input['password'] ?? '');
            $rem   = (bool)($input['remember_me'] ?? false);
            $csrf  = (string)($input['csrf_token'] ?? '');

            if (!$auth->validateCsrfToken($csrf)) {
                throw new RuntimeException("Security token (CSRF) validation failed. Please refresh and try again.");
            }

            $user = $auth->login($ident, $pass, $rem);
            echo json_encode([
                'success' => true,
                'message' => "Welcome back, {$user['full_name']}! Authenticated successfully.",
                'user' => $user,
                'session' => $auth->getSessionInfo(),
            ]);
            break;

        case 'quick_demo':
            // Easy 1-click evaluator login
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $roleKey = (string)($input['role'] ?? 'admin');
            
            $demoAccounts = [
                'admin'   => 'manish',
                'student' => 'aarav',
                'faculty' => 'sneha',
            ];
            $username = $demoAccounts[$roleKey] ?? 'manish';
            $user = $auth->login($username, 'Password@123', true);

            echo json_encode([
                'success' => true,
                'message' => "Logged in as {$user['full_name']} ({$user['role']}) via Quick Demo.",
                'user' => $user,
                'session' => $auth->getSessionInfo(),
            ]);
            break;

        case 'register':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $username = (string)($input['username'] ?? '');
            $email    = (string)($input['email'] ?? '');
            $password = (string)($input['password'] ?? '');
            $fullName = (string)($input['full_name'] ?? '');
            $role     = (string)($input['role'] ?? 'student');
            $dept     = (string)($input['department'] ?? 'Computer Science & Engineering');
            $csrf     = (string)($input['csrf_token'] ?? '');

            if (!$auth->validateCsrfToken($csrf)) {
                throw new RuntimeException("Security token (CSRF) validation failed. Please refresh the page.");
            }

            $user = $auth->register($username, $email, $password, $fullName, $role, $dept);

            // Auto-login after registration
            $loggedIn = $auth->login($username, $password, false);

            echo json_encode([
                'success' => true,
                'message' => "Registration successful! Account established for {$user['full_name']}.",
                'user' => $loggedIn,
                'session' => $auth->getSessionInfo(),
            ]);
            break;

        case 'logout':
            $auth->logout();
            echo json_encode([
                'success' => true,
                'message' => "You have been securely logged out. Session terminated.",
            ]);
            break;

        case 'logs':
            if (!$auth->isAuthenticated()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required.']);
                exit;
            }
            $userId = (int)$auth->getCurrentUser()['id'];
            $logs = $db->getRecentLogs($userId, 15);
            echo json_encode([
                'success' => true,
                'logs' => $logs,
            ]);
            break;

        case 'users':
            $users = $db->getAllUsers();
            echo json_encode([
                'success' => true,
                'users' => $users,
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Action '{$action}' not recognized."]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
