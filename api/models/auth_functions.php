<?php

// Cookie settings (shared between functions)
$cookieSettings = [
    'expires' => time() + 86400 * 30, // 30 days
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false, // true in production
    'httponly' => true,
    'samesite' => 'Lax'
];

function login($pdo, $username, $password): void {

    global $cookieSettings;

    try {

        if (!isset($username) || !isset($password)) {
            echo json_encode([
                "success" => false,
                "message" => "username or password is empty."
            ]);
        }
        else {
            
            // check if the user is in the database 
            $stmt = $pdo -> prepare("SELECT * FROM users WHERE username = ?");
            $stmt -> execute([$username]);
            $user = $stmt -> fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user["password_hash"])) {

                session_regenerate_id(delete_old_session: true);

                $_SESSION["username"] = $user["username"];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                // set secure cookie 
                setcookie("auth_session", session_id(), $cookieSettings);

                echo json_encode([
                    "success" => true,
                    "user" => [
                        "username" => $user["username"],
                        "role" => $user["role"],
                    ]
                ]);

            }
            else {

                echo json_encode([
                    "success" => false,
                    "message" => "username or password is wrong"
                ]);

            }

        }


    }
    catch (PDOException $e) {

        error_log("Login error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);

    }

}

function Register($pdo, $username, $password): void {
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