<?php
require 'auth.php';

header("Content-Type: application/json");

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
            $response = register($data['username'], $data['email'], $data['password']);
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