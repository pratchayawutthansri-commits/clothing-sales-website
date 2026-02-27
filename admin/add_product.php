<?php
require_once 'includes/config.php';
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('aap_title') ?> - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Page-specific: Add Product */
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        .variant-group { background: #f4f4f4; padding: 15px; border-radius: 4px; margin-bottom: 10px; display: flex; gap: 10px; align-items: center; }
        .variant-group input { margin-bottom: 0; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <h1><?= __('aap_title') ?></h1>
    
    <div class="form-container">
        <form action="process_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
            
            <div class="form-group">
                <label><?= __('aap_name') ?></label>
                <input type="text" name="name" required placeholder="<?= __('aap_name_ph') ?>">
            </div>

            <div style="display:flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex:1;">
                    <label><?= __('aap_badge') ?></label>
                    <input type="text" name="badge" placeholder="<?= __('aap_badge_ph') ?>">
                </div>
                <div style="flex:0 0 150px; display:flex; align-items:flex-end;">
                     <label style="cursor:pointer; display:flex; align-items:center;">
                        <input type="checkbox" name="is_visible" value="1" checked style="width:20px; height:20px; margin-right:10px;">
                        <span><?= __('aap_visible') ?></span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label><?= __('aap_category') ?></label>
                <select name="category">
                    <option value="Tops">Tops</option>
                    <option value="Bottoms">Bottoms</option>
                    <option value="Outerwear">Outerwear</option>
                    <option value="Accessories">Accessories</option>
                </select>
            </div>

            <div class="form-group">
                <label><?= __('aap_desc') ?></label>
                <textarea name="description" rows="5" required placeholder="<?= __('aap_desc_ph') ?>"></textarea>
            </div>

            <div class="form-group">
                <label><?= __('aap_base_price') ?></label>
                <input type="number" name="base_price" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label><?= __('aap_main_image') ?></label>
                <input type="file" name="image" required accept="image/*">
            </div>

            <div class="form-group">
                <label><?= __('aap_variants') ?></label>
                <div id="variants-container">
                    <div class="variant-group">
                        <input type="text" name="sizes[]" placeholder="<?= __('aap_var_size') ?>" style="flex: 2;">
                        <input type="number" name="prices[]" placeholder="<?= __('aap_var_price') ?>" min="0" step="0.01" style="flex: 2;">
                        <input type="number" name="stocks[]" value="100" placeholder="<?= __('aap_var_stock') ?>" min="0" style="flex: 1;">
                        <button type="button" onclick="this.parentElement.remove()" style="background:#dc3545; color:white; border:none; border-radius:4px; padding: 10px 15px; cursor:pointer;" title="Remove Variant">X</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addVariant()" style="margin-top:10px; font-size: 0.9rem;"><?= __('aap_add_variant') ?></button>
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top: 20px;"><?= __('aap_btn_save') ?></button>
        </form>
    </div>
</div>

<script>
function addVariant() {
    const container = document.getElementById('variants-container');
    const div = document.createElement('div');
    div.className = 'variant-group';
    div.innerHTML = `
        <input type="text" name="sizes[]" placeholder="<?= __('aap_var_size') ?>" style="flex: 2;">
        <input type="number" name="prices[]" placeholder="<?= __('aap_var_price') ?>" min="0" step="0.01" style="flex: 2;">
        <input type="number" name="stocks[]" value="100" placeholder="<?= __('aap_var_stock') ?>" min="0" style="flex: 1;">
        <button type="button" onclick="this.parentElement.remove()" style="background:#dc3545; color:white; border:none; border-radius:4px; padding: 10px 15px; cursor:pointer;" title="Remove Variant">X</button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
