<?php
session_start();
include 'db_connect.php';

$error = '';  

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic PHP validation (Req 5)
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Please enter both username and password.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Prepared statement (Req 4)
        $stmt = $conn->prepare("SELECT user_id, username, password FROM Users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();

                // Verify password (Req 6)
                if (password_verify($password, $row['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['username'] = $row['username'];

                    // COOKIE: remember username (Req 8)
                    if (isset($_POST['remember_me'])) {
                        // 30 days
                        setcookie("remember_username", $username, time() + (30 * 24 * 60 * 60), "/");
                    } else {
                        // Clear cookie if unchecked
                        if (isset($_COOKIE['remember_username'])) {
                            setcookie("remember_username", "", time() - 3600, "/");
                        }
                    }

               header("Location: home.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "Username not found.";
            }

            $stmt->close();
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}

$conn->close();

// Get username from cookie if exists (Req 8)
$rememberedUsername = isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Cafe Login</title>

    <!-- Favicon (Req 9) -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <!-- External CSS (Req 10) -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Alien Cafe Login</h2>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
                autofocus
                value="<?php echo htmlspecialchars($rememberedUsername); ?>"
            >
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <label class="remember-me">
                <input
                    type="checkbox"
                    name="remember_me"
                    <?php echo $rememberedUsername !== '' ? 'checked' : ''; ?>
                >
                Remember my username
            </label>

            <input type="submit" value="Login">
        </form>

        <p class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </div>
</body>
</html>
