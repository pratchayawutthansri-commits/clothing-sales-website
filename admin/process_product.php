<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        die("Security Error: Invalid CSRF Token");
    }

    try {
        // 1. Handle File Upload
        $target_dir = "../images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image = $_FILES['image'];
        $imageExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $newFileName = "prod_" . uniqid() . "." . $imageExtension;
        $target_file = $target_dir . $newFileName;
        $db_image_path = "images/" . $newFileName;

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageExtension, $allowed)) {
            die("Error: Invalid file type. Only JPG, PNG, GIF, WEBP allowed.");
        }

        if (!move_uploaded_file($image['tmp_name'], $target_file)) {
            die("Error: Failed to upload image.");
        }

        // 2. Insert Product
        $pdo->beginTransaction();

        $base_price = $_POST['base_price'];
        $badge = $_POST['badge'] ?? null;
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;

        // Validate Inputs
        if (empty($_POST['name']) || empty($base_price)) {
            die("กรุณากรอกข้อมูลที่จำเป็นให้ครบ");
        }

        $sql = "INSERT INTO products (name, category, description, base_price, image, badge, is_visible) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['description'],
            $base_price,
            $db_image_path,
            $badge,
            $is_visible
        ]);
        $product_id = $pdo->lastInsertId();

        // 3. Insert Variants
        $base_price = $_POST['base_price'];
        $badge = $_POST['badge'] ?? null;
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;

        // Validate Inputs
        if (empty($_POST['name']) || empty($base_price)) {
            die("กรุณากรอกข้อมูลที่จำเป็นให้ครบ");
        }
        $sizes = $_POST['sizes'];
        $prices = $_POST['prices'];
        $stocks = $_POST['stocks'];

        $stmtV = $pdo->prepare("INSERT INTO product_variants (product_id, size, price, stock) VALUES (?, ?, ?, ?)");

        for ($i = 0; $i < count($sizes); $i++) {
            if (!empty($sizes[$i])) {
                $stmtV->execute([
                    $product_id,
                    strtoupper($sizes[$i]),
                    $prices[$i],
                    $stocks[$i]
                ]);
            }
        }

        $pdo->commit();
        header("Location: index.php?success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>
