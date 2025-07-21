<?php
// Enable strict error reporting at the VERY TOP
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Set headers FIRST
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(204);
    exit();
}

require "./../config/database.php";
require "./../models/auth_functions.php";

// Start session
session_start([
    'cookie_lifetime' => 86400 * 30, // 30 days
    'cookie_secure' => false,       // true in production
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

try {
    $action = $_GET['action'] ?? '';
    $response = ['success' => false, 'message' => 'No action specified'];

    // Handle actions that don't require JSON input first
    if ($action === 'check-auth') {
        $response = auth_check();
        ob_end_clean();
        echo json_encode($response);
        exit();
    }

    // For actions that require JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON input');
    }

    switch ($action) {
        case 'register':
            if (empty($input['username']) || empty($input['password'])) {
                throw new InvalidArgumentException('Username and password are required');
            }
            $response = register($pdo, $input['username'], $input['password']);
            break;

        case 'login':
            if (empty($input['username']) || empty($input['password'])) {
                throw new InvalidArgumentException('Username and password are required');
            }
            $response = login($pdo, $input['username'], $input['password']);
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }

} catch (InvalidArgumentException $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
    http_response_code(400);
} catch (RuntimeException $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
    http_response_code(400);
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'An error occurred'];
    http_response_code(500);
    error_log('API Error: ' . $e->getMessage());
}

// Clean output and send response
ob_end_clean();
echo json_encode($response);
exit();