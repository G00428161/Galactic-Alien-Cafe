<?php
session_start();
include 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Basic PHP validation (Req 5) ---

    // Required fields
    if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = "All fields are required.";
    }
    // Username length / basic chars
    elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = "Username must be between 3 and 20 characters.";
    }
    elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    }
    // Email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    // Password rules
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // --- Check if username or email already exist using prepared statement (Req 4) ---

        $checkStmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
        if (!$checkStmt) {
            $error = "Database error. Please try again later.";
        } else {
            $checkStmt->bind_param("ss", $username, $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                $error = "Username or email already taken.";
            } else {
                // --- Hash password and insert user (Req 6 + Req 4) ---

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $conn->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
                if (!$insertStmt) {
                    $error = "Database error. Please try again later.";
                } else {
                    $insertStmt->bind_param("sss", $username, $email, $hashed_password);

                    if ($insertStmt->execute()) {
                        $success = "Registration successful! You can now log in.";
                    } else {
                        $error = "Error creating account. Please try again.";
                    }

                    $insertStmt->close();
                }
            }

            $checkStmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Cafe Register</title>

    <!-- Favicon (Req 9) -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <!-- External CSS (Req 10) -->
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="register-container">
        <h2>Alien Cafe Register</h2>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <p class="login-link">
                <a href="login.php">Go to Login</a>
            </p>
        <?php endif; ?>

        <form method="post" action="">
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
                value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
            >
            <input
                type="email"
                name="email"
                placeholder="Email"
                required
                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
            >
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >
            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm Password"
                required
            >
            <input type="submit" value="Register">
        </form>

        <p class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>
