<?php
// Lucky Draw Wheel App - Installation Script
require_once 'config.php';

// Check if database exists and create if needed
try {
    $pdo = getDatabaseConnection();
    echo "<h2>✅ Database connection successful!</h2>";
    echo "<p>Database: " . $db_config['dbname'] . "</p>";
    echo "<p>Host: " . $db_config['host'] . "</p>";
    echo "<p>Username: " . $db_config['username'] . "</p>";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'participants'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>✅ Table 'participants' already exists</h3>";

        // Show table info
        $stmt = $pdo->query("DESCRIBE participants");
        echo "<h4>Table structure:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Show record count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM participants");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>Total participants: " . $count . "</strong></p>";

    } else {
        echo "<h3>❌ Table 'participants' does not exist</h3>";
        echo "<p>Please run the database.sql file to create the table.</p>";
    }

} catch (Exception $e) {
    echo "<h2>❌ Database connection failed!</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<h3>Setup Instructions:</h3>";
    echo "<ol>";
    echo "<li>Make sure XAMPP is running</li>";
    echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
    echo "<li>Create a new database named 'vpbankgame_luckydraw'</li>";
    echo "<li>Create a user 'vpbankgame_luckydraw' with password 'VpBank2025!@#'</li>";
    echo "<li>Grant all privileges to the user on the database</li>";
    echo "<li>Import the database.sql file</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
}

// Check file permissions
echo "<h3>File Permissions Check:</h3>";
$files_to_check = [
    'index.php' => 'Main application file',
    'process.php' => 'Form handler',
    'api/config.php' => 'API configuration',
    'assets/css/style.css' => 'CSS styles',
    'assets/js/main.js' => 'JavaScript',
    '.htaccess' => 'Apache configuration'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file);
        echo "<p>✅ $description ($file) - " . ($readable ? "Readable" : "Not readable") . "</p>";
    } else {
        echo "<p>❌ $description ($file) - File not found</p>";
    }
}

// Check assets
echo "<h3>Assets Check:</h3>";
$assets = [
    'assets/images/background-1.png',
    'assets/images/background-2.png',
    'assets/images/background-3.png',
    'assets/images/start-button.png',
    'assets/images/spin-button.png',
    'assets/images/wheel.png',
    'assets/images/wheel-pointer.png'
];

$gifts = glob('assets/images/gifts/*.png');
echo "<p>Gift images: " . count($gifts) . " files found</p>";

foreach ($assets as $asset) {
    if (file_exists($asset)) {
        echo "<p>✅ " . basename($asset) . "</p>";
    } else {
        echo "<p>❌ " . basename($asset) . " - Missing</p>";
    }
}

echo "<h3>🎉 Installation Complete!</h3>";
echo "<p><a href='index.php'>Go to Lucky Draw Wheel App</a></p>";
?>
