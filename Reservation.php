<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error   = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id          = $_SESSION['user_id'];
    $num_people       = intval($_POST['num_people'] ?? 0);
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';

    // ✅ Safely trim (avoid null warnings)
    $special_requests = isset($_POST['special_requests'])
        ? trim($_POST['special_requests'])
        : '';

    $phone = isset($_POST['phone'])
        ? trim($_POST['phone'])
        : '';

    // Basic validation
    if ($num_people <= 0) {
        $error = "Please enter a valid number of people.";
    } elseif (empty($reservation_date) || empty($reservation_time)) {
        $error = "Please select a date and time.";
    } elseif ($phone === '') {
        $error = "Please enter a contact phone number.";
    } else {
        // OPTIONAL: simple phone format check (7–20 chars: digits, +, space, -)
        if (!preg_match('/^[0-9+\s\-]{7,20}$/', $phone)) {
            $error = "Please enter a valid phone number.";
        } else {
            // ✅ Insert reservation into database
            // Make sure 'phone' column exists in reservations table
            $stmt = $conn->prepare(
                "INSERT INTO reservations 
                    (user_id, num_people, phone, reservation_date, reservation_time, special_requests) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "iissss",
                $user_id,
                $num_people,
                $phone,
                $reservation_date,
                $reservation_time,
                $special_requests
            );

            if ($stmt->execute()) {
                $message = "Your reservation has been successfully made!";
            } else {
                $error = "Error making reservation. Please try again.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Alien Cafe - Reservation</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/favicon.png">
    
    <link rel="stylesheet" href="css/reservation.css" />
</head>
<body>
    <nav>
        <a href="home.php">Home</a>
        <a href="menu.php">Main Menu</a>
        <a href="merchandise.php">Merchandise</a>
        <a href="payment.php">Payment</a>
        <a href="reservation.php">Reservation</a>
        <a href="reviews.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Reserve a Table</h1>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="num_people">Number of People:</label>
            <input type="number" id="num_people" name="num_people" min="1" required />

            <label for="phone">Contact Phone Number:</label>
            <input type="tel"
                   id="phone"
                   name="phone"
                   placeholder="e.g. 085 123 4567"
                   pattern="[0-9+\s\-]{7,20}"
                   required />

            <label for="reservation_date">Date:</label>
            <input type="date" id="reservation_date" name="reservation_date" required 
                min="<?php echo date('Y-m-d'); ?>" />

            <label for="reservation_time">Time:</label>
            <input type="time" id="reservation_time" name="reservation_time" required />

            <label for="special_requests">Special Requests (optional):</label>
            <textarea id="special_requests" name="special_requests" placeholder="Any special requests..."></textarea>

            <button type="submit">Book Now</button>
        </form>
    </div>
</body>
</html>
