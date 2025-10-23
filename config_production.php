<?php
// Lucky Draw Wheel App - Production Configuration
// Multiple database connection options for different hosting environments

// Option 1: DirectAdmin/cPanel style (most common)
$db_config_options = [
    'option1' => [
        'host' => 'localhost',
        'dbname' => 'vpbankgame_luckydraw',
        'username' => 'vpbankgame_luckydraw',
        'password' => 'VpBank2025!@#',
        'charset' => 'utf8mb4'
    ],
    
    // Option 2: Root user (if database user not created yet)
    'option2' => [
        'host' => 'localhost',
        'dbname' => 'vpbankgame_luckydraw',
        'username' => 'root',
        'password' => '', // Empty password
        'charset' => 'utf8mb4'
    ],
    
    // Option 3: Root user with password
    'option3' => [
        'host' => 'localhost',
        'dbname' => 'vpbankgame_luckydraw',
        'username' => 'root',
        'password' => 'your_root_password_here',
        'charset' => 'utf8mb4'
    ],
    
    // Option 4: Different database name format
    'option4' => [
        'host' => 'localhost',
        'dbname' => 'username_vpbankgame_luckydraw', // Common cPanel format
        'username' => 'username_vpbankgame_luckydraw',
        'password' => 'VpBank2025!@#',
        'charset' => 'utf8mb4'
    ]
];

// Choose which option to use (change this number)
$selected_option = 'option1'; // Change to option2, option3, or option4 as needed

$db_config = $db_config_options[$selected_option];

// App Configuration
$app_config = [
    'name' => 'Lucky Draw Wheel - VPBank Solution Day',
    'version' => '1.0.0',
    'debug' => false, // Set to false for production
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

// Database connection function with better error handling
function getDatabaseConnection() {
    global $db_config;

    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        // Log error for debugging
        error_log("Database connection failed: " . $e->getMessage());
        
        if ($GLOBALS['app_config']['debug']) {
            die("Database connection failed: " . $e->getMessage() . 
                "<br><br>Current config:<br>" . 
                "Host: " . $db_config['host'] . "<br>" .
                "Database: " . $db_config['dbname'] . "<br>" .
                "Username: " . $db_config['username'] . "<br>" .
                "Password: " . (empty($db_config['password']) ? '(empty)' : '(set)') . "<br>");
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

// Test database connection (for debugging)
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        return "Database connection successful!";
    } catch(Exception $e) {
        return "Database connection failed: " . $e->getMessage();
    }
}
?>
