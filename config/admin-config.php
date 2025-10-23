<?php
// Admin configuration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// Database configuration (same as main app)
$host = 'localhost';
$dbname = 'vpbankgame_luckydraw';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
