<?php
session_start();
include 'db_connect.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message  = "";

// Categories
$categories = ["Food", "Drinks", "Merchandise", "Overall"];

// ---------- HANDLE FORM SUBMISSIONS ----------
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

        // ADD NEW REVIEW
        if (isset($_POST['add_review'])) {
            $stmt = $conn->prepare(
                "INSERT INTO reviews (user_id, username, category, rating, review_text)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("issis", $user_id, $username, $category, $rating, $review_text);

            if ($stmt->execute()) {
                $message = "Review submitted successfully!";
            } else {
                $message = "Error submitting review. Please try again.";
            }
            $stmt->close();

        // UPDATE EXISTING REVIEW
        } elseif (isset($_POST['update_review'])) {
            $review_id = intval($_POST['review_id'] ?? 0);

            // Update only if the review belongs to this user
            $stmt = $conn->prepare(
                "UPDATE reviews
                 SET category = ?, rating = ?, review_text = ?
                 WHERE review_id = ? AND user_id = ?"
            );
            $stmt->bind_param("sisii", $category, $rating, $review_text, $review_id, $user_id);

            if ($stmt->execute()) {
                $stmt->close();
                // Redirect to clear POST & edit mode
                header("Location: reviews.php");
                exit();
            } else {
                $message = "Error updating review. Please try again.";
                $stmt->close();
            }
        }
    }
}

// ---------- EDIT MODE (IF ?edit_id=... IN URL) ----------
$edit_mode   = false;
$edit_review = null;

if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);

    $stmt = $conn->prepare(
        "SELECT * FROM reviews WHERE review_id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $edit_mode   = true;
        $edit_review = $result->fetch_assoc();
    }

    $stmt->close();
}

// Values for the form (either blank for new, or review values for edit)
$current_category = $edit_mode ? $edit_review['category'] : '';
$current_rating   = $edit_mode ? (int)$edit_review['rating'] : 0;
$current_text     = $edit_mode ? $edit_review['review_text'] : '';
$current_id       = $edit_mode ? (int)$edit_review['review_id'] : 0;

// ---------- PAGINATION ----------
$limit  = 6; // reviews per page
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total reviews
$countResult  = $conn->query("SELECT COUNT(*) AS total FROM reviews");
$rowTotal     = $countResult->fetch_assoc();
$totalReviews = (int)$rowTotal['total'];
$countResult->close();

// Fetch reviews for current page
$stmtReviews = $conn->prepare(
    "SELECT * FROM reviews ORDER BY created_at DESC LIMIT ? OFFSET ?"
);
$stmtReviews->bind_param("ii", $limit, $offset);
$stmtReviews->execute();
$reviewsResult = $stmtReviews->get_result();

// ---------- AVATAR HELPER ----------
function getAvatarPath($uid) {
    // These files must exist in images/avatars/
    $avatars = ['alien1.jpg', 'alien2.jpg', 'alien3.png', 'alien4.jpg'];
    $index   = $uid % count($avatars);
    return 'images/avatars/' . $avatars[$index];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Café | Reviews</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/favicon.png">
    
    <link rel="stylesheet" href="css/reviews.css">
</head>
<body>

<div class="nav">
    <a href="home.php">Home</a>
    <a href="menu.php">Menu</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="payment.php">Payment</a>
    <a href="reservation.php">Reservation</a>
    <a href="reviews.php">Reviews</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h1>Customer Reviews</h1>

    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- ADD / EDIT REVIEW FORM -->
    <form method="POST" class="review-form" id="review-form">
        <h2><?php echo $edit_mode ? 'Edit Your Review' : 'Leave a Review'; ?></h2>

        <?php if ($edit_mode): ?>
            <input type="hidden" name="review_id" value="<?php echo $current_id; ?>">
        <?php endif; ?>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat; ?>"
                    <?php echo ($cat === $current_category) ? 'selected' : ''; ?>>
                    <?php echo $cat; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="rating">Rating:</label>
        <select name="rating" id="rating" required>
            <option value="">Select rating</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?php echo $i; ?>"
                    <?php echo ($i === $current_rating) ? 'selected' : ''; ?>>
                    <?php echo str_repeat("⭐", $i); ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="review_text">Your Review:</label>
        <textarea name="review_text" id="review_text"
                  placeholder="Share your experience..." required><?php
            echo htmlspecialchars($current_text);
        ?></textarea>

        <button type="submit"
                name="<?php echo $edit_mode ? 'update_review' : 'add_review'; ?>">
            <?php echo $edit_mode ? 'Save Changes' : 'Submit Review'; ?>
        </button>
    </form>

    <h2>What Other Customers Say</h2>

    <div class="reviews-list">
        <?php if ($reviewsResult->num_rows > 0): ?>
            <?php while ($row = $reviewsResult->fetch_assoc()): ?>
                <?php
                    $avatarPath = getAvatarPath($row['user_id']);
                    $isOwner    = ($row['user_id'] == $user_id);
                    $cat        = $row['category'] ?? 'Overall';
                    $text       = $row['review_text'] ?? '';
                ?>
                <div class="review-card">
                    <div class="review-header">
                        <img class="avatar" src="<?php echo $avatarPath; ?>" alt="avatar">
                        <div class="review-meta">
                            <h3><?php echo htmlspecialchars($row['username']); ?></h3>
                            <p class="category">
                                Category: <?php echo htmlspecialchars($cat); ?>
                            </p>
                            <p class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo ($i <= (int)$row['rating']) ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </p>
                        </div>
                    </div>

                    <p class="review-text">
                        <?php echo nl2br(htmlspecialchars($text)); ?>
                    </p>

                    <div class="review-footer">
                        <span class="date"><?php echo $row['created_at']; ?></span>

                        <?php if ($isOwner): ?>
                            <div class="owner-actions">
                                <a href="reviews.php?edit_id=<?php echo $row['review_id']; ?>#review-form"
                                   class="edit-btn">
                                    Edit
                                </a>
                                <a href="delete_review.php?id=<?php echo $row['review_id']; ?>"
                                   class="delete-btn"
                                   onclick="return confirm('Delete this review?');">
                                    Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to leave one!</p>
        <?php endif; ?>
    </div>

    <!-- LOAD MORE BUTTON -->
    <?php
    $totalPages = ($limit > 0) ? ceil($totalReviews / $limit) : 1;
    if ($page < $totalPages): ?>
        <form method="GET" class="load-more-form">
            <input type="hidden" name="page" value="<?php echo $page + 1; ?>">
            <button type="submit" class="load-more-btn">Load More Reviews</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>
<?php
$stmtReviews->close();
$conn->close();
?>
