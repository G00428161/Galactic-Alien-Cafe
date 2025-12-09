<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize the cart if it's not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart logic (using name + price only)
if (isset($_POST['add_to_cart'])) {
    $item_name  = $_POST['item_name'];
    $item_price = (float)$_POST['item_price'];

    $_SESSION['cart'][] = [
        'name'  => $item_name,
        'price' => $item_price
    ];

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Remove from cart logic (match by name)
if (isset($_POST['remove_from_cart'])) {
    $item_name_to_remove = $_POST['item_name_to_remove'];
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['name'] === $item_name_to_remove) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // re-index
            break;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Cafe - Menu</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <!-- External CSS for menu page -->
    <link rel="stylesheet" href="css/menu.css">

    <!-- Main JS (fetch calls) -->
    <script src="js/main.js" defer></script>
</head>
<body>

    <!-- Top Navigation Menu -->
    <nav>
        <a href="home.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="merchandise.php">Merchandise</a>
        <a href="payment.php">Payment</a>
        <a href="reservation.php">Reservation</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

        <h2>Available Menu Items (loaded with fetch)</h2>
        <!-- JS will fill this with HTML from get_menu_items.php -->
        <div class="menu-grid">
            <p>Loading menu items...</p>
        </div>

        <h2>Your Cart</h2>
        <ul class="cart-list">
            <?php
            $total = 0;
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    $total += $item['price'];
                    ?>
                    <li>
                        <?php echo htmlspecialchars($item['name']); ?>
                        - €<?php echo number_format($item['price'], 2); ?>
                        <form action="" method="POST" class="inline-form">
                            <input type="hidden" name="item_name_to_remove"
                                   value="<?php echo htmlspecialchars($item['name']); ?>">
                            <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
                        </form>
                    </li>
                    <?php
                }
                ?>
                <li class="cart-total">
                    <strong>Total: €<?php echo number_format($total, 2); ?></strong>
                </li>
                <?php
            } else {
                echo "<li>Your cart is empty.</li>";
            }
            ?>
        </ul>

        <!-- AJAX Cart Summary (JSON + HTML) -->
        <h2>Cart Summary (AJAX)</h2>
        <button id="refreshCartBtn" class="add-to-cart-btn">Refresh Cart Summary (AJAX)</button>
        <div id="cart-summary-ajax" style="margin-top: 15px;">
            <p>Click the button above to load cart summary using fetch().</p>
        </div>
    </div>
</body>
</html>

