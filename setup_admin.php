<?php
// Admin User Setup Script
// Use this to check and create admin user on production

require_once 'config.php';

echo "<h2>Admin User Setup & Check</h2>";

try {
    $pdo = getDatabaseConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";

    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ admin_users table does not exist!</p>";
        echo "<p>Please import the database schema first using database_no_drop.sql</p>";
        exit;
    }

    echo "<p style='color: green;'>✅ admin_users table exists!</p>";

    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "<p style='color: orange;'>⚠️ Admin user does not exist. Creating...</p>";

        // Create admin user
        $password = 'Admin2025!@#';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        $result = $stmt->execute(['admin', $passwordHash]);

        if ($result) {
            echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> Admin2025!@#</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin user!</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Admin user exists!</p>";
        echo "<p><strong>User ID:</strong> " . $admin['id'] . "</p>";
        echo "<p><strong>Username:</strong> " . $admin['username'] . "</p>";
        echo "<p><strong>Password Hash:</strong> " . substr($admin['password_hash'], 0, 20) . "...</p>";

        // Test password
        $testPassword = 'Admin2025!@#';
        if (password_verify($testPassword, $admin['password_hash'])) {
            echo "<p style='color: green;'>✅ Password verification successful!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification failed!</p>";
            echo "<p>Updating password...</p>";

            $newPasswordHash = password_hash($testPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
            $result = $stmt->execute([$newPasswordHash]);

            if ($result) {
                echo "<p style='color: green;'>✅ Password updated successfully!</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update password!</p>";
            }
        }
    }

    // Show all admin users
    echo "<h3>All Admin Users:</h3>";
    $stmt = $pdo->query("SELECT id, username, created_at FROM admin_users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        echo "<p>No admin users found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Created At</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>If admin user was created/updated, try logging in with:</li>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> Admin2025!@#</li>";
echo "</ul>";
echo "<li>If still having issues, check:</li>";
echo "<ul>";
echo "<li>Database schema is complete</li>";
echo "<li>All tables are created</li>";
echo "<li>Database connection is working</li>";
echo "</ul>";
echo "<li>Delete this file after fixing the issue</li>";
echo "</ol>";
?>
