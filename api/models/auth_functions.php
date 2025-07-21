<?php

// Cookie settings (shared between functions)
$cookieSettings = [
    'expires' => time() + 86400 * 30, // 30 days
    'path' => '/',
    'domain' => 'localhost', // Change for production
    'secure' => false,      // true in production
    'httponly' => true,
    'samesite' => 'Lax'
];

// function login($pdo, $username, $password): void {

//     global $cookieSettings;

//     try {

//         if (!isset($username) || !isset($password)) {
//             echo json_encode([
//                 "success" => false,
//                 "message" => "username or password is empty."
//             ]);
//         }
//         else {
            
//             // check if the user is in the database 
//             $stmt = $pdo -> prepare("SELECT * FROM users WHERE username = ?");
//             $stmt -> execute([$username]);
//             $user = $stmt -> fetch(PDO::FETCH_ASSOC);

//             if ($user && password_verify($password, $user["password_hash"])) {

//                 session_regenerate_id(delete_old_session: true);

//                 $_SESSION["id"] = $user["id"];
//                 $_SESSION["username"] = $user["username"];
//                 $_SESSION['role'] = $user['role'];
//                 $_SESSION['logged_in'] = true;

//                 // set secure cookie 
//                 setcookie("auth_session", session_id(), $cookieSettings);

//                 echo json_encode([
//                     "success" => true,
//                     "user" => [
//                         "id" => $user["id"],
//                         "username" => $user["username"],
//                         "role" => $user["role"],
//                     ]
//                 ]);

//             }
//             else {

//                 echo json_encode([
//                     "success" => false,
//                     "message" => "username or password is wrong"
//                 ]);

//             }

//         }


//     }
//     catch (PDOException $e) {

//         error_log("Login error: " . $e->getMessage());
//         echo json_encode([
//             'success' => false,
//             'message' => 'Database error occurred'
//         ]);

//     }

// }


function login($pdo, $username, $password): void {
    global $cookieSettings;

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400 * 30,
            'cookie_secure' => false,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }

    try {
        // Validate input
        if (empty($username) || empty($password)) {
            throw new InvalidArgumentException('Username and password are required');
        }

        // Check user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('Invalid credentials');
        }

        // Regenerate session ID
        session_regenerate_id(true);

        // Set session variables
        $_SESSION = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'logged_in' => true
        ];

        // Set secure cookie
        setcookie('auth_session', session_id(), $cookieSettings);

        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ]);
        exit();

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

function register($pdo, $username, $password): void {
    header('Content-Type: application/json');
    
    $username = trim($username);
    $password = trim($password);

    try {
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Username or password is empty."
            ]);
            exit();
        }

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            http_response_code(409); // Conflict
            echo json_encode([
                "success" => false,
                "message" => "User already exists"
            ]);
            exit();
        }
        
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        if ($passwordHash === false) {
            throw new Exception("Password hashing failed");
        }
        
        // Insert the user
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $success = $stmt->execute([$username, $passwordHash]);

        if ($success) {
            http_response_code(201); // Created
            echo json_encode([
                "success" => true,
                "message" => "Registration successful"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Registration failed"
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error",
            "error" => $e->getMessage()
        ]); 
    }

}

// function auth_check(): void {

//     if (session_start() === PHP_SESSION_NONE) {
//         session_start();
//     }

//     if (isset($_SESSION["id"]) && isset($_COOKIE["auth_session"]) && $_COOKIE["auth_session"] === session_id()) {

//         echo json_encode([
//             "authenticated" => true,
//             "user" => [
//                 "id" => $_SESSION["id"],
//                 "username" => $_SESSION["username"],
//                 "role" => $_SESSION["role"]
//             ]
//         ]);

//     }

//     echo json_encode([
//         "authenticated" => false
//     ]);

// }

function auth_check(): void {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400 * 30, // 30 days
            'cookie_secure' => false,       // true in production
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }

    // Prepare default response
    $response = ['authenticated' => false];

    // Check if user is authenticated
    if (isset($_SESSION['id'], $_COOKIE['auth_session']) && 
        $_COOKIE['auth_session'] === session_id()) {
        $response = [
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ]
        ];
    }

    // Send JSON response and terminate
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}