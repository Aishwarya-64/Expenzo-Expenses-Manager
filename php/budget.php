<?php
session_start();
header('Content-Type: text/plain');

// Database config
$host     = "localhost";
$dbname   = "db_name";
$username = "your_username";
$password = "your_password";

// Connect to DB
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// Get and sanitize POST values
$category   = trim($_POST['category'] ?? '');
$period     = trim($_POST['period'] ?? '');
$startDate  = $_POST['startDate'] ?? '';
$endDate    = $_POST['endDate'] ?? '';
$amount     = $_POST['amount'] ?? null;

$missingFields = [];

if (empty($category))   $missingFields[] = 'category';
if (empty($period))     $missingFields[] = 'period';
if (empty($startDate))  $missingFields[] = 'startDate';
if (empty($endDate))    $missingFields[] = 'endDate';
if (!isset($amount) || !is_numeric($amount)) $missingFields[] = 'amount';

if (!empty($missingFields)) {
    http_response_code(400);
    echo "Missing fields: " . implode(', ', $missingFields);
    exit;
}

// Prepare insert
$sql = "INSERT INTO budgets (category, period, start_date, end_date, amount, user_id)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    die("SQL Prepare Failed: " . $conn->error);
}

// You should store user_id in session when logged in
$user_id = $_SESSION['user_id'] ?? 0;

$stmt->bind_param("ssssdi", $category, $period, $startDate, $endDate, $amount, $user_id);

if ($stmt->execute()) {
    $_SESSION['budget_saved_success'] = true; // Used by notifications.php
    echo "success";
} else {
    http_response_code(500);
    echo "SQL Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
