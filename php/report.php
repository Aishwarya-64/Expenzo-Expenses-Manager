<?php
session_start();

// --- Database Connection ---
$host = "localhost";
$user = "your_username";
$password = "your_password";
$dbname = "db_name";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

header('Content-Type: application/json');

// --- Ensure User is Logged In ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

$response = [
    'income' => 0,
    'expenses' => 0,
    'balance' => 0,
    'categories' => []
];

// --- Total Income (from income table) ---
$incomeQuery = "SELECT IFNULL(SUM(amount), 0) AS total_income FROM income WHERE user_id = ?";
$stmt = $conn->prepare($incomeQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalIncome);
$stmt->fetch();
$response['income'] = $totalIncome;
$stmt->close();

// --- Total Expenses (from transactions table) ---
$expenseQuery = "SELECT IFNULL(SUM(amount), 0) AS total_expenses FROM transactions WHERE user_id = ?";
$stmt = $conn->prepare($expenseQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalExpenses);
$stmt->fetch();
$response['expenses'] = $totalExpenses;
$stmt->close();

// --- Balance Calculation ---
$response['balance'] = $response['income'] - $response['expenses'];

// --- Expense Category Breakdown ---
$categoryQuery = "SELECT category, SUM(amount) AS total FROM transactions WHERE user_id = ? GROUP BY category";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['categories'][] = $row;
}
$stmt->close();

echo json_encode($response);
?>
