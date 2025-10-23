<?php
// Run database improvements
require_once 'config.php';

try {
    $pdo = getDatabaseConnection();
    
    echo "Running database improvements...\n";
    
    // Read and execute SQL file
    $sql = file_get_contents('database_improvements.sql');
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch(PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠ Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n✅ Database improvements completed successfully!\n";
    echo "Added tables:\n";
    echo "- export_history (for tracking exports)\n";
    echo "- admin_activity_log (for admin actions)\n";
    echo "- system_settings (for system configuration)\n";
    echo "- dashboard_stats view (for dashboard data)\n";
    echo "- prize_analytics view (for prize statistics)\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
