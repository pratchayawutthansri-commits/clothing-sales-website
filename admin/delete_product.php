<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

// Only accept POST requests with CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit;
}

// CSRF Check
if (!isset($_POST['csrf_token']) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
    die("Security Error: Invalid CSRF Token");
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    try {
        // 1. Check if product is in any order
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
        $stmtCheck->execute([$id]);
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            header("Location: products.php?error=" . urlencode("Cannot delete product: It is associated with $count existing order(s)"));
            exit;
        }

        // 2. Delete Image
        $stmtImg = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();

        // 3. Delete Product (Variants cascade via FK)
        $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmtDel->execute([$id]);

        // Delete image file
        if ($img && file_exists("../" . $img)) {
            unlink("../" . $img);
        }

        header("Location: products.php?success=" . urlencode("Product deleted successfully"));
        exit;

    } catch (Exception $e) {
        header("Location: products.php?error=" . urlencode("Error: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: products.php");
    exit;
}
?>
