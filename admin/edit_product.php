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
    die("Product not found.");
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
    <title>Edit Product - Xivex Admin</title>
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
    <h1>Edit Product: <?= htmlspecialchars($product['name']) ?></h1>
    
    <div class="form-container">
        <form action="update_product_logic.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div style="display:flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex:1;">
                    <label>Badge</label>
                    <input type="text" name="badge" value="<?= htmlspecialchars($product['badge'] ?? '') ?>" placeholder="e.g. New Arrival, Sale">
                </div>
                <div style="flex:0 0 150px; display:flex; align-items:flex-end;">
                     <label style="cursor:pointer; display:flex; align-items:center;">
                        <input type="checkbox" name="is_visible" value="1" <?= $product['is_visible'] ? 'checked' : '' ?> style="width:20px; height:20px; margin-right:10px;">
                        <span>Visible</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Tops" <?= $product['category'] == 'Tops' ? 'selected' : '' ?>>Tops</option>
                    <option value="Bottoms" <?= $product['category'] == 'Bottoms' ? 'selected' : '' ?>>Bottoms</option>
                    <option value="Outerwear" <?= $product['category'] == 'Outerwear' ? 'selected' : '' ?>>Outerwear</option>
                    <option value="Accessories" <?= $product['category'] == 'Accessories' ? 'selected' : '' ?>>Accessories</option>
                </select>
            </div>

            <div class="form-group">
                <label>Product Description</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Base Price (THB)</label>
                <input type="number" name="base_price" value="<?= $product['base_price'] ?>" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>Main Image (Upload new to replace)</label>
                <input type="file" name="image" accept="image/*">
                <?php if ($product['image']): ?>
                    <div style="margin-top:5px; font-size:0.8rem; color:#666;">Current Image:</div>
                    <img src="../<?= htmlspecialchars($product['image']) ?>" class="current-img">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Product Variants</label>
                <div id="variants-container">
                    <?php foreach ($variants as $v): ?>
                    <div class="variant-group">
                        <input type="hidden" name="existing_variant_ids[]" value="<?= $v['id'] ?>">
                        <input type="text" name="existing_sizes[]" value="<?= htmlspecialchars($v['size']) ?>" required style="width: 30%;" placeholder="Size">
                        <input type="number" name="existing_prices[]" value="<?= $v['price'] ?>" required style="width: 30%;" placeholder="Price">
                        <input type="number" name="existing_stocks[]" value="<?= $v['stock'] ?>" required style="width: 25%;" placeholder="Stock">
                        <span style="font-size: 0.8rem; color: #888;">(Edit)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="new-variants-container"></div>
                
                <button type="button" class="btn btn-secondary" onclick="addVariant()" style="margin-top:10px; font-size: 0.9rem;">+ Add New Variant</button>
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top: 20px;">Save Changes</button>
        </form>
    </div>
</div>

<script>
function addVariant() {
    const container = document.getElementById('new-variants-container');
    const div = document.createElement('div');
    div.className = 'variant-group';
    div.innerHTML = `
        <input type="text" name="new_sizes[]" placeholder="Size (e.g. M)" required style="width: 30%;">
        <input type="number" name="new_prices[]" placeholder="Price" required style="width: 30%;">
        <input type="number" name="new_stocks[]" value="100" placeholder="Stock" style="width: 25%;">
        <button type="button" onclick="this.parentElement.remove()" style="background:red; color:white; border:none; border-radius:4px; cursor:pointer; padding: 5px 10px;">X</button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
