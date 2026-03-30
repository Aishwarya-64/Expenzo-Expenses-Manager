<?php
session_start();

$host = "localhost";
$db = "db_name";
$user = "your_username";
$pass = "your_password";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ DB connection failed");
}

$userId = $_SESSION['user_id'] ?? 0;
$newPassword = $_POST['new_password'] ?? '';

if (!$userId || empty($newPassword)) {
    echo "invalid";
    exit;
}

$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newPassword, $userId);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
