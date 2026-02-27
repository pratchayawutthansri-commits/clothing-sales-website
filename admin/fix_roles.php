<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

// Fix users with empty or NULL role
$stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE role IS NULL OR role = '' OR role NOT IN ('user', 'admin')");
$count = $stmt->execute();
$affected = $stmt->rowCount();

echo "<h2>Fixed! Updated {$affected} users to role='user'</h2>";

// Show all users now
$users = $pdo->query("SELECT id, username, name, email, role FROM users ORDER BY id")->fetchAll();
echo "<table border='1' cellpadding='8'><tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th></tr>";
foreach ($users as $u) {
    echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['name']}</td><td>{$u['email']}</td><td><b>{$u['role']}</b></td></tr>";
}
echo "</table>";
echo "<br><a href='users.php'>← กลับไปหน้าจัดการสมาชิก</a>";
?>
