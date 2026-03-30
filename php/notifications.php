<?php
session_start();
header('Content-Type: application/json');

// ✅ Step 1: Connect to the database
$host     = "localhost";
$dbname   = "db_name";
$username = "your_username";
$password = "your_password";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["notifications" => [["type" => "error", "message" => "❌ Database connection failed."]]]);
    exit;
}

// ✅ Step 2: Initialize
$userId = $_SESSION['user_id'] ?? 0;
$notifications = [];

if ($userId === 0) {
    echo json_encode(["notifications" => [["type" => "auth", "message" => "⚠️ Not logged in."]]]);
    exit;
}

// ✅ Step 3: Budget saved success (only added for immediate feedback)
if (!empty($_SESSION['budget_saved_success'])) {
    $notifications[] = [
        "type"    => "budgetSaved",
        "message" => "✅ Budget saved successfully!"
    ];
    unset($_SESSION['budget_saved_success']);
}

// ✅ Step 4: Check live category-wise budget usage
$sql = "SELECT b.category, b.amount AS budget_amount,
               COALESCE(SUM(t.amount), 0) AS total_spent
        FROM budgets AS b
        LEFT JOIN transactions AS t 
          ON t.user_id = b.user_id 
          AND t.category = b.category 
          AND t.type = 'expense' 
          AND t.date BETWEEN b.start_date AND b.end_date
        WHERE b.user_id = $userId
        GROUP BY b.id";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $category    = ucfirst($row['category']);
        $budgetAmt   = (float) $row['budget_amount'];
        $spentAmt    = (float) $row['total_spent'];

        if ($budgetAmt <= 0) continue;

        $percentUsed = ($spentAmt / $budgetAmt) * 100;

        // ✅ Real-time status check
        if ($spentAmt > $budgetAmt) {
            $overAmt = $spentAmt - $budgetAmt;
            $notifications[] = [
                "type"     => "exceededBudget",
                "category" => $category,
                "message"  => "❌ You exceeded the budget for $category by ₹" . number_format($overAmt, 2)
            ];
        } elseif ($percentUsed >= 90) {
            $notifications[] = [
                "type"     => "nearBudget",
                "category" => $category,
                "message"  => "⚠️ Your spending for $category is at " . floor($percentUsed) . "% of its budget"
            ];
        }
    }
}

// ✅ Step 5: Get income from income table and expenses from transactions
$totalIncome = 0;
$totalExpense = 0;

// 👉 Fetch income from `income` table
$sqlIncome = "SELECT COALESCE(SUM(amount), 0) AS total_income FROM income WHERE user_id = $userId";
$resIncome = $conn->query($sqlIncome);
if ($resIncome && $row = $resIncome->fetch_assoc()) {
    $totalIncome = (float) $row['total_income'];
}

// 👉 Fetch expense from `transactions` table
$sqlExpense = "SELECT COALESCE(SUM(amount), 0) AS total_expense FROM transactions WHERE user_id = $userId AND type = 'expense'";
$resExpense = $conn->query($sqlExpense);
if ($resExpense && $row = $resExpense->fetch_assoc()) {
    $totalExpense = (float) $row['total_expense'];
}

// ✅ Check condition: only alert if truly exceeded
if ($totalExpense > $totalIncome) {
    $notifications[] = [
        "type"    => "overBudgetOverall",
        "message" => "⚠️ Total expenses (₹" . number_format($totalExpense, 2) . ") exceed income (₹" . number_format($totalIncome, 2) . ")"
    ];
}

// ✅ Step 6: Return live notifications only
echo json_encode(["notifications" => $notifications]);
?>
