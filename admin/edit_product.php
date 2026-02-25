<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: products.php");
    exit;
}

// Fetch Product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("ไม่พบสินค้า");
}

// Fetch Variants
$stmtV = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$stmtV->execute([$id]);
$variants = $stmtV->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Page-specific: Edit Product */
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: 'Kanit';
        }
        .variant-group { background: #f4f4f4; padding: 15px; border-radius: 4px; margin-bottom: 10px; display: flex; gap: 10px; align-items: center; }
        .current-img { margin-top: 10px; max-width: 150px; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <h1>แก้ไขสินค้า: <?= htmlspecialchars($product['name']) ?></h1>
    
    <div class="form-container">
        <form action="update_product_logic.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            
            <div class="form-group">
                <label>ชื่อสินค้า</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div style="display:flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex:1;">
                    <label>ป้ายกำกับ (Badge)</label>
                    <input type="text" name="badge" value="<?= htmlspecialchars($product['badge'] ?? '') ?>" placeholder="เช่น New Arrival, Sale">
                </div>
                <div style="flex:0 0 150px; display:flex; align-items:flex-end;">
                     <label style="cursor:pointer; display:flex; align-items:center;">
                        <input type="checkbox" name="is_visible" value="1" <?= $product['is_visible'] ? 'checked' : '' ?> style="width:20px; height:20px; margin-right:10px;">
                        <span>แสดงสินค้า</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>หมวดหมู่</label>
                <select name="category">
                    <option value="Tops" <?= $product['category'] == 'Tops' ? 'selected' : '' ?>>เสื้อ (Tops)</option>
                    <option value="Bottoms" <?= $product['category'] == 'Bottoms' ? 'selected' : '' ?>>กางเกง (Bottoms)</option>
                    <option value="Outerwear" <?= $product['category'] == 'Outerwear' ? 'selected' : '' ?>>เสื้อคลุม (Outerwear)</option>
                    <option value="Accessories" <?= $product['category'] == 'Accessories' ? 'selected' : '' ?>>เครื่องประดับ (Accessories)</option>
                </select>
            </div>

            <div class="form-group">
                <label>รายละเอียดสินค้า</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>ราคาเริ่มต้น (บาท)</label>
                <input type="number" name="base_price" value="<?= $product['base_price'] ?>" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>รูปภาพหลัก (อัปโหลดใหม่เพื่อเปลี่ยน)</label>
                <input type="file" name="image" accept="image/*">
                <?php if ($product['image']): ?>
                    <div style="margin-top:5px; font-size:0.8rem; color:#666;">รูปปัจจุบัน:</div>
                    <img src="../<?= htmlspecialchars($product['image']) ?>" class="current-img">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>ตัวเลือกสินค้า (Variants)</label>
                <div id="variants-container">
                    <?php foreach ($variants as $v): ?>
                    <div class="variant-group">
                        <input type="hidden" name="existing_variant_ids[]" value="<?= $v['id'] ?>">
                        <input type="text" name="existing_sizes[]" value="<?= htmlspecialchars($v['size']) ?>" required style="width: 30%;" placeholder="ไซส์">
                        <input type="number" name="existing_prices[]" value="<?= $v['price'] ?>" required style="width: 30%;" placeholder="ราคา">
                        <input type="number" name="existing_stocks[]" value="<?= $v['stock'] ?>" required style="width: 25%;" placeholder="สต็อก">
                        <span style="font-size: 0.8rem; color: #888;">(แก้ไข)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="new-variants-container"></div>
                
                <button type="button" class="btn btn-secondary" onclick="addVariant()" style="margin-top:10px; font-size: 0.9rem;">+ เพิ่มตัวเลือกใหม่</button>
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top: 20px;">บันทึกการแก้ไข</button>
        </form>
    </div>
</div>

<script>
function addVariant() {
    const container = document.getElementById('new-variants-container');
    const div = document.createElement('div');
    div.className = 'variant-group';
    div.innerHTML = `
        <input type="text" name="new_sizes[]" placeholder="ไซส์ (เช่น M)" required style="width: 30%;">
        <input type="number" name="new_prices[]" placeholder="ราคา" required style="width: 30%;">
        <input type="number" name="new_stocks[]" value="100" placeholder="สต็อก" style="width: 25%;">
        <button type="button" onclick="this.parentElement.remove()" style="background:red; color:white; border:none; border-radius:4px; cursor:pointer; padding: 5px 10px;">X</button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
