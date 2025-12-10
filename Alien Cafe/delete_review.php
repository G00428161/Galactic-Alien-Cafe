<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: reviews.php");
    exit();
}

$review_id = intval($_GET['id']);

// Delete review only if it belongs to this user
$stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: reviews.php");
exit();
