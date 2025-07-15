<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require 'db_connection.php';

function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Set secure HTTP-only cookie
        setcookie(
            'auth_session', 
            session_id(), 
            [
                'expires' => time() + 86400 * 30, // 30 days
                'path' => '/',
                'domain' => 'localhost',
                'secure' => false, // true in production with HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ];
    }
    
    return ['success' => false, 'message' => 'Invalid credentials'];
}

function checkAuth() {
    if (isset($_SESSION['user_id']) && isset($_COOKIE['auth_session']) && $_COOKIE['auth_session'] === session_id()) {
        return [
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ]
        ];
    }
    return ['authenticated' => false];
}

function logout() {
    // Clear session
    session_unset();
    session_destroy();
    
    // Clear cookie
    setcookie('auth_session', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => 'localhost'
    ]);
    
    return ['success' => true];
}

function register($username, $password) {
    global $pdo;
    
    // Basic validation
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username and password are required'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters'];
    }

    try {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert new user (with or without email)
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $success = $stmt->execute([$username, $passwordHash]);

        return $success 
            ? ['success' => true, 'message' => 'Registration successful'] 
            : ['success' => false, 'message' => 'Registration failed'];
            
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}
?>