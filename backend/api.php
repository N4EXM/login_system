<?php

ob_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'auth_functions.php';
require 'db_connection.php';

// Start session
session_start();

$action = $_GET['action'] ?? '';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'login':
            $response = loginUser($pdo, $input['username'] ?? '', $input['password'] ?? '');
            break;
            
        case 'register':
            $response = registerUser($pdo, $input['username'] ?? '', $input['password'] ?? '');
            break;
            
        case 'check-auth':
            $response = checkAuthStatus();
            break;
            
        case 'logout':
            $response = ['success' => logoutUser()];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Process request and prepare $response
    
    // Clear buffer and send only JSON
    ob_end_clean();
    echo json_encode($response);
    exit();
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}