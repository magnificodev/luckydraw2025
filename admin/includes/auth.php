<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/admin-config.php';

// Check if user is logged in
function isAdminLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) &&
           $_SESSION[ADMIN_SESSION_KEY] === true &&
           isset($_SESSION['admin_user_id']) &&
           isset($_SESSION['admin_username']);
}

// Redirect to login if not authenticated
function requireAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// Login function
function adminLogin($username, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION[ADMIN_SESSION_KEY] = true;
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];

        return true;
    }

    return false;
}

// Logout function
function adminLogout() {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Get current admin user info
function getCurrentAdmin() {
    if (isAdminLoggedIn()) {
        return [
            'id' => $_SESSION['admin_user_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null
        ];
    }
    return null;
}
?>
