<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialise cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ===========================
   FUNCTION: CHECK IF ITEM NEEDS SIZE
=========================== */
function requires_size($itemName) {
    $name = strtolower($itemName);

    $keywords = [
        'hoodie',
        'gown',
        'night gown',
        'dress',
        'shirt',
        'suit',
        'bodysuit',
        'body suit',
        'top'
    ];

    foreach ($keywords as $k) {
        if (strpos($name, $k) !== false) {
            return true;
        }
    }
    return false;
}

/* ===========================
   ADD TO CART (MERCH ONLY)
=========================== */
if (isset($_POST['add_to_cart'])) {

    $item_name  = $_POST['item_name'];
    $item_price = (float)$_POST['item_price'];
    $quantity   = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $item_size  = $_POST['item_size'] ?? null;

    // Enforce size for clothing items
    if (requires_size($item_name) && empty($item_size)) {
        $_SESSION['error'] = "Please select a size for {$item_name}.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Add quantity as individual items
    for ($i = 0; $i < $quantity; $i++) {
        $_SESSION['cart'][] = [
            'name'  => $item_name,
            'price' => $item_price,
            'size'  => $item_size,
            'type'  => 'merch'
        ];
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* ===========================
   REMOVE ONE ITEM FROM CART
=========================== */
if (isset($_POST['remove_from_cart'])) {

    $index = (int)$_POST['cart_index'];

    if (isset($_SESSION['cart'][$index]) &&
        $_SESSION['cart'][$index]['type'] === 'merch') {

        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* ===========================
   FETCH MERCHANDISE
=========================== */
$sql = "SELECT merch_id, name, price, image_url
        FROM Merchandise
        WHERE availability = 'available'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Café Merchandise</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/merchandise.css">
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

<?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="main-content">

<!-- LEFT: MERCH ITEMS -->
<div class="merch-container">

<?php if ($result && $result->num_rows > 0): ?>
<?php while ($row = $result->fetch_assoc()): ?>

<div class="merch-item">

    <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>"
         alt="<?php echo htmlspecialchars($row['name']); ?>">

    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
    <p class="price">€<?php echo number_format($row['price'], 2); ?></p>

    <form method="POST">

        <input type="hidden" name="item_name"
               value="<?php echo htmlspecialchars($row['name']); ?>">

        <input type="hidden" name="item_price"
               value="<?php echo htmlspecialchars($row['price']); ?>">

        <?php if (requires_size($row['name'])): ?>
            <label class="size-label">Size:</label>
            <select name="item_size" class="size-select" required>
                <option value="" disabled selected>Select Size</option>
                <option value="S">Small (S)</option>
                <option value="M">Medium (M)</option>
                <option value="L">Large (L)</option>
                <option value="XL">Extra Large (XL)</option>
            </select>
        <?php endif; ?>

        <div class="qty-row">
            <label>Qty:</label>
            <input type="number"
                   name="quantity"
                   min="1"
                   max="20"
                   value="1"
                   class="qty-input">
        </div>

        <button type="submit" name="add_to_cart" class="add-to-cart-btn">
            Add to Cart
        </button>

    </form>
</div>

<?php endwhile; ?>
<?php else: ?>
<p>No merchandise available.</p>
<?php endif; ?>

</div>

<!-- RIGHT: CART -->
<div class="cart-container">
<h2>Your Cart</h2>

<ul>
<?php
$grouped = [];
$total = 0;

foreach ($_SESSION['cart'] as $index => $item) {

    if ($item['type'] !== 'merch') continue;

    $key = $item['name'] . '|' . ($item['size'] ?? '');

    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'name' => $item['name'],
            'price' => $item['price'],
            'size' => $item['size'],
            'quantity' => 1,
            'indexes' => [$index]
        ];
    } else {
        $grouped[$key]['quantity']++;
        $grouped[$key]['indexes'][] = $index;
    }
}

if (!empty($grouped)):
foreach ($grouped as $data):

$line_total = $data['quantity'] * $data['price'];
$total += $line_total;
?>

<li>
<?php echo htmlspecialchars($data['name']); ?>
<?php if (!empty($data['size'])): ?>
(Size: <?php echo htmlspecialchars($data['size']); ?>)
<?php endif; ?>
(x<?php echo $data['quantity']; ?>)
– €<?php echo number_format($line_total, 2); ?>

<form method="POST" style="display:inline;">
<input type="hidden" name="cart_index"
       value="<?php echo $data['indexes'][0]; ?>">
<button type="submit" name="remove_from_cart" class="remove-btn">
Remove
</button>
</form>
</li>

<?php endforeach; ?>

<li class="cart-total">
Total: €<?php echo number_format($total, 2); ?>
</li>

<li>
<form action="payment.php">
<button class="checkout-btn">Go to Payment</button>
</form>
</li>

<?php else: ?>
<li>Your cart is empty.</li>
<?php endif; ?>
</ul>

</div>
</div>

</body>
</html>
