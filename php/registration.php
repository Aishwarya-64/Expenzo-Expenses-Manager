<?php
session_start();
header("Content-Type: application/json");

// DB connection
$conn = new mysqli("localhost", "username", "password", "database_name");


if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Input validation
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    $errors = [];
    if (empty($username)) $errors[] = "Username is required";
    if (!$email) $errors[] = "Valid email is required";
    if (strlen($password) < 8) $errors[] = "Password must be 8+ characters";
    if ($password !== $confirm_password) $errors[] = "Passwords don't match";

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode(", ", $errors)]);
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
        exit;
    }

    // Hash password and create user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        // Set sessions (matches login.php)
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
        
        echo json_encode([
            "status" => "success", 
            "message" => "Registration successful",
            "user_id" => $_SESSION['user_id'] // For debugging
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed: " . $conn->error]);
    }
    
    $stmt->close();
}
$conn->close();
?>