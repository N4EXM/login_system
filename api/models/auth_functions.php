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
                    "data" => [
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

    try {

        if (!isset($username) || !isset($password)) {
            echo json_encode([
                "success" => false,
                "message" => "username or password is empty."
            ]);
        }
        else {

            // check if the user is in the database 
            $stmt = $pdo -> prepare("SELECT id FROM users WHERE username = ?");
            $stmt -> execute([$username]);
            
            if ($stmt -> fetch(PDO::FETCH_ASSOC)) {
                
                echo json_encode([
                    "success" => false,
                    "message" => "user already exists"
                ]);

            }
            else {
                
                // hash the password
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                
                // insert the user
                $stmt = $pdo -> prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                $success = $stmt->execute([$username, $passwordHash]);

                if ($success) {
                    login($pdo, $username, $password);
                }

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
