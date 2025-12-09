<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart on this page
if (isset($_POST['add_to_cart'])) {
    $item_name  = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    $_SESSION['cart'][] = [
        'name'  => $item_name,
        'price' => (float)$item_price
    ];

    // Stay on the same page after adding
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch merchandise from database (Req 1)
// Make sure your table/column names match these
$sql = "SELECT merch_id, name, price, image_url 
        FROM Merchandise 
        WHERE availability = 'available'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Cafe Merchandise</title>

    <!-- Favicon (Req 9) -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <!-- External CSS (Req 10) -->
    <link rel="stylesheet" href="css/merchandise.css">
</head>
<body>
<div class="nav">
    <a href="home.php">Home</a>
    <a href="menu.php">Main Menu</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="payment.php">Payment</a>
    <a href="reservation.php">Reservation</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <div class="merch-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="merch-item">
                    <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>"
                         alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p class="price">€<?php echo number_format($row['price'], 2); ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                        <input type="hidden" name="item_price" value="<?php echo htmlspecialchars($row['price']); ?>">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No merchandise available at the moment.</p>
        <?php endif; ?>
    </div>

    <div class="cart-container">
        <h2>Your Cart</h2>
        <?php if (!empty($_SESSION['cart'])): ?>
            <ul>
                <?php 
                $total = 0;
                foreach ($_SESSION['cart'] as $cart_item):
                    $total += $cart_item['price'];
                ?>
                    <li>
                        <?php echo htmlspecialchars($cart_item['name']); ?>
                        - €<?php echo number_format($cart_item['price'], 2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="cart-total">Total: €<?php echo number_format($total, 2); ?></div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
