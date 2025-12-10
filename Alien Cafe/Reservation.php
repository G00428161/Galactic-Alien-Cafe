<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error   = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id          = $_SESSION['user_id'];
    $num_people       = intval($_POST['num_people'] ?? 0);
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $special_requests = trim($_POST['special_requests'] ?? '');

    // Basic validation
    if ($num_people <= 0) {
        $error = "Please enter a valid number of people.";
    } elseif (empty($reservation_date) || empty($reservation_time)) {
        $error = "Please select a date and time.";
    } else {
        // Insert reservation into database
        $stmt = $conn->prepare(
            "INSERT INTO reservations (user_id, num_people, reservation_date, reservation_time, special_requests)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("iisss",
                $user_id,
                $num_people,
                $reservation_date,
                $reservation_time,
                $special_requests
            );

            if ($stmt->execute()) {
                // This is what you wanted:
                $message = "Booking successful! Your reservation has been made.";
            } else {
                $error = "Error making reservation. Please try again.";
            }

            $stmt->close();
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Café - Reservation</title>
    <link rel="stylesheet" href="css/reservation.css">
</head>
<body>

    <nav>
        <a href="home.php">Home</a>
        <a href="menu.php">Menu</a>
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
            <input type="number" id="num_people" name="num_people" min="1" required>

            <label for="reservation_date">Date:</label>
            <input
                type="date"
                id="reservation_date"
                name="reservation_date"
                required
                min="<?php echo date('Y-m-d'); ?>"
            >

            <label for="reservation_time">Time:</label>
            <input type="time" id="reservation_time" name="reservation_time" required>

            <label for="special_requests">Special Requests (optional):</label>
            <textarea id="special_requests" name="special_requests"
                      placeholder="Any special requests..."></textarea>

            <button type="submit">Book Now</button>
        </form>
    </div>

</body>
</html>
