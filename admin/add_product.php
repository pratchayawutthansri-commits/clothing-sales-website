<?php
require_once 'includes/config.php';
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Page-specific: Add Product */
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        .variant-group { background: #f4f4f4; padding: 15px; border-radius: 4px; margin-bottom: 10px; display: flex; gap: 10px; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <h1>เพิ่มสินค้าใหม่</h1>
    
    <div class="form-container">
        <form action="process_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
            
            <div class="form-group">
                <label>ชื่อสินค้า</label>
                <input type="text" name="name" required placeholder="เช่น เสื้อยืด Oversize สีดำ">
            </div>

            <div style="display:flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex:1;">
                    <label>ป้ายกำกับ (Badge)</label>
                    <input type="text" name="badge" placeholder="เช่น New Arrival, Sale, Best Seller">
                </div>
                <div style="flex:0 0 150px; display:flex; align-items:flex-end;">
                     <label style="cursor:pointer; display:flex; align-items:center;">
                        <input type="checkbox" name="is_visible" value="1" checked style="width:20px; height:20px; margin-right:10px;">
                        <span>แสดงสินค้า</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>หมวดหมู่</label>
                <select name="category">
                    <option value="Tops">เสื้อ (Tops)</option>
                    <option value="Bottoms">กางเกง (Bottoms)</option>
                    <option value="Outerwear">เสื้อคลุม (Outerwear)</option>
                    <option value="Accessories">เครื่องประดับ (Accessories)</option>
                </select>
            </div>

            <div class="form-group">
                <label>รายละเอียดสินค้า</label>
                <textarea name="description" rows="5" required placeholder="รายละเอียดสินค้า..."></textarea>
            </div>

            <div class="form-group">
                <label>ราคาเริ่มต้น (บาท)</label>
                <input type="number" name="base_price" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>รูปภาพหลัก</label>
                <input type="file" name="image" required accept="image/*">
            </div>

            <div class="form-group">
                <label>ตัวเลือกสินค้า (Variants)</label>
                <div id="variants-container">
                    <div class="variant-group">
                        <input type="text" name="sizes[]" placeholder="ไซส์ (เช่น S)" required style="width: 40%;">
                        <input type="number" name="prices[]" placeholder="ราคา" required style="width: 40%;">
                        <input type="number" name="stocks[]" value="100" placeholder="สต็อก" style="width: 20%;">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addVariant()" style="margin-top:10px; font-size: 0.9rem;">+ เพิ่มตัวเลือก</button>
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top: 20px;">บันทึกสินค้า</button>
        </form>
    </div>
</div>

<script>
function addVariant() {
    const container = document.getElementById('variants-container');
    const div = document.createElement('div');
    div.className = 'variant-group';
    div.innerHTML = `
        <input type="text" name="sizes[]" placeholder="ไซส์ (เช่น M)" required style="width: 40%;">
        <input type="number" name="prices[]" placeholder="ราคา" required style="width: 40%;">
        <input type="number" name="stocks[]" value="100" placeholder="สต็อก" style="width: 20%;">
        <button type="button" onclick="this.parentElement.remove()" style="background:red; color:white; border:none; border-radius:4px; cursor:pointer;">X</button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
