<?php
// Admin configuration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_SESSION_KEY', 'admin_logged_in');
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// Use same database configuration as main app
require_once '../config.php';

// Use the same PDO connection from main config
$pdo = getDatabaseConnection();
?>
