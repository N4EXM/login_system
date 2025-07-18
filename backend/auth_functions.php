<?php 

require_once "db_connection.php";

$cookieSettings = [
    'expires' => time() + 86400 * 30, // 30 days
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false, // true in production
    'httponly' => true,
    'samesite' => 'Lax'
];

function loginUser ($pdo, $username, $password): void {

    global $cookieSettings;

    try {

        // validating input
        $username = trim(string: $username);
        $password = trim(string: $password);

        if (empty($username) || empty($password)) {
            echo json_encode(value: [
                "status" => false,
                "message" => "username or password is empty"
            ]);
        }

        // get the user from the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // get the user from the database 
        if ($user && password_verify(password: $password, hash: $user["password_hash"])) {

            session_regenerate_id(delete_old_session: true);

            // set session variables
            $_SESSION["id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["logged_in"] = true;

            // set secure cookie
            setcookie(name: "auth_session", value: session_id(), expires_or_options: $cookieSettings);
         
            echo json_encode([
                "success"=> true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);

        }
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid credentials'
        ]);
    }
    catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode([
            "success" => "false",
            "message" => "Database error"
        ]);
    }

}

function registerUser($pdo, $username, $password) {
    try {
        // Validate input
        $username = trim($username);
        $password = trim($password);
        
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false, 
                'message' => 'Username already exists'
            ]);
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $success = $stmt->execute([$username, $passwordHash]);

        if ($success) {
            return loginUser($pdo, $username, $password); // Auto-login after registration
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed'
        ]);
    } 
    catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Database error occurred'
        ]);
    }
}

function checkAuthStatus() {
    if (isset($_SESSION['logged_in']) && 
        isset($_COOKIE['auth_session']) && 
        $_COOKIE['auth_session'] === session_id()) {
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

function logoutUser() {
    global $cookieSettings;
    
    try {
        // Unset all session variables
        $_SESSION = array();

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();

        // Delete auth cookie
        setcookie('auth_session', '', $cookieSettings + ['expires' => time() - 3600]);

        return true;
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        return false;
    }
}