<?php
$servername = "zainproject";  // your host
$username = "root";            // your DB username
$password = "";                // your DB password
$dbname = "alien_cafe_db";     // your database
$port = 3304;                  // your port number

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
