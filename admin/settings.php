<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check (Reuse admin token)
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        die("Security Error");
    }

    // 1. Handle Shop Settings
    if (isset($_POST['update_settings'])) {
        $data = [
            'bank_name' => trim($_POST['bank_name']),
            'bank_account' => trim($_POST['bank_account']),
            'bank_owner' => trim($_POST['bank_owner']),
            'shipping_cost' => max(0, (float)$_POST['shipping_cost'])
        ];

        $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($data as $key => $val) {
            $stmt->execute([$key, $val]);
        }
        $success = __('as_saved');
    }
    
}

// Fetch Current Settings
$currentSettings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern SaaS Dashboard Layout */
        body { background: #f3f4f6; font-family: 'Kanit', sans-serif; }
        
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 1.8rem; margin: 0; color: #111827; }

        .modern-card { 
            background: white; border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03); 
            border: 1px solid #e5e7eb; padding: 32px; max-width: 800px;
        }

        .section-title { font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 10px; }

        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-weight: 500; color: #374151; margin-bottom: 8px; font-size: 0.95rem; }
        .form-control { 
            width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; 
            font-family: 'Kanit', sans-serif; font-size: 1rem; color: #111827; transition: 0.2s; 
            background: #fff; box-sizing: border-box;
        }
        .form-control:focus { outline: none; border-color: #000; box-shadow: 0 0 0 3px rgba(0,0,0,0.05); }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

        .btn-modern { 
            display: inline-flex; align-items: center; justify-content: center; 
            padding: 14px 28px; background: #000; color: #fff; border: none; 
            border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; 
            transition: 0.2s; gap: 8px; font-family: 'Kanit', sans-serif; width: 100%;
        }
        .btn-modern:hover { background: #374151; }

        .alert-success { background: #dcfce7; color: #166534; padding: 16px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px; border: 1px solid #bbf7d0;}
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="page-header">
        <h1><?= __('as_title') ?></h1>
    </div>
    
    <?php if ($success): ?>
        <div class="alert-success" style="max-width: 800px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="modern-card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token'] ?? '') ?>">
            <input type="hidden" name="update_settings" value="1">
            
            <h3 class="section-title">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <?= __('as_bank_info') ?>
            </h3>
            
            <div class="form-group">
                <label><?= __('as_bank_name') ?></label>
                <input type="text" name="bank_name" class="form-control" placeholder="เช่น กสิกรไทย (KBank)" value="<?= htmlspecialchars($currentSettings['bank_name'] ?? '') ?>" required>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label><?= __('as_acc_number') ?></label>
                    <input type="text" name="bank_account" class="form-control" placeholder="เช่น 123-4-56789-0" value="<?= htmlspecialchars($currentSettings['bank_account'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label><?= __('as_acc_name') ?></label>
                    <input type="text" name="bank_owner" class="form-control" placeholder="เช่น บริษัท ไซเว็กซ์ จำกัด" value="<?= htmlspecialchars($currentSettings['bank_owner'] ?? '') ?>" required>
                </div>
            </div>

            <h3 class="section-title" style="margin-top: 20px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                <?= __('as_shipping') ?>
            </h3>
            <div class="form-group">
                <label><?= __('as_shipping_cost') ?> (THB)</label>
                <input type="number" name="shipping_cost" class="form-control" value="<?= htmlspecialchars($currentSettings['shipping_cost'] ?? '50') ?>" required min="0">
            </div>
            
            <div style="margin-top: 40px;">
                <button type="submit" class="btn-modern">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?= __('as_btn_save') ?>
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
