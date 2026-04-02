<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    // 1. First, we need to alter the product_id column to allow NULL
    $pdo->exec("ALTER TABLE order_items MODIFY COLUMN product_id INT DEFAULT NULL");

    // 2. Find the exact name of the foreign key constraint on the `product_id` column
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'order_items' 
        AND COLUMN_NAME = 'product_id' 
        AND REFERENCED_TABLE_NAME = 'products'
    ");
    $fkName = $stmt->fetchColumn();

    if ($fkName) {
        // Drop the old constraint
        $pdo->exec("ALTER TABLE order_items DROP FOREIGN KEY `$fkName`");
        echo "Old constraint `$fkName` dropped.<br>";
    }

    // 3. Add the new constraint with ON DELETE SET NULL
    // First, let's create a predictable name for the new FK
    $newFkName = "fk_order_items_product_id";
    $pdo->exec("ALTER TABLE order_items ADD CONSTRAINT `$newFkName` FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL");

    echo "Successfully updated the foreign key constraint to ON DELETE SET NULL. You can now safely delete products.";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key') !== false) {
         echo "The constraint already exists or has been migrated.";
    } else {
         echo "Error updating database: " . $e->getMessage();
    }
}
?>
