<?php
session_start();
include 'db_connect.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialise cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ADD TO CART (MENU ITEMS)
if (isset($_POST['add_to_cart'])) {
    $item_id    = $_POST['item_id'];
    $item_name  = $_POST['item_name'];
    $item_price = (float)$_POST['item_price'];

    $_SESSION['cart'][] = [
        'id'    => $item_id,
        'name'  => $item_name,
        'price' => $item_price,
        'type'  => 'menu' // important tag
    ];

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// REMOVE FROM CART (ONE UNIT)
if (isset($_POST['remove_from_cart'])) {
    $cart_index = (int)$_POST['cart_index'];

    if (isset($_SESSION['cart'][$cart_index]) &&
        isset($_SESSION['cart'][$cart_index]['type']) &&
        $_SESSION['cart'][$cart_index]['type'] === 'menu'
    ) {
        unset($_SESSION['cart'][$cart_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Fix indexes
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch menu items
$sql = "
    SELECT item_id, item_name, item_type, price, image_url
    FROM Menu_Items
    WHERE availability = 'available'
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Alien Café | Main Menu</title>
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>

<div class="nav">
    <a href="home.php">Home</a>
    <a href="menu.php">Main Menu</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="payment.php">Payment</a>
    <a href="reservation.php">Reservation</a>
    <a href="reviews.php">Reviews</a>
    <a href="logout.php">Logout</a>
</div>

<div class="page-wrapper">
    <h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <h2 class="page-subtitle">Intergalactic Menu</h2>

    <div class="main-content">

        <!-- LEFT SIDE — MENU ITEMS -->
        <div class="menu-container">
            <div class="menu-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="menu-item">
                            <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>"
                                 alt="<?php echo htmlspecialchars($row['item_name']); ?>">

                            <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                            <p class="type">Type: <?php echo htmlspecialchars($row['item_type']); ?></p>
                            <p class="price">€<?php echo number_format($row['price'], 2); ?></p>

                            <form action="" method="POST">
                                <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>">
                                <input type="hidden" name="item_price" value="<?php echo $row['price']; ?>">
                                <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No menu items available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT SIDE — CART -->
        <div class="cart-container">
            <h2>Your Cart</h2>
            <ul>
                <?php
                // Group repeated menu items
                $grouped = [];
                $total = 0;

                foreach ($_SESSION['cart'] as $index => $item) {

                    // Show ONLY menu items
                    if (!isset($item['type']) || $item['type'] !== 'menu') {
                        continue;
                    }

                    $id = $item['id'];

                    if (!isset($grouped[$id])) {
                        $grouped[$id] = [
                            'name'     => $item['name'],
                            'price'    => $item['price'],
                            'quantity' => 1,
                            'indexes'  => [$index]
                        ];
                    } else {
                        $grouped[$id]['quantity']++;
                        $grouped[$id]['indexes'][] = $index;
                    }
                }

                if (!empty($grouped)):
                    foreach ($grouped as $id => $data):
                        $line_total = $data['price'] * $data['quantity'];
                        $total += $line_total;
                ?>
                        <li>
                            <?php echo htmlspecialchars($data['name']); ?>
                            (x<?php echo $data['quantity']; ?>)
                            – €<?php echo number_format($line_total, 2); ?>

                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="cart_index" value="<?php echo $data['indexes'][0]; ?>">
                                <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
                            </form>
                        </li>
                <?php endforeach; ?>

                    <li class="cart-total-line">
                        <strong>Total: €<?php echo number_format($total, 2); ?></strong>
                    </li>

                <?php else: ?>
                    <li>Your cart is empty.</li>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</div>

</body>
</html>
