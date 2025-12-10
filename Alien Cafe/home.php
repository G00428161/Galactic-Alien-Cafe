<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alien Café | Home</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>

<nav>
    <a href="home.php">Home</a>
    <a href="menu.php">Menu</a>
    <a href="merchandise.php">Merchandise</a>
    <a href="payment.php">Payment</a>
    <a href="reservation.php">Reservations</a>
    <a href="reviews.php">Reviews</a>
    <a href="logout.php">Logout</a>
  

    <?php if (isset($_SESSION['user_id'])): ?>
        <a class="nav-right" href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
    <?php else: ?>
        <a class="nav-right" href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>

<!-- HERO -->
<header class="hero">
    <div class="hero-content">
        <h1>Welcome to Alien Café</h1>
        <p>
            A futuristic café experience combining cosmic flavours, exclusive merchandise,
            and seamless online reservations.
        </p>

        <div class="hero-buttons">
            <a href="menu.php" class="btn primary">View Menu</a>
            <a href="reservation.php" class="btn secondary">Reserve a Table</a>
        </div>
    </div>
</header>

<!-- FEATURES -->
<section class="features">
    <div class="feature-card">
        <h3>🍸 Signature Drinks</h3>
        <p>Crafted cosmic beverages and glowing specialty coffees.</p>
    </div>

    <div class="feature-card">
        <h3>🛸 Exclusive Merchandise</h3>
        <p>Alien-themed apparel, accessories, and collectibles.</p>
    </div>

    <div class="feature-card">
        <h3>🎧 Atmosphere & Experience</h3>
        <p>Neon ambience, chill synthwave, and futuristic interiors.</p>
    </div>
</section>

<!-- INFO BANNER -->
<section class="info">
    <p>
        Open daily · Online ordering available · Secure payments · Live reservations
    </p>
</section>

<footer>
    <p>© <?php echo date('Y'); ?> Alien Café. All rights reserved.</p>
</footer>

</body>
</html>
