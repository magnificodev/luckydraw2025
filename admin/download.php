<?php
require_once 'includes/auth.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Get filename from query parameter
$filename = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($filename)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Filename is required';
    exit;
}

// Sanitize filename to prevent directory traversal
$filename = basename($filename);

// Define the export directory
$exportDir = '../exports/';

// Check if file exists
$filePath = $exportDir . $filename;

if (!file_exists($filePath)) {
    header('HTTP/1.1 404 Not Found');
    echo 'File not found';
    exit;
}

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Output the file
readfile($filePath);
exit;
?>
