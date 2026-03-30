<?php
// ========================
// SECURITY & SESSION SETUP
// ========================
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

// ========================
// HEADERS FOR CORS & JSON
// ========================
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");

// ========================
// DATABASE CONNECTION
// ========================
require_once 'config.php';

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]));
}

// ========================
// INPUT PROCESSING
// ========================
try {
    // Accept both JSON and form-data
    if ($_SERVER["CONTENT_TYPE"] === 'application/json') {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }

    // Validate input
    $email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $data['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        die(json_encode([
            "status" => "error",
            "message" => "Invalid email format"
        ]));
    }

    if (empty($password)) {
        http_response_code(400);
        die(json_encode([
            "status" => "error",
            "message" => "Password cannot be empty"
        ]));
    }

    // ========================
    // LOGIN PROCESSING
    // ========================
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        die(json_encode([
            "status" => "error",
            "message" => "User not found"
        ]));
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        die(json_encode([
            "status" => "error",
            "message" => "Incorrect password"
        ]));
    }

    // Create session
    session_regenerate_id(true);
    $_SESSION = [
        'user_id' => (int)$user['id'],
        'email' => $email,
        'username' => $user['username'],
        'authenticated' => true
    ];

    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => [
            "id" => $user['id'],
            "username" => $user['username']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error"
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>