<?php
session_start();
include 'db_connect.php'; // optional if you want to fetch more info from DB

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Example user info (replace with DB fetch if available)
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';
$email = $_SESSION['email'] ?? 'guest@example.com';

// Optionally, you can fetch more info from your database:
// $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id=?");
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $stmt->bind_result($username, $email);
// $stmt->fetch();
// $stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Alien Cafe Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Audiowide&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Orbitron', sans-serif;
    background: url('images/BigHead.jpg') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 0;
    color: #00ffcc;
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
    transition: 0.3s;
}

nav a:hover {
    color: #ff4d4d;
}

.container {
    background: rgba(15, 15, 30, 0.9);
    margin: 50px auto;
    padding: 40px;
    width: 80%;
    max-width: 600px;
    border-radius: 15px;
    box-shadow: 0 0 30px #00ffcc;
    text-align: center;
}

h1 {
    font-family: 'Audiowide', cursive;
    text-shadow: 0 0 15px #00ffcc;
    margin-bottom: 30px;
}

.profile-info {
    text-align: left;
    margin: 0 auto;
    max-width: 400px;
    font-size: 1.2em;
}

.profile-info p {
    margin: 15px 0;
}

.logout-btn {
    margin-top: 30px;
    padding: 12px 25px;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    background-color: #ff4d4d;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    box-shadow: 0 0 15px #ff4d4d, 0 0 25px #ff4d4d;
    transition: 0.3s;
}

.logout-btn:hover {
    background-color: #00ffcc;
    color: black;
    box-shadow: 0 0 20px #00ffcc, 0 0 35px #00ffcc;
}
</style>
</head>
<body>
<nav>
    <a href="dashboard.php">Home</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="payment.php">Payment</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h1>Your Profile</h1>
    <div class="profile-info">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
    </div>

    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>
</body>
</html>
