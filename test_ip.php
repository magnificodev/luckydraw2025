<?php
// Test IP Address Detection
echo "<h2>IP Address Detection Test</h2>";

// Function to get real IP address
function getRealIPAddress() {
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_CLIENT_IP',            // Proxy
        'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
        'HTTP_X_FORWARDED',          // Proxy
        'HTTP_X_CLUSTER_CLIENT_IP', // Cluster
        'HTTP_FORWARDED_FOR',        // Proxy
        'HTTP_FORWARDED',            // Proxy
        'REMOTE_ADDR'               // Standard
    ];
    
    echo "<h3>Available IP Headers:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Header</th><th>Value</th><th>Status</th></tr>";
    
    foreach ($ipKeys as $key) {
        $value = $_SERVER[$key] ?? 'Not set';
        $status = !empty($_SERVER[$key]) ? 'Available' : 'Not available';
        echo "<tr><td>$key</td><td>$value</td><td>$status</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>IP Selection Process:</h3>";
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs (take first one)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            // Validate IP (allow private ranges for testing)
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                echo "<p><strong>Selected IP from $key:</strong> $ip</p>";
                return $ip;
            }
        }
    }
    
    // Fallback to REMOTE_ADDR even if it's localhost
    $fallbackIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    echo "<p><strong>Fallback IP:</strong> $fallbackIP</p>";
    return $fallbackIP;
}

$detectedIP = getRealIPAddress();
echo "<h3>Final Result:</h3>";
echo "<p><strong>Detected IP Address:</strong> <span style='color: blue; font-size: 18px;'>$detectedIP</span></p>";

// Additional info
echo "<h3>Additional Information:</h3>";
echo "<p><strong>Server IP:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "</p>";
echo "<p><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "</p>";
echo "<p><strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "</p>";
echo "<p><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</p>";

// Test with different scenarios
echo "<h3>Test Scenarios:</h3>";
echo "<p>1. <strong>Localhost:</strong> Should show 127.0.0.1 or ::1</p>";
echo "<p>2. <strong>Local Network:</strong> Should show 192.168.x.x or 10.x.x.x</p>";
echo "<p>3. <strong>Public IP:</strong> Should show real public IP</p>";
echo "<p>4. <strong>Behind Proxy:</strong> Should show client IP from X-Forwarded-For</p>";

?>
