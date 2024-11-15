<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Initialize feedback message
$feedback = "";

// Process the join requests and fetch the data
$data = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['inner_join'])) {
        // Inner Join Query: Select users who have made purchases and the corresponding products
        $sql = "SELECT users.id AS user_id, users.username, products.id AS product_id, products.title, purchases.purchase_date
                FROM purchases
                INNER JOIN users ON purchases.user_id = users.id
                INNER JOIN products ON purchases.product_id = products.id";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
    } elseif (isset($_POST['left_join'])) {
        // Left Join Query: Select all users, with matching purchases and products (show only user data if no purchase)
        $sql = "SELECT users.id AS user_id, users.username, products.id AS product_id, products.title, purchases.purchase_date
                FROM users
                LEFT JOIN purchases ON users.id = purchases.user_id
                LEFT JOIN products ON purchases.product_id = products.id";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
    } elseif (isset($_POST['right_join'])) {
        // Right Join Query: Select all products, with matching users and purchases (show only product data if no user)
        $sql = "SELECT users.id AS user_id, users.username, products.id AS product_id, products.title, purchases.purchase_date
                FROM products
                RIGHT JOIN purchases ON products.id = purchases.product_id
                RIGHT JOIN users ON purchases.user_id = users.id";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f4f9; }
        .navbar { background-color: #3399ff; }
        .navbar a { color: #ffffff !important; }
        .container { margin-top: 30px; }
        .btn-join { background-color: #3399ff; border: none; color: white; }
        .btn-join:hover { background-color: #66ccff; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Dashboard</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h1 class="text-center">Purchase History</h1>

    <!-- Join Buttons -->
    <div class="text-center">
        <form method="post" action="history.php">
            <button type="submit" name="inner_join" class="btn btn-join">Inner Join</button>
            <button type="submit" name="left_join" class="btn btn-join">Left Join</button>
            <button type="submit" name="right_join" class="btn btn-join">Right Join</button>
        </form>
    </div>

    <!-- Display Table for Joined Data -->
    <?php if ($data): ?>
        <table class="table table-bordered mt-5">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Product ID</th>
                    <th>Product Title</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['product_id']) ? htmlspecialchars($row['product_id']) : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['title']) ? htmlspecialchars($row['title']) : 'N/A'; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['purchase_date']) ? htmlspecialchars($row['purchase_date']) : 'N/A'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No data available for this join.</p>
    <?php endif; ?>
</div>

</body>
</html>
