<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // 1. Check if product is in any order
        // Note: We check order_items. Even if order is cancelled, we might want to keep history.
        // If you want to allow delete, you must delete order_items first? 
        // Best practice: Do NOT allow delete if order exists.
        
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
        $stmtCheck->execute([$id]);
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            // Cannot delete
            header("Location: index.php?error=ไม่สามารถลบสินค้าได้ เนื่องจากมีประวัติการสั่งซื้อ (มีอยู่ใน $count รายการ)");
            exit;
        }

        // 2. Delete Image (Optional - Get path first)
        $stmtImg = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();

        // 3. Delete Product (Variants will be deleted via ON DELETE CASCADE in DB? 
        // Let's check DB schema in database.sql... 
        // FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
        // Yes, variants delete automatically.
        
        $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmtDel->execute([$id]);

        // Delete valid image file
        if ($img && file_exists("../" . $img)) {
            unlink("../" . $img);
        }

        header("Location: index.php?success=1");
        exit;

    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode("Error: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
