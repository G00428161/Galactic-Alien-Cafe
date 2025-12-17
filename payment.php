<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function groupCartItems(array $items, string $type): array {
    $grouped = [];
    $total = 0;

    foreach ($items as $index => $item) {
        if (!isset($item['type']) || $item['type'] !== $type) continue;

        $name  = $item['name'] ?? 'Unknown';
        $price = (float)($item['price'] ?? 0);
        $size  = $item['size'] ?? '';

        if ($type === 'menu') {
            $id = $item['id'] ?? $name;
            $key = "menu|" . $id;
        } else {
            $key = "merch|" . $name . "|" . $size;
        }

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'name'    => $name,
                'price'   => $price,
                'qty'     => 1,
                'size'    => $size,
                'indexes' => [$index]
            ];
        } else {
            $grouped[$key]['qty']++;
            $grouped[$key]['indexes'][] = $index;
        }
    }

    foreach ($grouped as $g) {
        $total += $g['price'] * $g['qty'];
    }

    return [$grouped, $total];
}

// Remove one unit
if (isset($_POST['remove_from_cart'])) {
    $cart_index = (int)($_POST['cart_index'] ?? -1);

    if ($cart_index >= 0 && isset($_SESSION['cart'][$cart_index])) {
        unset($_SESSION['cart'][$cart_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Group carts
[$foodGrouped, $foodTotal]   = groupCartItems($_SESSION['cart'], 'menu');
[$merchGrouped, $merchTotal] = groupCartItems($_SESSION['cart'], 'merch');
$grandTotal = $foodTotal + $merchTotal;

// Payment handling + merch delivery handling
$paymentSuccess = false;
$paymentError = '';

$hasMerch = !empty($merchGrouped);

// Defaults
$delivery_method = 'collect';
$address_line1 = '';
$address_line2 = '';
$city = '';
$eircode = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['pay_now'])) {

    // Payment fields
    $cardholder = trim($_POST['cardholder_name'] ?? '');
    $cardnumber = preg_replace('/\s+/', '', ($_POST['card_number'] ?? ''));
    $expiry     = trim($_POST['expiry_date'] ?? '');
    $cvv        = trim($_POST['cvv'] ?? '');

    // Delivery fields (ONLY matters if merch exists)
    $delivery_method = $_POST['delivery_method'] ?? 'collect';
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $eircode = trim($_POST['eircode'] ?? '');

    // Basic validation
    if ($cardholder === '') {
        $paymentError = "Please enter the cardholder name.";
    } elseif (!preg_match('/^\d{16}$/', $cardnumber)) {
        $paymentError = "Card number must be 16 digits.";
    } elseif ($expiry === '') {
        $paymentError = "Please select an expiry date.";
    } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
        $paymentError = "CVV must be 3 or 4 digits.";
    } elseif (empty($_SESSION['cart'])) {
        $paymentError = "Your cart is empty.";
    } else {

        // ✅ Delivery validation ONLY if merchandise exists
        if ($hasMerch) {
            if (!in_array($delivery_method, ['collect', 'delivery'], true)) {
                $paymentError = "Please choose a valid merchandise delivery option.";
            } elseif ($delivery_method === 'delivery') {
                if ($address_line1 === '' || $city === '' || $eircode === '') {
                    $paymentError = "Please fill in Address Line 1, City and Eircode for delivery.";
                }
            }
        }

        if ($paymentError === '') {
            $paymentSuccess = true;

            // Store delivery choice in session (for receipt / later DB save)
            $_SESSION['merch_delivery'] = [
                'method' => $delivery_method,
                'address_line1' => $address_line1,
                'address_line2' => $address_line2,
                'city' => $city,
                'eircode' => $eircode
            ];

            // Clear cart after successful payment
            $_SESSION['cart'] = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Café | Payment</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/payment.css">
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

<div class="container">
    <h1>Checkout</h1>

    <?php if ($paymentError): ?>
        <p style="text-align:center; color:#ff4d4d; font-weight:bold;">
            <?php echo htmlspecialchars($paymentError); ?>
        </p>
    <?php endif; ?>

    <div class="cart-split">

        <!-- FOOD CART -->
        <div class="cart-box">
            <h2>Food Cart</h2>

            <div class="cart-summary">
                <?php if (!empty($foodGrouped)): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Total (€)</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($foodGrouped as $g):
                            $lineTotal = $g['price'] * $g['qty']; ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($g['name']); ?>
                                    <?php if ($g['qty'] > 1): ?>
                                        (x<?php echo $g['qty']; ?>)
                                    <?php endif; ?>
                                </td>
                                <td>€<?php echo number_format($lineTotal, 2); ?></td>
                                <td>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="cart_index" value="<?php echo (int)$g['indexes'][0]; ?>">
                                        <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td><strong>Food Total</strong></td>
                            <td><strong>€<?php echo number_format($foodTotal, 2); ?></strong></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center;">No food items in your cart.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- MERCH CART -->
        <div class="cart-box">
            <h2>Merch Cart</h2>

            <div class="cart-summary">
                <?php if (!empty($merchGrouped)): ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Total (€)</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($merchGrouped as $g):
                            $lineTotal = $g['price'] * $g['qty']; ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($g['name']); ?>
                                    <?php if (!empty($g['size'])): ?>
                                        (Size: <?php echo htmlspecialchars($g['size']); ?>)
                                    <?php endif; ?>
                                    <?php if ($g['qty'] > 1): ?>
                                        (x<?php echo $g['qty']; ?>)
                                    <?php endif; ?>
                                </td>
                                <td>€<?php echo number_format($lineTotal, 2); ?></td>
                                <td>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="cart_index" value="<?php echo (int)$g['indexes'][0]; ?>">
                                        <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td><strong>Merch Total</strong></td>
                            <td><strong>€<?php echo number_format($merchTotal, 2); ?></strong></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center;">No merch items in your cart.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <h2>Grand Total: €<?php echo number_format($grandTotal, 2); ?></h2>

    <form class="payment-form" method="POST" action="">

        <?php if (!empty($merchGrouped)): ?>
            <div class="merch-delivery">
                <h3>Merchandise Delivery</h3>

                <label>
                    <input type="radio" name="delivery_method" value="collect"
                        <?php echo ($delivery_method === 'collect') ? 'checked' : ''; ?>>
                    Collect in-store (Free)
                </label>

                <label>
                    <input type="radio" name="delivery_method" value="delivery"
                        <?php echo ($delivery_method === 'delivery') ? 'checked' : ''; ?>>
                    Deliver to my house
                </label>

                <div class="address-fields">
                    <input type="text" name="address_line1" placeholder="Address Line 1"
                           value="<?php echo htmlspecialchars($address_line1); ?>">
                    <input type="text" name="address_line2" placeholder="Address Line 2 (optional)"
                           value="<?php echo htmlspecialchars($address_line2); ?>">
                    <input type="text" name="city" placeholder="City"
                           value="<?php echo htmlspecialchars($city); ?>">
                    <input type="text" name="eircode" placeholder="Eircode / Postcode"
                           value="<?php echo htmlspecialchars($eircode); ?>">
                </div>

                <p style="font-size:13px; opacity:0.85; margin-top:8px;">
                    * Address is only required if you choose home delivery.
                </p>
            </div>
        <?php endif; ?>

        <input type="text" name="cardholder_name" placeholder="Cardholder Name"
               value="<?php echo isset($_POST['cardholder_name']) ? htmlspecialchars($_POST['cardholder_name']) : ''; ?>"
               required>

        <input type="text" name="card_number" placeholder="Card Number (16 digits)" maxlength="16"
               value="<?php echo isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : ''; ?>"
               required>

        <input type="month" name="expiry_date"
               value="<?php echo isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : ''; ?>"
               required>

        <input type="text" name="cvv" placeholder="CVV" maxlength="4"
               value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>"
               required>

        <button type="submit" name="pay_now">Pay Now</button>
    </form>
</div>

<!-- SUCCESS MODAL -->
<?php if ($paymentSuccess): ?>
<div class="modal-overlay">
    <div class="modal-box">
        <h3>✅ Payment Successful!</h3>
        <p>Thank you for ordering from <strong>Alien Café</strong> 👽☕</p>

        <?php if ($hasMerch): ?>
            <p><strong>Merch Delivery:</strong>
                <?php echo ($delivery_method === 'delivery') ? 'Home Delivery' : 'Collect In-Store'; ?>
            </p>
        <?php endif; ?>

        <div class="modal-actions">
            <a class="modal-btn" href="menu.php">Back to Menu</a>
            <a class="modal-btn secondary" href="home.php">Go Home</a>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>
