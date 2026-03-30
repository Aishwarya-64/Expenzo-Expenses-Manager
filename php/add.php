<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost"); // Match your frontend URL
error_reporting(E_ALL); // Enable error reporting

// Database connection
$conn = new mysqli("localhost", "username", "password", "database_name");


if ($conn->connect_error) {
    error_log("DB Connection Failed: " . $conn->connect_error);
    die(json_encode([
        "status" => "error", 
        "message" => "Database connection failed",
        "error_code" => "DB_CONNECTION_ERROR"
    ]));
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode([
        "status" => "error",
        "message" => "Only POST requests are allowed",
        "error_code" => "INVALID_METHOD"
    ]));
}

// Validate session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode([
        "status" => "error",
        "message" => "Not authenticated",
        "error_code" => "AUTH_REQUIRED"
    ]));
}

// Get and validate user ID
$user_id = (int)$_SESSION['user_id'];
if ($user_id < 1) {
    die(json_encode([
        "status" => "error",
        "message" => "Invalid user session",
        "error_code" => "INVALID_USER"
    ]));
}

// Validate input
$required_fields = ['category', 'amount'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die(json_encode([
            "status" => "error",
            "message" => "Missing required field: $field",
            "error_code" => "MISSING_FIELD"
        ]));
    }
}

// Sanitize and validate data
$category = trim($conn->real_escape_string($_POST['category']));
$amount = (float)$_POST['amount'];

if ($amount <= 0 || !is_numeric($_POST['amount'])) {
    die(json_encode([
        "status" => "error",
        "message" => "Amount must be a positive number",
        "error_code" => "INVALID_AMOUNT"
    ]));
}

try {
    // Insert with prepared statement
    $stmt = $conn->prepare("INSERT INTO transactions 
                          (user_id, category, amount, type, date) 
                          VALUES (?, ?, ?, 'expense', NOW())");
    $stmt->bind_param("isd", $user_id, $category, $amount);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    // Log successful transaction
    error_log("New expense recorded - User: $user_id, Amount: $amount, Category: $category");
    
    // Return success with transaction ID
    echo json_encode([
        "status" => "success",
        "message" => "Expense recorded successfully",
        "transaction" => [
            "id" => $stmt->insert_id,
            "user_id" => $user_id,
            "category" => $category,
            "amount" => $amount,
            "date" => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    error_log("Expense Submission Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to record expense",
        "error" => $e->getMessage(),
        "error_code" => "TRANSACTION_FAILED"
    ]);
} finally {
    $stmt->close();
    $conn->close();
}
?>