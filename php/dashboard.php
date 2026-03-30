<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login.html");
    exit;
}

// Get session data
$userEmail = $_SESSION['email'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 50px;
            text-align: center;
        }

        .dashboard-box {
            background: white;
            padding: 30px;
            max-width: 500px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: crimson;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="dashboard-box">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Your email: <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>

        <a href="../php/logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
