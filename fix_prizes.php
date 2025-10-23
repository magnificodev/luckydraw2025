<?php
// Prizes Data Check & Fix Script
// Use this to check and fix prizes data on production

require_once 'config.php';

echo "<h2>Prizes Data Check & Fix</h2>";

try {
    $pdo = getDatabaseConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Check if prizes table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'prizes'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ prizes table does not exist!</p>";
        echo "<p>Please import the database schema first using database_no_drop.sql</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ prizes table exists!</p>";
    
    // Check prizes data
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM prizes");
    $totalPrizes = $stmt->fetch()['total'];
    
    echo "<p><strong>Total prizes in database:</strong> $totalPrizes</p>";
    
    if ($totalPrizes == 0) {
        echo "<p style='color: red;'>❌ No prizes found! Adding default prizes...</p>";
        
        // Insert default prizes
        $prizes = [
            ['name' => 'Tai nghe bluetooth', 'display_order' => 1, 'stock' => 50, 'is_active' => 1],
            ['name' => 'Bình thủy tinh', 'display_order' => 2, 'stock' => 30, 'is_active' => 1],
            ['name' => 'Tag hành lý', 'display_order' => 3, 'stock' => 40, 'is_active' => 1],
            ['name' => 'Móc khóa', 'display_order' => 4, 'stock' => 60, 'is_active' => 1],
            ['name' => 'Túi tote', 'display_order' => 5, 'stock' => 25, 'is_active' => 1],
            ['name' => 'Bịt mắt ngủ', 'display_order' => 6, 'stock' => 35, 'is_active' => 1],
            ['name' => 'Ô gấp', 'display_order' => 7, 'stock' => 20, 'is_active' => 1],
            ['name' => 'Mũ bảo hiểm', 'display_order' => 8, 'stock' => 15, 'is_active' => 1]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO prizes (name, display_order, stock, is_active) VALUES (?, ?, ?, ?)");
        $inserted = 0;
        
        foreach ($prizes as $prize) {
            try {
                $stmt->execute([$prize['name'], $prize['display_order'], $prize['stock'], $prize['is_active']]);
                $inserted++;
            } catch(PDOException $e) {
                echo "<p style='color: orange;'>⚠️ Failed to insert {$prize['name']}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p style='color: green;'>✅ Inserted $inserted prizes!</p>";
    } else {
        echo "<p style='color: green;'>✅ Prizes data exists!</p>";
    }
    
    // Check available prizes (stock > 0 and is_active = 1)
    $stmt = $pdo->query("SELECT COUNT(*) as available FROM prizes WHERE stock > 0 AND is_active = 1");
    $availablePrizes = $stmt->fetch()['available'];
    
    echo "<p><strong>Available prizes (stock > 0 and active):</strong> $availablePrizes</p>";
    
    if ($availablePrizes == 0) {
        echo "<p style='color: red;'>❌ No available prizes! This will cause the popup to show.</p>";
        echo "<p>Updating all prizes to have stock and be active...</p>";
        
        $stmt = $pdo->prepare("UPDATE prizes SET stock = 50, is_active = 1 WHERE stock = 0 OR is_active = 0");
        $result = $stmt->execute();
        
        if ($result) {
            echo "<p style='color: green;'>✅ Updated all prizes to be available!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update prizes!</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Prizes are available! Game should work normally.</p>";
    }
    
    // Show all prizes
    echo "<h3>All Prizes:</h3>";
    $stmt = $pdo->query("SELECT id, name, stock, is_active FROM prizes ORDER BY display_order");
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($prizes)) {
        echo "<p>No prizes found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Stock</th><th>Active</th><th>Status</th></tr>";
        foreach ($prizes as $prize) {
            $status = ($prize['stock'] > 0 && $prize['is_active']) ? '✅ Available' : '❌ Not Available';
            $activeText = $prize['is_active'] ? 'Yes' : 'No';
            echo "<tr>";
            echo "<td>" . $prize['id'] . "</td>";
            echo "<td>" . $prize['name'] . "</td>";
            echo "<td>" . $prize['stock'] . "</td>";
            echo "<td>" . $activeText . "</td>";
            echo "<td>" . $status . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check wheel_segments table
    echo "<h3>Wheel Segments Check:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM wheel_segments");
    $totalSegments = $stmt->fetch()['total'];
    
    echo "<p><strong>Total wheel segments:</strong> $totalSegments</p>";
    
    if ($totalSegments == 0) {
        echo "<p style='color: red;'>❌ No wheel segments found! Adding default segments...</p>";
        
        $segments = [
            ['segment_index' => 0, 'product_id' => 1], // Tai nghe bluetooth
            ['segment_index' => 1, 'product_id' => 2], // Bình thủy tinh
            ['segment_index' => 2, 'product_id' => 3], // Tag hành lý
            ['segment_index' => 3, 'product_id' => 4], // Móc khóa
            ['segment_index' => 4, 'product_id' => 5], // Túi tote
            ['segment_index' => 5, 'product_id' => 2], // Bình thủy tinh (duplicate)
            ['segment_index' => 6, 'product_id' => 4], // Móc khóa (duplicate)
            ['segment_index' => 7, 'product_id' => 6], // Bịt mắt ngủ
            ['segment_index' => 8, 'product_id' => 3], // Tag hành lý (duplicate)
            ['segment_index' => 9, 'product_id' => 5], // Túi tote (duplicate)
            ['segment_index' => 10, 'product_id' => 7], // Ô gấp
            ['segment_index' => 11, 'product_id' => 8]  // Mũ bảo hiểm
        ];
        
        $stmt = $pdo->prepare("INSERT INTO wheel_segments (segment_index, product_id) VALUES (?, ?)");
        $inserted = 0;
        
        foreach ($segments as $segment) {
            try {
                $stmt->execute([$segment['segment_index'], $segment['product_id']]);
                $inserted++;
            } catch(PDOException $e) {
                echo "<p style='color: orange;'>⚠️ Failed to insert segment {$segment['segment_index']}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p style='color: green;'>✅ Inserted $inserted wheel segments!</p>";
    } else {
        echo "<p style='color: green;'>✅ Wheel segments exist!</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>If prizes were added/updated, the game should work normally now</li>";
echo "<li>If still showing popup, check:</li>";
echo "<ul>";
echo "<li>All prizes have stock > 0</li>";
echo "<li>All prizes have is_active = 1</li>";
echo "<li>Wheel segments are properly mapped</li>";
echo "</ul>";
echo "<li>Delete this file after fixing</li>";
echo "</ol>";
?>
