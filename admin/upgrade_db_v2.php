<?php
require_once '../includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

echo "<h3>Updating Database for Product Enhancements...</h3>";

try {
    // Add is_visible
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_visible'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_visible TINYINT(1) DEFAULT 1 AFTER image");
        echo "✅ Added 'is_visible' column.<br>";
    } else {
        echo "ℹ️ 'is_visible' column already exists.<br>";
    }

    // Add badge
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'badge'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN badge VARCHAR(50) DEFAULT NULL AFTER is_visible");
        echo "✅ Added 'badge' column.<br>";
    } else {
        echo "ℹ️ 'badge' column already exists.<br>";
    }
    
    // Add tracking_number (Phase 17 prep)
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'tracking_number'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100) DEFAULT NULL AFTER status");
        echo "✅ Added 'tracking_number' column.<br>";
    } else {
        echo "ℹ️ 'tracking_number' column already exists.<br>";
    }

    echo "<h3>Database Upgrade Complete! <a href='index.php'>Back to Dashboard</a></h3>";

} catch (PDOException $e) {
    error_log("DB Upgrade Error: " . $e->getMessage());
    die("Database upgrade encountered an error. Please check server logs.");
}
?>
