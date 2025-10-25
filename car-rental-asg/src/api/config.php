<?php
// Database configuration
// Rename this file to "config.php" and fill in your local database details.

// === XAMPP Configuration ===
// $host = 'localhost';
// $db   = 'webdev_asg_db';
// $user = 'root';
// $pass = ''; // Default XAMPP password is empty

// === Docker Configuration ===
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'car-rental-db';
$user = getenv('DB_USER') ?: 'user1';
$pass = getenv('DB_PASS') ?: '1234';

?>