<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php'; // Use main DB connection

// Fetch Products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Xivex</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">

</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <?php if (isset($_GET['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            ทำรายการสำเร็จ
        </div>
    <?php endif; ?>

    <div class="header">
        <h1>รายการสินค้าทั้งหมด</h1>
        <a href="add_product.php" class="btn">+ เพิ่มสินค้าใหม่</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>รูปภาพ</th>
                <th>ชื่อสินค้า</th>
                <th>หมวดหมู่</th>
                <th>ราคาเริ่มต้น</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><img src="../<?= htmlspecialchars($product['image']) ?>" class="thumb"></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td>฿<?= number_format($product['base_price'], 0) ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $product['id'] ?>" style="color:#ffc107; margin-right:10px;">แก้ไข</a>
                    <form action="delete_product.php" method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันที่จะลบสินค้านี้? ข้อมูลทั้งหมดรวมถึงสต็อกจะหายไป')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <button type="submit" style="color:#dc3545; background:none; border:none; cursor:pointer; font-family:inherit; font-size:inherit;">ลบ</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
