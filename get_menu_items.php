<?php
session_start();
include 'db_connect.php';

// Optional: require login
if (!isset($_SESSION['user_id'])) {
    echo "<p>Please <a href='login.php'>log in</a> to see the menu.</p>";
    exit();
}

// Fetch menu items from database
$sql = "SELECT item_id, item_name, item_type, price, image_url 
        FROM Menu_Items
        WHERE availability = 'available'"; 

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="menu-item">
            <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($row['item_name']); ?>">

            <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
            <p>Type: <?php echo htmlspecialchars($row['item_type']); ?></p>
            <p>Price: €<?php echo number_format($row['price'], 2); ?></p>

            <!-- This form posts back to menu.php -->
            <form action="menu.php" method="POST" class="inline-form">
                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row['item_id']); ?>">
                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>">
                <input type="hidden" name="item_price" value="<?php echo htmlspecialchars($row['price']); ?>">

                <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                    Add to Cart
                </button>
            </form>
        </div>
        <?php
    }
} else {
    echo "<p>No menu items available.</p>";
}

$conn->close();
