<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h3>Adding missing columns to 'orders' table...</h3>";

try {
    // Add customer_name
    $pdo->exec("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255) NOT NULL AFTER user_id");
    echo "✅ Added 'customer_name'<br>";

    // Add email
    $pdo->exec("ALTER TABLE orders ADD COLUMN email VARCHAR(255) NOT NULL AFTER customer_name");
    echo "✅ Added 'email'<br>";

    // Add phone
    $pdo->exec("ALTER TABLE orders ADD COLUMN phone VARCHAR(50) NOT NULL AFTER email");
    echo "✅ Added 'phone'<br>";

    // Add address
    $pdo->exec("ALTER TABLE orders ADD COLUMN address TEXT NOT NULL AFTER phone");
    echo "✅ Added 'address'<br>";

    echo "<h3>Database Upgrade Complete! <a href='checkout.php'>Try Checkout Again</a></h3>";

} catch (PDOException $e) {
    echo "Error (might already exist): " . $e->getMessage();
}
?>
