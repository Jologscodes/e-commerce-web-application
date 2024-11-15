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
        // Inner Join Query: Select users, products, and purchases where they match
        $sql = "SELECT users.id AS user_id, users.username, products.id AS product_id, products.title, purchases.purchase_date
                FROM purchases
                INNER JOIN users ON purchases.user_id = users.id
                INNER JOIN products ON purchases.product_id = products.id";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
    } elseif (isset($_POST['left_join'])) {
        // Left Join Query: Select all users with their created_at timestamp
        $sql = "SELECT users.id AS user_id, users.username, users.created_at
                FROM users"; // Ensure created_at is selected
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
    } elseif (isset($_POST['right_join'])) {
        // Right Join Query: Select all products, with matching users and purchases
        $sql = "SELECT products.id AS product_id, products.title, products.description, products.price
                FROM products
                RIGHT JOIN purchases ON products.id = purchases.product_id"; // Remove unnecessary joins
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History Log</title>
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
    <h1 class="text-center">History Log</h1>

    <!-- Join Buttons -->
    <div class="text-center mb-3">
        <form method="post" action="historylog.php">
            <button type="submit" name="left_join" class="btn btn-join">Left Join (Users Data)</button>
            <button type="submit" name="right_join" class="btn btn-join">Right Join (Products Data)</button>
            <button type="submit" name="inner_join" class="btn btn-join">Inner Join (Purchase Data)</button>
        </form>
    </div>

    <!-- Display Table for Left Join Data (Users) -->
    <?php if (isset($_POST['left_join']) && $data): ?>
        <table class="table table-bordered mt-5">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Display Table for Right Join Data (Products) -->
    <?php if (isset($_POST['right_join']) && $data): ?>
        <table class="table table-bordered mt-5">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Display Table for Inner Join Data (Purchases) -->
    <?php if (isset($_POST['inner_join']) && $data): ?>
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
                        <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['purchase_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!$data): ?>
        <p class="text-center">No data available for this join.</p>
    <?php endif; ?>
</div>

</body>
</html>
