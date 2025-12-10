<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Categories must match reviews.php
$categories = ["Food", "Drinks", "Merchandise", "Overall"];

if (!isset($_GET['id'])) {
    header("Location: reviews.php");
    exit();
}

$review_id = intval($_GET['id']);

// Fetch review and ensure it belongs to this user
$stmt = $conn->prepare("SELECT * FROM reviews WHERE review_id = ? AND user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    header("Location: reviews.php");
    exit();
}

$review = $result->fetch_assoc();
$stmt->close();

// Handle POST update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rating      = intval($_POST['rating'] ?? 0);
    $category    = $_POST['category'] ?? '';
    $review_text = trim($_POST['review_text'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $message = "Please choose a rating between 1 and 5.";
    } elseif (!in_array($category, $categories)) {
        $message = "Please select a valid category.";
    } elseif ($review_text === "") {
        $message = "Please write something in your review.";
    } else {
        $update = $conn->prepare(
            "UPDATE reviews SET category = ?, rating = ?, review_text = ? WHERE review_id = ? AND user_id = ?"
        );
        $update->bind_param("sisii", $category, $rating, $review_text, $review_id, $user_id);

        if ($update->execute()) {
            $update->close();
            $conn->close();
            header("Location: reviews.php");
            exit();
        } else {
            $message = "Error updating review. Please try again.";
            $update->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Review</title>
    <link rel="stylesheet" href="css/reviews.css">
</head>
<body>

<div class="nav">
    <a href="home.php">Home</a>
    <a href="menu.php">Menu</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="reviews.php">Reviews</a>
    <a href="reservation.php">Reservation</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h1>Edit Your Review</h1>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" class="review-form">
        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat; ?>"
                    <?php if ($review['category'] === $cat) echo 'selected'; ?>>
                    <?php echo $cat; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="rating">Rating:</label>
        <select name="rating" id="rating" required>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?php echo $i; ?>"
                    <?php if ((int)$review['rating'] === $i) echo 'selected'; ?>>
                    <?php echo str_repeat("⭐", $i); ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="review_text">Your Review:</label>
        <textarea name="review_text" id="review_text" required><?php
            echo htmlspecialchars($review['review_text']);
        ?></textarea>

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
