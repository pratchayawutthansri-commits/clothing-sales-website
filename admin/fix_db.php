<?php
require_once '../includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

echo "<h3>Repairing Database...</h3>";

try {
    // Check if 'status' column exists in 'orders' table
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'pending' AFTER total_price");
        echo "✅ Added 'status' column to 'orders' table.<br>";
    } else {
        echo "ℹ️ 'status' column already exists.<br>";
    }

    // Check if 'payment_method' column exists (just in case)
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'COD' AFTER total_price");
        echo "✅ Added 'payment_method' column to 'orders' table.<br>";
    } else {
        echo "ℹ️ 'payment_method' column already exists.<br>";
    }
    
    echo "<h3>Database Repair Complete! <a href='index.php'>Go to Dashboard</a></h3>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
