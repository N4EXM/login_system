<?php

// Enable strict error reporting at the VERY TOP
declare(strict_types=1);
ini_set('display_errors', '0'); // Don't show errors to users
ini_set('log_errors', '1');     // But do log them
error_reporting(E_ALL);

// debugging 
ob_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(204);
    exit();
}

require "database.php";
require "auth_functions";

try {

    $input = json_decode(json: file_get_contents(filename: "php://input"), associative: true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $action = $_GET['action'] ?? '';
    $response = json_encode([
        "success" => false,
        "message" => "no action"
    ]);

    switch ($action) {
        case "register":
            $response = Register(pdo: $pdo, username: $input["username"], password: $input["password"]);
            break;

        case "login":
            $response = login(pdo: $pdo, username: $input["username"], password: $input["password"]);
            break;

        default:
            throw new RuntimeException(message: "invalid action");
    }

}
catch (Exception $e) {

}