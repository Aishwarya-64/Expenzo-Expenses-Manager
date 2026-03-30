<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$db = "db_name";
$user = "username";
$pass = "password";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;
if (!$userId) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

$sql = "SELECT username, email, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "username" => $row['username'],
        "email" => $row['email'],
        "current_password" => $row['password']
    ]);
} else {
    echo json_encode(["success" => false, "error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
