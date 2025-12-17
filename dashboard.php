<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['menu_cart'])) {
    $_SESSION['menu_cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
    $item_id    = $_POST['item_id'];
    $item_name  = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    $_SESSION['menu_cart'][] = [
        'id'    => $item_id,
        'name'  => $item_name,
        'price' => $item_price
    ];

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['remove_from_cart'])) {
    $item_id_to_remove = $_POST['item_id_to_remove'];
    foreach ($_SESSION['menu_cart'] as $index => $item) {
        if ($item['id'] == $item_id_to_remove) {
            unset($_SESSION['menu_cart'][$index]);
            $_SESSION['menu_cart'] = array_values($_SESSION['menu_cart']);
            break;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$sql = "SELECT * FROM Menu_Items WHERE availability='available'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Alien Cafe Dashboard</title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="images/favicon.png">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">

<!-- ✅ External CSS (no CSS in PHP files) -->
<link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
<nav>
    <a href="dashboard.php">Home</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="payment.php">Payment</a>
    <a href="reviews.php">User Reviews</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h1>

    <div class="dashboard-flex">
        <div class="menu-section">
            <h2>Available Menu Items</h2>

            <div class="menu-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='menu-item'>";
                        echo "<img src='images/{$row['image_url']}' alt='".htmlspecialchars($row['item_name'])."'>";
                        echo "<h3>".htmlspecialchars($row['item_name'])."</h3>";
                        echo "<p>Type: ".htmlspecialchars($row['item_type'])."</p>";
                        echo "<p>Price: €".number_format($row['price'], 2)."</p>";
                        echo "<form method='POST' action=''>
                                <input type='hidden' name='item_id' value='{$row['item_id']}'>
                                <input type='hidden' name='item_name' value='".htmlspecialchars($row['item_name'])."'>
                                <input type='hidden' name='item_price' value='{$row['price']}'>
                                <button type='submit' name='add_to_cart' class='add-to-cart-btn'>Add to Cart</button>
                              </form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No menu items available.</p>";
                }
                ?>
            </div>
        </div>

        <div class="cart-section">
            <h2>Your Cart</h2>

            <ul>
                <?php
                $total = 0;

                if (!empty($_SESSION['menu_cart'])) {
                    foreach ($_SESSION['menu_cart'] as $item) {
                        $total += (float)$item['price'];

                        echo "<li>".htmlspecialchars($item['name'])." - €".number_format($item['price'], 2)."
                                <form method='POST' action='' class='inline-remove-form'>
                                    <input type='hidden' name='item_id_to_remove' value='".htmlspecialchars($item['id'])."'>
                                    <button type='submit' name='remove_from_cart' class='remove-btn'>Remove</button>
                                </form>
                              </li>";
                    }

                    echo "<li><strong>Total: €".number_format($total, 2)."</strong></li>";
                } else {
                    echo "<li>Your food cart is empty.</li>";
                }
                ?>
            </ul>

            <?php if (!empty($_SESSION['menu_cart'])): ?>
                <form action="payment.php" method="get">
                    <button type="submit" class="checkout-btn">Go to Payment</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
