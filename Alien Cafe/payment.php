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

// Handle Remove from Cart
if (isset($_POST['remove_from_cart'])) {
    $item_name_to_remove = $_POST['item_name_to_remove'];
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['name'] === $item_name_to_remove) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
            break;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$payment_error = '';
$payment_success = '';

// Handle Payment Submission (PHP validation – Req 5)
if (isset($_POST['pay_now'])) {

    $cardholder_name = trim($_POST['cardholder_name'] ?? '');
    $card_number     = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $expiry_date     = $_POST['expiry_date'] ?? '';
    $cvv             = trim($_POST['cvv'] ?? '');

    // Basic checks
    if ($cardholder_name === '' || $card_number === '' || $expiry_date === '' || $cvv === '') {
        $payment_error = "All payment fields are required.";
    }
    // Card number: 16 digits
    elseif (!preg_match('/^\d{16}$/', $card_number)) {
        $payment_error = "Card number must be exactly 16 digits.";
    }
    // CVV: 3–4 digits
    elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
        $payment_error = "CVV must be 3 or 4 digits.";
    }
    // Expiry not in the past
    else {
        // expiry_date from <input type="month"> is "YYYY-MM"
        $now = new DateTime('first day of this month');
        $expObj = DateTime::createFromFormat('Y-m', $expiry_date);
        if (!$expObj) {
            $payment_error = "Invalid expiry date format.";
        } else {
            // set to first day of that month
            $expObj->modify('first day of this month');
            if ($expObj < $now) {
                $payment_error = "Card expiry date cannot be in the past.";
            }
        }
    }

    // If everything is valid
    if ($payment_error === '') {
        // Example: store basic, non-sensitive payment summary in session (Req 7)
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'];
        }

        $_SESSION['last_payment'] = [
            'cardholder_name' => $cardholder_name,
            'amount'          => $total,
            'time'            => date('Y-m-d H:i:s')
        ];

        // In real life you would NOT store full card number or CVV anywhere.

        // Clear cart after "payment"
        $_SESSION['cart'] = [];

        $payment_success = "Payment successful! Thank you, " . htmlspecialchars($cardholder_name) . ".";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Cafe Payment</title>

    <!-- Favicon (Req 9) -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <!-- External CSS (Req 10) -->
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <div class="nav">
        <a href="Home.php">Home</a>
        <a href="menu.php">Main Menu</a>
        <a href="merchandise.php">Merchandise</a>
        <a href="payment.php">Payment</a>
        <a href="reservation.php">Reservation</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h1>Your Cart</h1>
        <div class="cart-summary">
            <?php if (!empty($_SESSION['cart'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price (€)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $index => $item):
                            $total += $item['price'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>€<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="POST" action="" class="inline-form">
                                    <input type="hidden" name="item_name_to_remove" value="<?php echo htmlspecialchars($item['name']); ?>">
                                    <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong>€<?php echo number_format($total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <h2>Payment Details</h2>

        <?php if ($payment_error !== ''): ?>
            <div class="message error"><?php echo htmlspecialchars($payment_error); ?></div>
        <?php endif; ?>

        <?php if ($payment_success !== ''): ?>
            <div class="message success"><?php echo $payment_success; ?></div>
        <?php endif; ?>

        <form class="payment-form" method="POST" action="">
            <input
                type="text"
                name="cardholder_name"
                placeholder="Cardholder Name"
                required
                value="<?php echo isset($cardholder_name) ? htmlspecialchars($cardholder_name) : ''; ?>"
            >

            <input
                type="text"
                name="card_number"
                placeholder="Card Number (16 digits)"
                maxlength="19"
                required
                value="<?php echo isset($card_number) ? htmlspecialchars($card_number) : ''; ?>"
            >

            <input
                type="month"
                name="expiry_date"
                required
                value="<?php echo isset($expiry_date) ? htmlspecialchars($expiry_date) : ''; ?>"
            >

            <input
                type="text"
                name="cvv"
                placeholder="CVV"
                maxlength="4"
                required
                value="<?php echo isset($cvv) ? htmlspecialchars($cvv) : ''; ?>"
            >

            <button type="submit" name="pay_now">Pay Now</button>
        </form>
    </div>
</body>
</html>
