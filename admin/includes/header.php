<?php
require_once 'auth.php';
requireAuth();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$admin = getCurrentAdmin();

// Debug: Check if admin session is properly set
if (!$admin) {
    error_log("Admin session not found, redirecting to login");
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPBank Admin - Lucky Draw</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <i class="fas fa-desktop"></i>
                <h1>VPBank Admin</h1>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="logout-btn" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng xuất
                </button>
            </div>
        </header>

        <div class="admin-content">
            <!-- Sidebar -->
            <nav class="sidebar">
                <ul class="nav-menu">
                    <li class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo $currentPage === 'manage-stock' ? 'active' : ''; ?>">
                        <a href="manage-stock.php" class="nav-link">
                            <i class="fas fa-boxes"></i>
                            <span>Quản lý Stock</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo $currentPage === 'players' ? 'active' : ''; ?>">
                        <a href="players.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Người chơi</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo $currentPage === 'export-page' ? 'active' : ''; ?>">
                        <a href="export-page.php" class="nav-link">
                            <i class="fas fa-download"></i>
                            <span>Export CSV</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="confirmLogout(); return false;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="main-content">
                <div class="content-wrapper">
