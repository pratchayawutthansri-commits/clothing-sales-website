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
    <h1>Add New Product</h1>
    
    <div class="form-container">
        <form action="process_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
            
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" required placeholder="e.g. Oversized Black T-Shirt">
            </div>

            <div style="display:flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex:1;">
                    <label>Badge</label>
                    <input type="text" name="badge" placeholder="e.g. New Arrival, Sale, Best Seller">
                </div>
                <div style="flex:0 0 150px; display:flex; align-items:flex-end;">
                     <label style="cursor:pointer; display:flex; align-items:center;">
                        <input type="checkbox" name="is_visible" value="1" checked style="width:20px; height:20px; margin-right:10px;">
                        <span>Visible</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Tops">Tops</option>
                    <option value="Bottoms">Bottoms</option>
                    <option value="Outerwear">Outerwear</option>
                    <option value="Accessories">Accessories</option>
                </select>
            </div>

            <div class="form-group">
                <label>Product Description</label>
                <textarea name="description" rows="5" required placeholder="Description..."></textarea>
            </div>

            <div class="form-group">
                <label>Base Price (THB)</label>
                <input type="number" name="base_price" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>Main Image</label>
                <input type="file" name="image" required accept="image/*">
            </div>

            <div class="form-group">
                <label>Product Variants</label>
                <div id="variants-container">
                    <div class="variant-group">
                        <input type="text" name="sizes[]" placeholder="Size (e.g. S)" required style="width: 40%;">
                        <input type="number" name="prices[]" placeholder="Price" required style="width: 40%;">
                        <input type="number" name="stocks[]" value="100" placeholder="Stock" style="width: 20%;">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addVariant()" style="margin-top:10px; font-size: 0.9rem;">+ Add Variant</button>
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top: 20px;">Save Product</button>
        </form>
    </div>
</div>

<script>
function addVariant() {
    const container = document.getElementById('variants-container');
    const div = document.createElement('div');
    div.className = 'variant-group';
    div.innerHTML = `
        <input type="text" name="sizes[]" placeholder="Size (e.g. M)" required style="width: 40%;">
        <input type="number" name="prices[]" placeholder="Price" required style="width: 40%;">
        <input type="number" name="stocks[]" value="100" placeholder="Stock" style="width: 20%;">
        <button type="button" onclick="this.parentElement.remove()" style="background:red; color:white; border:none; border-radius:4px; cursor:pointer;">X</button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
