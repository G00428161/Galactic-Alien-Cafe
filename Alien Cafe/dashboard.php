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
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    $_SESSION['menu_cart'][] = [
        'id' => $item_id,
        'name' => $item_name,
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
<link href="https://fonts.googleapis.com/css2?family=Russo+One&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Russo One', sans-serif;
    background: url('images/GalacticGif.gif') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 0;
    color: #00ffcc;
}

h1, h2 {
    font-family: 'Russo One', sans-serif;
    text-shadow: 0 0 15px #00ffcc;
}

nav {
    background: rgba(0,0,0,0.85);
    padding: 12px 0;
    display: flex;
    justify-content: center;
    gap: 30px;
    box-shadow: 0 0 20px #00ffcc;
    position: sticky;
    top: 0;
    z-index: 1000;
}

nav a {
    color: #00ffcc;
    text-decoration: none;
    font-weight: bold;
    font-size: 18px;
    text-shadow: none; /* Removed glow from menu links */
    transition: 0.3s;
}

nav a:hover {
    color: #ff4d4d;
}

.container {
    background: rgba(15,15,30,0.9);
    margin: 30px auto;
    padding: 25px;
    width: 90%;
    max-width: 1200px;
    border-radius: 15px;
    box-shadow: 0 0 30px #00ffcc;
}

.dashboard-flex {
    display: flex;
    gap: 20px;
    margin-top: 30px;
}

.menu-section {
    flex: 3;
}

.cart-section {
    flex: 1;
    background: rgba(0,0,0,0.85);
    padding: 20px;
    border-radius: 15px;
    max-height: 600px;
    overflow-y: auto;
    box-shadow: 0 0 20px #00ffcc;
    border: 2px solid #00ffcc;
}

.cart-section ul {
    list-style: none;
    padding: 0;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
    gap: 20px;
}

.menu-item {
    background: rgba(0,0,0,0.7);
    padding: 15px;
    border-radius: 15px;
    text-align: center;
    border: 2px solid #00ffcc;
    transition: 0.2s;
}

.menu-item:hover {
    transform: scale(1.05);
    box-shadow: 0 0 25px #ff4d4d;
}

.menu-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    border:1px solid #00ffcc;
}

/* Font for menu item titles */
.menu-item h3 {
    font-family: 'Russo One', sans-serif;
    color: #00ffcc;
    font-size: 1.3em;
    text-shadow: 0 0 5px #00ffcc; /* subtle glow for readability */
}

.menu-item p {
    font-family: 'Russo One', sans-serif;
    color: #00ffcc;
}

.add-to-cart-btn {
    margin-top: 10px;
    padding: 10px;
    background-color: #00ffcc;
    color: black;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    text-shadow: 0 0 5px #000;
}

.add-to-cart-btn:hover {
    background-color: #ff4d4d;
    color: #fff;
    box-shadow: 0 0 15px #ff4d4d,0 0 25px #ff4d4d;
}

.remove-btn {
    margin-top: 5px;
    padding: 5px;
    background-color: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition:0.3s;
}

.remove-btn:hover {
    background-color: #00ffcc;
    color:black;
    box-shadow:0 0 15px #00ffcc,0 0 25px #00ffcc;
}

.checkout-btn {
    margin-top: 15px;
    padding: 12px;
    width: 100%;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    background-color: #ff4d4d;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    box-shadow: 0 0 15px #ff4d4d,0 0 25px #ff4d4d;
}

.checkout-btn:hover {
    background-color: #00ffcc;
    color:black;
    box-shadow:0 0 20px #00ffcc,0 0 35px #00ffcc;
}
</style>
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
<h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
<div class="dashboard-flex">
<div class="menu-section">
<h2>Available Menu Items</h2>
<div class="menu-grid">
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='menu-item'>";
        echo "<img src='images/{$row['image_url']}' alt='".htmlspecialchars($row['item_name'])."'>";
        echo "<h3>".htmlspecialchars($row['item_name'])."</h3>";
        echo "<p>Type: ".htmlspecialchars($row['item_type'])."</p>";
        echo "<p>Price: €".number_format($row['price'],2)."</p>";
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
$total=0;
if(!empty($_SESSION['menu_cart'])){
    foreach($_SESSION['menu_cart'] as $item){
        $total+=$item['price'];
        echo "<li>".htmlspecialchars($item['name'])." - €".number_format($item['price'],2)."
        <form method='POST' action='' style='display:inline;'>
        <input type='hidden' name='item_id_to_remove' value='{$item['id']}'>
        <button type='submit' name='remove_from_cart' class='remove-btn'>Remove</button>
        </form>
        </li>";
    }
    echo "<li><strong>Total: €".number_format($total,2)."</strong></li>";
} else {
    echo "<li>Your food cart is empty.</li>";
}
?>
</ul>
<?php if(!empty($_SESSION['menu_cart'])): ?>
<form action="payment.php" method="get">
<button type="submit" class="checkout-btn">Go to Payment</button>
</form>
<?php endif; ?>
</div>
</div>
</div>
</body>
</html>
