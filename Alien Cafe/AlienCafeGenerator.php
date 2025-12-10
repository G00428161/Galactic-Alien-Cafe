<!DOCTYPE html>
<html>

<head>
    <title>Creating Database Table for Alien Cafe</title>
</head>

<body>

<?php
//include 'db_connect.php';

// Connect to MySQL server
$conn = new mysqli("zainproject", "root", "",NULL, 3304);

$dbname = "alien_cafe_db";

// Drop database if exists
$sql = "DROP DATABASE IF EXISTS $dbname;";
if ($conn->query($sql) === TRUE) {
  echo "Database dropped successfully (if it existed)<br>";
} else {
  echo "Error dropping database: " . $conn->error;
}

// Create database
$sql = "CREATE DATABASE $dbname;";
if ($conn->query($sql) === TRUE) {
  echo "Database created successfully<br>";
} else {
  echo "Error creating database: " . $conn->error;
}

// Connect to the new database
$conn = new mysqli("zainproject", "root", "", $dbname,3304);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create Users table
$sql = "CREATE TABLE Users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

if ($conn->query($sql) === TRUE) {
  echo "Users table created successfully<br>";
} else {
  echo "Error creating Users table: " . $conn->error;
}

// Create Menu_Items table
$sql = "CREATE TABLE Menu_Items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(100) NOT NULL,
  item_type ENUM('drink', 'food', 'dessert') NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  availability ENUM('available', 'unavailable') DEFAULT 'available',
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

if ($conn->query($sql) === TRUE) {
  echo "Menu_Items table created successfully<br>";
} else {
  echo "Error creating Menu_Items table: " . $conn->error;
}

// Create Orders table
$sql = "CREATE TABLE Orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;";

if ($conn->query($sql) === TRUE) {
  echo "Orders table created successfully<br>";
} else {
  echo "Error creating Orders table: " . $conn->error;
}

// Create Order_Items table
$sql = "CREATE TABLE Order_Items (
  order_item_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES Menu_Items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB;";

if ($conn->query($sql) === TRUE) {
  echo "Order_Items table created successfully<br>";
} else {
  echo "Error creating Order_Items table: " . $conn->error;
}

// Insert sample menu items
$sql = "INSERT INTO Menu_Items (item_name, item_type, price, availability, image_url) VALUES
('Galactic Latte', 'drink', 4.99, 'available', 'galactic_latte.jpg'),
('Meteorite Muffin', 'dessert', 3.49, 'available', 'meteorite_muffin.jpg'),
('Alien Cheesecake', 'dessert', 5.99, 'available', 'alien_cheesecake.jpg'),
('Cosmic Smoothie', 'drink', 4.49, 'available', 'cosmic_smoothie.jpg'),
('Space Pizza', 'food', 6.50, 'available', 'space_pizza.webp'),
('Space Bagel', 'food', 2.99, 'available', 'space_bagel.jpg');";

if ($conn->query($sql) === TRUE) {
  echo "Sample menu items inserted successfully<br>";
} else {
  echo "Error inserting menu items: " . $conn->error;
}

$conn->close();
?>

</body>
</html>
