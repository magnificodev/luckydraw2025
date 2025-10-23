<?php
// Lucky Draw Wheel App - Main Configuration
// This file contains all configuration settings for both XAMPP and DirectAdmin

// Database Configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'vpbankgame_luckydraw',
    'username' => 'vpbankgame_luckydraw',
    'password' => 'VpBank2025!@#',
    'charset' => 'utf8mb4'
];

// App Configuration
$app_config = [
    'name' => 'Lucky Draw Wheel - VPBank Solution Day',
    'version' => '1.0.0',
    'debug' => true, // Set to false for production
    'timezone' => 'Asia/Ho_Chi_Minh'
];

// Set timezone
date_default_timezone_set($app_config['timezone']);

// Error reporting (disable for production)
if ($app_config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database connection function
function getDatabaseConnection() {
    global $db_config;

    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        if ($GLOBALS['app_config']['debug']) {
            die("Database connection failed: " . $e->getMessage());
        } else {
            die("Database connection failed. Please contact administrator.");
        }
    }
}

// Helper function to get app config
function getAppConfig($key = null) {
    global $app_config;
    return $key ? $app_config[$key] : $app_config;
}
?>
