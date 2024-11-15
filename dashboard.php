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

// Process the buy request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION["id"];

    // Check if the product exists
    $productCheck = $pdo->prepare("SELECT * FROM products WHERE id = :product_id");
    $productCheck->bindParam(":product_id", $product_id, PDO::PARAM_INT);
    $productCheck->execute();

    if ($productCheck->rowCount() > 0) {
        // Check if the user has already purchased this product (optional)
        $purchaseCheck = $pdo->prepare("SELECT * FROM purchases WHERE user_id = :user_id AND product_id = :product_id");
        $purchaseCheck->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $purchaseCheck->bindParam(":product_id", $product_id, PDO::PARAM_INT);
        $purchaseCheck->execute();

        if ($purchaseCheck->rowCount() == 0) {
            // Insert the purchase record into the purchases table
            $sql = "INSERT INTO purchases (user_id, product_id) VALUES (:user_id, :product_id)";
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $feedback = "<div class='alert alert-success text-center'>Product purchased successfully!</div>";
                } else {
                    $feedback = "<div class='alert alert-danger text-center'>An error occurred. Please try again.</div>";
                }
                
                unset($stmt);
            }
        } else {
            $feedback = "<div class='alert alert-warning text-center'>You have already purchased this product.</div>";
        }
    } else {
        $feedback = "<div class='alert alert-danger text-center'>Product not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #000000; /* Set background to black */
            color: white; /* Set text color to white for readability */
        }
        .navbar {
            background-color: #3399ff;
        }
        .navbar a {
            color: #ffffff !important;
        }
        .product-container {
            margin-top: 30px;
        }
        .card {
            width: 100%;
            margin-bottom: 20px;
            background-color: #333; /* Dark background for product cards */
        }
        .card-img-top {
            width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
            background-color: #e9ecef;
        }
        .btn-buy {
            background-color: #3399ff;
            border: none;
            color: white;
        }
        .btn-buy:hover {
            background-color: #66ccff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">Dashboard</a>
    <div class="collapse navbar-collapse">dashboard.php
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="historylog.php">History</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container product-container">
    <h1 class="text-center">Products</h1>
    
    <!-- Display feedback messages -->
    <?php if (!empty($feedback)) echo $feedback; ?>

    <div class="row">
        <?php
        // Fetch products from the database
        $sql = "SELECT id, image, title, description, price FROM products";
        if ($result = $pdo->query($sql)) {
            while ($row = $result->fetch()) {
                echo '<div class="col-md-4">';
                echo '<div class="card">';
                echo '<img src="' . htmlspecialchars($row["image"]) . '" class="card-img-top" alt="' . htmlspecialchars($row["title"]) . '">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($row["title"]) . '</h5>';
                echo '<p class="card-text">' . htmlspecialchars($row["description"]) . '</p>';
                echo '<p class="card-text">Price: $' . htmlspecialchars($row["price"]) . '</p>';
                
                // Purchase form
                echo '<form method="post" action="dashboard.php">';
                echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
                echo '<button type="submit" class="btn btn-buy">Buy Now</button>';
                echo '</form>';

                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">No products available.</p>';
        }
        ?>
    </div>
</div>

</body>
</html>
