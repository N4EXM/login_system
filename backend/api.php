<?php
require 'auth.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = login($data['username'], $data['password']);
            break;
            
        case 'check-auth':
            $response = checkAuth();
            break;
            
        case 'logout':
            $response = logout();
            break;

        case 'register':
            $data = json_decode(file_get_contents('php://input'), true);
            $response = register($data['username'], $data['password']);
            break;
            
        default:
            $response = ['error' => 'Invalid action'];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>