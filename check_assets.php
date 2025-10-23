<?php
// Assets Check Script
// Use this to check if all assets are properly uploaded and accessible

echo "<h2>Assets Check & Fix</h2>";

// Check if we're in the right directory
$currentDir = getcwd();
echo "<p><strong>Current directory:</strong> $currentDir</p>";

// Check main CSS file
$cssFile = 'assets/css/style.css';
echo "<h3>CSS Files Check:</h3>";

if (file_exists($cssFile)) {
    $cssSize = filesize($cssFile);
    echo "<p style='color: green;'>✅ $cssFile exists ($cssSize bytes)</p>";
    
    // Check if CSS is accessible via HTTP
    $cssUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $cssFile;
    echo "<p><strong>CSS URL:</strong> <a href='$cssUrl' target='_blank'>$cssUrl</a></p>";
    
    // Test if CSS loads
    $cssContent = file_get_contents($cssFile);
    if (strpos($cssContent, '.alert-box') !== false) {
        echo "<p style='color: green;'>✅ CSS contains alert-box styles</p>";
    } else {
        echo "<p style='color: red;'>❌ CSS missing alert-box styles</p>";
    }
} else {
    echo "<p style='color: red;'>❌ $cssFile not found!</p>";
}

// Check admin CSS
$adminCssFile = 'assets/css/admin.css';
if (file_exists($adminCssFile)) {
    $adminCssSize = filesize($adminCssFile);
    echo "<p style='color: green;'>✅ $adminCssFile exists ($adminCssSize bytes)</p>";
} else {
    echo "<p style='color: red;'>❌ $adminCssFile not found!</p>";
}

// Check login CSS
$loginCssFile = 'assets/css/login.css';
if (file_exists($loginCssFile)) {
    $loginCssSize = filesize($loginCssFile);
    echo "<p style='color: green;'>✅ $loginCssFile exists ($loginCssSize bytes)</p>";
} else {
    echo "<p style='color: red;'>❌ $loginCssFile not found!</p>";
}

// Check JavaScript files
echo "<h3>JavaScript Files Check:</h3>";
$jsFile = 'assets/js/main.js';
if (file_exists($jsFile)) {
    $jsSize = filesize($jsFile);
    echo "<p style='color: green;'>✅ $jsFile exists ($jsSize bytes)</p>";
} else {
    echo "<p style='color: red;'>❌ $jsFile not found!</p>";
}

$adminJsFile = 'assets/js/admin.js';
if (file_exists($adminJsFile)) {
    $adminJsSize = filesize($adminJsFile);
    echo "<p style='color: green;'>✅ $adminJsFile exists ($adminJsSize bytes)</p>";
} else {
    echo "<p style='color: red;'>❌ $adminJsFile not found!</p>";
}

// Check images
echo "<h3>Images Check:</h3>";
$imageFiles = [
    'assets/images/wheel.png',
    'assets/images/wheel-pointer.png',
    'assets/images/start-button.png',
    'assets/images/spin-button.png',
    'assets/images/background.png'
];

foreach ($imageFiles as $imageFile) {
    if (file_exists($imageFile)) {
        $imageSize = filesize($imageFile);
        echo "<p style='color: green;'>✅ $imageFile exists ($imageSize bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ $imageFile not found!</p>";
    }
}

// Check fonts
echo "<h3>Fonts Check:</h3>";
$fontFiles = [
    'assets/fonts/SVN-GILROY BOLD.OTF',
    'assets/fonts/SVN-GILROY REGULAR.OTF',
    'assets/fonts/SVN-GILROY SEMIBOLD.OTF'
];

foreach ($fontFiles as $fontFile) {
    if (file_exists($fontFile)) {
        $fontSize = filesize($fontFile);
        echo "<p style='color: green;'>✅ $fontFile exists ($fontSize bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ $fontFile not found!</p>";
    }
}

// Check directory structure
echo "<h3>Directory Structure Check:</h3>";
$directories = [
    'assets',
    'assets/css',
    'assets/js',
    'assets/images',
    'assets/fonts',
    'assets/images/gifts'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✅ $dir/ directory exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $dir/ directory not found!</p>";
    }
}

// Test CSS loading
echo "<h3>CSS Loading Test:</h3>";
echo "<p>Testing if CSS loads properly...</p>";

// Create a test HTML to check CSS loading
$testHtml = '<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>CSS Test</title>
</head>
<body>
    <div class="alert-box" style="margin: 20px; padding: 20px; border: 2px solid #00d4ff;">
        <div class="alert-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="alert-content">
            <h3>CSS Test</h3>
            <p>If you can see this styled properly, CSS is working!</p>
        </div>
    </div>
</body>
</html>';

$testFile = 'css_test.html';
file_put_contents($testFile, $testHtml);
echo "<p><a href='$testFile' target='_blank'>Open CSS Test Page</a></p>";

// Check file permissions
echo "<h3>File Permissions Check:</h3>";
$filesToCheck = [
    'assets/css/style.css',
    'assets/css/admin.css',
    'assets/js/main.js',
    'assets/images/wheel.png'
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? 'Yes' : 'No';
        echo "<p><strong>$file:</strong> Permissions: " . decoct($perms & 0777) . ", Readable: $readable</p>";
    }
}

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>Check which files are missing (marked with ❌)</li>";
echo "<li>Upload missing files to the correct directories</li>";
echo "<li>Check file permissions (should be readable)</li>";
echo "<li>Test the CSS test page to see if styling works</li>";
echo "<li>Delete this file after fixing</li>";
echo "</ol>";

echo "<h3>Common Solutions:</h3>";
echo "<ul>";
echo "<li><strong>Missing files:</strong> Upload all assets from local to production</li>";
echo "<li><strong>Wrong paths:</strong> Check if assets/ directory structure is correct</li>";
echo "<li><strong>Permissions:</strong> Set files to be readable (644 or 755)</li>";
echo "<li><strong>Server config:</strong> Ensure server serves CSS files with correct MIME type</li>";
echo "</ul>";
?>
