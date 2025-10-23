<?php
// JavaScript & CSS Debug Script
// Use this to debug JavaScript and CSS loading issues

echo "<h2>JavaScript & CSS Debug</h2>";

// Check if main files exist and are readable
$mainFiles = [
    'index.php',
    'assets/css/style.css',
    'assets/js/main.js'
];

echo "<h3>File Accessibility Check:</h3>";
foreach ($mainFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        $readable = is_readable($file) ? 'Yes' : 'No';
        echo "<p style='color: green;'>✅ $file exists ($size bytes, Readable: $readable)</p>";
    } else {
        echo "<p style='color: red;'>❌ $file not found!</p>";
    }
}

// Check CSS content for key styles
echo "<h3>CSS Content Check:</h3>";
$cssFile = 'assets/css/style.css';
if (file_exists($cssFile)) {
    $cssContent = file_get_contents($cssFile);
    
    $keyStyles = [
        '.alert-box' => 'Alert box styles',
        '.alert-overlay' => 'Alert overlay styles',
        '.alert-btn' => 'Alert button styles',
        '.error-message' => 'Error message styles',
        '.wheel' => 'Wheel styles',
        '.start-button' => 'Start button styles'
    ];
    
    foreach ($keyStyles as $selector => $description) {
        if (strpos($cssContent, $selector) !== false) {
            echo "<p style='color: green;'>✅ $description found</p>";
        } else {
            echo "<p style='color: red;'>❌ $description missing</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ CSS file not found!</p>";
}

// Check JavaScript content
echo "<h3>JavaScript Content Check:</h3>";
$jsFile = 'assets/js/main.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    
    $keyFunctions = [
        'showAlertPopup' => 'Alert popup function',
        'closeAlert' => 'Close alert function',
        'animateWheelSpin' => 'Wheel animation function',
        'handleSpinSubmit' => 'Spin submit handler',
        'validatePhoneNumber' => 'Phone validation function'
    ];
    
    foreach ($keyFunctions as $function => $description) {
        if (strpos($jsContent, $function) !== false) {
            echo "<p style='color: green;'>✅ $description found</p>";
        } else {
            echo "<p style='color: red;'>❌ $description missing</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ JavaScript file not found!</p>";
}

// Create a test page to check if everything loads
echo "<h3>Test Page Creation:</h3>";

$testPage = '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Test Page</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="debug-info" style="position: fixed; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; z-index: 9999;">
        <h4>Debug Info:</h4>
        <p id="css-loaded">CSS: Loading...</p>
        <p id="js-loaded">JS: Loading...</p>
        <p id="jquery-loaded">jQuery: Loading...</p>
    </div>

    <!-- Test Alert Box -->
    <div id="test-alert" class="alert-overlay" style="display: block;">
        <div class="alert-box">
            <div class="alert-icon">
                <i class="fas fa-check"></i>
            </div>
            <div class="alert-content">
                <h3>Test Alert</h3>
                <p>If you can see this styled properly, CSS is working!</p>
                <button class="alert-btn" onclick="closeTestAlert()">OK</button>
            </div>
        </div>
    </div>

    <script>
        // Check if CSS is loaded
        function checkCSS() {
            const testEl = document.createElement("div");
            testEl.className = "alert-box";
            document.body.appendChild(testEl);
            const styles = window.getComputedStyle(testEl);
            const hasStyles = styles.borderRadius !== "" || styles.background !== "";
            document.body.removeChild(testEl);
            return hasStyles;
        }

        // Check if JS is loaded
        function checkJS() {
            return typeof showAlertPopup === "function";
        }

        // Check if jQuery is loaded
        function checkjQuery() {
            return typeof $ !== "undefined";
        }

        // Update debug info
        function updateDebugInfo() {
            document.getElementById("css-loaded").textContent = "CSS: " + (checkCSS() ? "✅ Loaded" : "❌ Not Loaded");
            document.getElementById("js-loaded").textContent = "JS: " + (checkJS() ? "✅ Loaded" : "❌ Not Loaded");
            document.getElementById("jquery-loaded").textContent = "jQuery: " + (checkjQuery() ? "✅ Loaded" : "❌ Not Loaded");
        }

        // Close test alert
        function closeTestAlert() {
            document.getElementById("test-alert").style.display = "none";
        }

        // Run checks when page loads
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(updateDebugInfo, 1000);
        });

        // Also check after a delay
        setTimeout(updateDebugInfo, 2000);
    </script>

    <script src="assets/js/main.js"></script>
</body>
</html>';

$testFile = 'debug_test.html';
file_put_contents($testFile, $testPage);
echo "<p style='color: green;'>✅ Test page created: <a href='$testFile' target='_blank'>Open Debug Test Page</a></p>";

// Check for common issues
echo "<h3>Common Issues Check:</h3>";

// Check if there are any PHP errors in the main files
echo "<h4>PHP Syntax Check:</h4>";
$phpFiles = ['index.php', 'process.php'];
foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color: green;'>✅ $file syntax OK</p>";
        } else {
            echo "<p style='color: red;'>❌ $file syntax error: $output</p>";
        }
    }
}

// Check server configuration
echo "<h4>Server Configuration:</h4>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>";

// Check if mod_rewrite is enabled (for clean URLs)
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $rewriteEnabled = in_array('mod_rewrite', $modules);
    echo "<p><strong>mod_rewrite:</strong> " . ($rewriteEnabled ? '✅ Enabled' : '❌ Disabled') . "</p>";
}

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>Open the <strong>Debug Test Page</strong> to see if CSS and JS load properly</li>";
echo "<li>Check the debug info in the top-right corner</li>";
echo "<li>If CSS shows 'Not Loaded', check server MIME types</li>";
echo "<li>If JS shows 'Not Loaded', check JavaScript errors in browser console</li>";
echo "<li>If everything shows 'Loaded' but still not working, check browser cache</li>";
echo "</ol>";

echo "<h3>Troubleshooting Steps:</h3>";
echo "<ul>";
echo "<li><strong>Clear browser cache:</strong> Ctrl+F5 or Cmd+Shift+R</li>";
echo "<li><strong>Check browser console:</strong> F12 → Console tab</li>";
echo "<li><strong>Check network tab:</strong> F12 → Network tab → Reload page</li>";
echo "<li><strong>Try different browser:</strong> Test in incognito/private mode</li>";
echo "<li><strong>Check server logs:</strong> Look for PHP or server errors</li>";
echo "</ul>";
?>
