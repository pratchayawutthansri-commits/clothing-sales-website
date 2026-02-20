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
    <style>
        body { font-family: 'Kanit', sans-serif; margin: 0; background: #f9f9f9; display: flex; }
        .sidebar { width: 250px; background: #1a1a1a; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { margin-top: 0; margin-bottom: 30px; letter-spacing: 1px;}
        .sidebar a { display: block; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #333; transition: 0.3s; }
        .sidebar a:hover { color: white; background: #333; padding-left: 20px; }
        .sidebar a.active { color: white; font-weight: bold; background: #333; border-left: 4px solid #fff; }
        .content { flex: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn { padding: 10px 20px; background: #000; color: white; text-decoration: none; border-radius: 30px; display: inline-block; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.2); }
        .btn-logout { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f4f4f4; }
        .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
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
