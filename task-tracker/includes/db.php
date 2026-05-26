<?php
// includes/db.php
// Database connection settings for XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Default XAMPP MySQL user
define('DB_PASS', '');            // Default XAMPP MySQL password (empty)
define('DB_NAME', 'task_tracker_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
