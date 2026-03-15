<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    echo "<h3>🔧 Database Fix Script: Allow Product Deletion</h3>";

    // 1. Drop existing constraint
    $pdo->exec("ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2");
    
    // 2. Modify column to allow NULL
    $pdo->exec("ALTER TABLE order_items MODIFY product_id INT NULL");
    
    // 3. Add new constraint with ON DELETE SET NULL
    $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_2 FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL");
    
    echo "✅ Database constraints updated successfully!<br>";
    echo "You can now delete products without affecting past orders.<br>";
    echo "<a href='admin/products.php'>Go back to Products</a>";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Can't DROP FOREIGN KEY") !== false) {
         echo "✅ Note: The foreign key might have already been modified, or has a different name. Please verify in phpMyAdmin.<br>";
    } else {
         echo "❌ Error: " . $e->getMessage();
    }
}
?>
