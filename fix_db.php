<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    echo "<h3>🔧 Database Fix Script</h3>";

    // 1. Fix Database and Tables Charset to UTF8MB4
    $pdo->exec("ALTER DATABASE xivex_store CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci");
    echo "✅ Database charset updated.<br>";

    $tables = ['users', 'products', 'product_variants', 'orders', 'order_items', 'settings', 'chat_messages', 'notifications', 'user_notifications'];
    foreach ($tables as $table) {
        $pdo->exec("ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    echo "✅ All tables charset updated to utf8mb4.<br>";

    // 2. Fix Default Values (Re-inserting product names with correct Thai encoding)
    $updates = [
        1 => 'ฮู้ดดี้โอเวอร์ไซส์ สีเบจ (Oversized Beige Hoodie)',
        2 => 'กางเกงคาร์โก้ แทคติคอล (Tactical Cargo Pants)',
        3 => 'เสื้อแจ็คเก็ตกันลม (Monochrome Windbreaker)',
        4 => 'เสื้อยืด Heavy Tee (Signature Heavy Tee)',
        5 => 'เสื้อกั๊กยีนส์ (Raw Edge Denim Vest)',
        6 => 'กระเป๋าสะพายข้าง (Utility Crossbody Bag)'
    ];

    $stmt = $pdo->prepare("UPDATE products SET name = ? WHERE id = ? AND name LIKE '%%'"); // Only update if corrupted
    foreach ($updates as $id => $name) {
        $stmt->execute([$name, $id]);
    }
    
    // Force update all to be safe since they are mojibake, just doing it cleanly:
    $stmtForce = $pdo->prepare("UPDATE products SET name = ? WHERE id = ?");
    foreach ($updates as $id => $name) {
        $stmtForce->execute([$name, $id]);
    }
    echo "✅ Products text repaired.<br>";


    echo "<br><b>All fixes applied successfully!</b><br>";
    echo "<a href='admin/products.php'>Go back to Products</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
