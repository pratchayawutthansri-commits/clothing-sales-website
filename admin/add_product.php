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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern Form Layout */
        body { background: #f3f4f6; font-family: 'Kanit', sans-serif; }
        
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px;
        }
        .page-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem; margin: 0; color: #111827; 
        }

        .dashboard-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 24px;
            align-items: start;
        }
        
        .modern-card {
            background: #ffffff; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
            padding: 24px; margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }
        .modern-card h2 {
            font-size: 1.1rem; color: #111827; margin: 0 0 20px 0;
            padding-bottom: 12px; border-bottom: 1px solid #f3f4f6;
            font-weight: 600;
        }
        
        /* Modern Inputs */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 500; color: #374151; margin-bottom: 8px; }
        .modern-input, .modern-select, .modern-textarea {
            width: 100%; box-sizing: border-box;
            padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; background: #f9fafb;
            transition: all 0.2s;
        }
        .modern-textarea { resize: vertical; min-height: 120px; }
        .modern-input:focus, .modern-select:focus, .modern-textarea:focus {
            outline: none; border-color: #000; box-shadow: 0 0 0 3px rgba(0,0,0,0.05); background: #fff;
        }
        
        /* Variants */
        .variant-group { 
            background: #f9fafb; border: 1px solid #e5e7eb; 
            padding: 16px; border-radius: 8px; margin-bottom: 12px; 
            display: flex; gap: 12px; align-items: flex-end; 
        }
        .variant-field { flex: 1; }
        .variant-field label { font-size: 0.8rem; color: #6b7280; font-weight: 400; margin-bottom: 4px; }
        
        /* Buttons */
        .btn-modern {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 24px; background: #000; color: #fff; border: none; border-radius: 8px;
            font-family: inherit; font-weight: 600; font-size: 1rem; cursor: pointer;
            transition: all 0.2s; text-decoration: none; width: 100%; gap: 8px;
        }
        .btn-modern:hover { background: #374151; transform: translateY(-1px); }
        .btn-outline { background: #fff; color: #111827; border: 1px solid #d1d5db; }
        .btn-outline:hover { background: #f9fafb; }
        .btn-danger-icon { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
        .btn-danger-icon:hover { background: #fecaca; }

        /* Media */
        .custom-file-upload {
            border: 1px dashed #d1d5db; display: flex; align-items: center; justify-content: center;
            padding: 30px; border-radius: 8px; background: #f9fafb; cursor: pointer;
            color: #6b7280; font-size: 0.9rem; transition: 0.2s;
        }
        .custom-file-upload:hover { border-color: #9ca3af; color: #374151; background: #f3f4f6; }
        input[type="file"] { display: none; }
        
        /* Toggle */
        .toggle-label { display: flex; align-items: center; cursor: pointer; gap: 10px; font-weight: 500; color: #111827; }
        .toggle-switch { position: relative; width: 44px; height: 24px; background: #d1d5db; border-radius: 24px; transition: 0.3s; }
        .toggle-switch::after {
            content: ''; position: absolute; top: 2px; left: 2px;
            width: 20px; height: 20px; background: white; border-radius: 50%; transition: 0.3s;
        }
        input:checked + .toggle-switch { background: #000; }
        input:checked + .toggle-switch::after { transform: translateX(20px); }

        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="page-header">
        <h1><?= __('aap_title') ?></h1>
        <a href="products.php" class="btn-modern btn-outline" style="width: auto; padding: 10px 20px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 5px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            กลับสู่หน้ารายการสินค้า
        </a>
    </div>
    
    <form action="process_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
        
        <div class="dashboard-grid">
            <!-- Left Column: Primary Details -->
            <div class="grid-left">
                <div class="modern-card">
                    <h2>ข้อมูลพื้นฐานสินค้า</h2>
                    
                    <div class="form-group">
                        <label><?= __('aap_name') ?></label>
                        <input type="text" name="name" required placeholder="<?= __('aap_name_ph') ?>" class="modern-input">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label><?= __('aap_category') ?></label>
                            <select name="category" class="modern-select">
                                <option value="Tops">Tops</option>
                                <option value="Bottoms">Bottoms</option>
                                <option value="Outerwear">Outerwear</option>
                                <option value="Accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= __('aap_base_price') ?></label>
                            <input type="number" name="base_price" required min="0" step="0.01" placeholder="0.00" class="modern-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= __('aap_desc') ?></label>
                        <textarea name="description" required placeholder="<?= __('aap_desc_ph') ?>" class="modern-textarea"></textarea>
                    </div>
                </div>

                <div class="modern-card">
                    <h2><?= __('aap_variants') ?> (ขนาดและสต็อก)</h2>
                    <div id="variants-container">
                        <div class="variant-group">
                            <div class="variant-field">
                                <label>Size (ขนาด)</label>
                                <input type="text" name="sizes[]" placeholder="e.g. M" class="modern-input">
                            </div>
                            <div class="variant-field">
                                <label>Price (ราคาเฉพาะไซส์)</label>
                                <input type="number" name="prices[]" placeholder="0.00" min="0" step="0.01" class="modern-input">
                            </div>
                            <div class="variant-field">
                                <label>Stock (จำนวนเริ่ม)</label>
                                <input type="number" name="stocks[]" value="100" min="0" class="modern-input">
                            </div>
                            <button type="button" class="btn-modern btn-danger-icon" onclick="this.parentElement.remove()" style="width: auto; padding: 10px; margin-bottom: 2px;" title="ลบตัวเลือกนี้">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-modern btn-outline" onclick="addVariant()" style="margin-top:10px; width: auto;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <?= __('aap_add_variant') ?>
                    </button>
                </div>
            </div>

            <!-- Right Column: Media & Settings -->
            <div class="grid-right">
                <div class="modern-card" style="padding-bottom: 30px;">
                    <h2>เพิ่มสินค้าใหม่</h2>
                    <button type="submit" class="btn-modern">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <?= __('aap_btn_save') ?>
                    </button>
                </div>

                <div class="modern-card">
                    <h2>สถานะการแสดงผล</h2>
                    
                    <div class="form-group" style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
                        <label class="toggle-label">
                            <input type="checkbox" name="is_visible" value="1" checked style="display:none;">
                            <div class="toggle-switch"></div>
                            <span><?= __('aap_visible') ?> (แสดงหน้าร้านทันที)</span>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label><?= __('aap_badge') ?> (ป้ายกำกับ)</label>
                        <input type="text" name="badge" placeholder="<?= __('aap_badge_ph') ?>" class="modern-input">
                        <span style="font-size:0.8rem; color:#6b7280; display:block; margin-top:5px;">เช่น New Arrival, Sale 50%</span>
                    </div>
                </div>

                <div class="modern-card">
                    <h2><?= __('aap_main_image') ?> <span style="color:#ef4444;">*</span></h2>

                    <label class="custom-file-upload">
                        <input type="file" name="image" required accept="image/*" id="imgInp">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span id="fileName">คลิกเพื่ออัปโหลดรูปภาพสินค้า</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('imgInp').onchange = function() {
    const fileNameDisplay = document.getElementById('fileName');
    if (this.files && this.files[0]) {
        fileNameDisplay.textContent = 'เตรียมอัปโหลด: ' + this.files[0].name;
        fileNameDisplay.style.color = '#059669';
        fileNameDisplay.style.fontWeight = 'bold';
    }
};

function addVariant() {
    const container = document.getElementById('variants-container');
    const div = document.createElement('div');
    div.className = 'variant-group';
    div.innerHTML = `
        <div class="variant-field">
            <label>Size (ขนาด)</label>
            <input type="text" name="sizes[]" placeholder="e.g. M" class="modern-input">
        </div>
        <div class="variant-field">
            <label>Price (ราคาเฉพาะไซส์)</label>
            <input type="number" name="prices[]" placeholder="0.00" min="0" step="0.01" class="modern-input">
        </div>
        <div class="variant-field">
            <label>Stock (จำนวนเริ่ม)</label>
            <input type="number" name="stocks[]" value="100" min="0" class="modern-input">
        </div>
        <button type="button" class="btn-modern btn-danger-icon" onclick="this.parentElement.remove()" style="width: auto; padding: 10px; margin-bottom: 2px;" title="ลบตัวเลือกนี้">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
