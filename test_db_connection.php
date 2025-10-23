<?php
// Database Connection Test
// Use this file to test different database configurations

require_once 'config_production.php';

echo "<h2>Database Connection Test</h2>";

// Test all configuration options
$db_config_options = [
    'option1' => [
        'host' => 'localhost',
        'dbname' => 'vpbankgame_luckydraw',
        'username' => 'vpbankgame_luckydraw',
        'password' => 'VpBank2025!@#',
        'charset' => 'utf8mb4'
    ],
    
    'option2' => [
        'host' => 'localhost',
        'dbname' => 'vpbankgame_luckydraw',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    
    'option3' => [
        'host' => 'localhost',
        'dbname' => 'vpbankgame_luckydraw',
        'username' => 'root',
        'password' => 'your_root_password_here',
        'charset' => 'utf8mb4'
    ]
];

foreach ($db_config_options as $option => $config) {
    echo "<h3>Testing Option: $option</h3>";
    echo "<p><strong>Config:</strong> Host: {$config['host']}, DB: {$config['dbname']}, User: {$config['username']}, Pass: " . (empty($config['password']) ? '(empty)' : '(set)') . "</p>";
    
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$config['dbname']}'");
        $result = $stmt->fetch();
        
        echo "<p style='color: green;'><strong>✅ SUCCESS!</strong> Connected to database. Found {$result['table_count']} tables.</p>";
        
        // List tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Tables:</strong> " . implode(', ', $tables) . "</p>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'><strong>❌ FAILED:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>Check which option shows <strong>SUCCESS</strong></li>";
echo "<li>Update <code>config.php</code> with the working configuration</li>";
echo "<li>If all fail, check:</li>";
echo "<ul>";
echo "<li>Database exists: <code>vpbankgame_luckydraw</code></li>";
echo "<li>Database user exists and has permissions</li>";
echo "<li>Password is correct</li>";
echo "<li>MySQL service is running</li>";
echo "</ul>";
echo "</ol>";

echo "<h3>Common Solutions:</h3>";
echo "<ul>";
echo "<li><strong>If Option 1 fails:</strong> Database user doesn't exist, use Option 2 (root)</li>";
echo "<li><strong>If Option 2 fails:</strong> Root password is set, use Option 3</li>";
echo "<li><strong>If all fail:</strong> Database doesn't exist, create it first</li>";
echo "</ul>";
?>
