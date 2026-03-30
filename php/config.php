<?php
// Real database credentials
$host = "localhost";
$user = "root";
$pass = "";  // Your actual MySQL password
$db   = "expenses_management";

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection error
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}
?>
