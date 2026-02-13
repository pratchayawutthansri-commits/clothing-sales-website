<?php
require_once '../includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

echo "<h3>Updating Database for Payment Verification...</h3>";

try {
    // Check if 'payment_slip' column exists in 'orders' table
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_slip'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_slip VARCHAR(255) DEFAULT NULL AFTER payment_method");
        echo "✅ Added 'payment_slip' column to 'orders' table.<br>";
    } else {
        echo "ℹ️ 'payment_slip' column already exists.<br>";
    }
    
    // Create uploads directory if not exists
    $uploadDir = '../uploads/slips';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "✅ Created uploads/slips directory.<br>";
    }

    echo "<h3>Database Update Complete! <a href='index.php'>Go to Dashboard</a></h3>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
