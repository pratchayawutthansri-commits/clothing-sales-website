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
            'bank_name' => $_POST['bank_name'],
            'bank_account' => $_POST['bank_account'],
            'bank_owner' => $_POST['bank_owner'],
            'shipping_cost' => $_POST['shipping_cost']
        ];

        $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($data as $key => $val) {
            $stmt->execute([$key, $val]);
        }
        $success = "บันทึกการตั้งค่าร้านค้าเรียบร้อยแล้ว";
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
    <title>ตั้งค่าผู้ดูแลระบบ - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; margin: 0; background: #f9f9f9; display: flex; }
        .sidebar { width: 250px; background: #1a1a1a; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { margin-top: 0; margin-bottom: 30px; letter-spacing: 1px;}
        .sidebar a { display: block; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #333; transition: 0.3s; }
        .sidebar a:hover { color: white; background: #333; padding-left: 20px; }
        .sidebar a.active { color: white; font-weight: bold; background: #333; border-left: 4px solid #fff; }
        .content { flex: 1; padding: 40px; }
        
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 500px; }
        h1 { margin-top: 0; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        button { padding: 10px 20px; background: #000; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #333; }
        
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="box" style="margin-bottom: 30px; max-width: 800px;">
        <h1>ตั้งค่าร้านค้า (Shop Settings)</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
            <input type="hidden" name="update_settings" value="1">
            
            <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">ข้อมูลธนาคาร (Bank Info)</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>ชื่อธนาคาร</label>
                    <input type="text" name="bank_name" value="<?= htmlspecialchars($currentSettings['bank_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>เลขที่บัญชี</label>
                    <input type="text" name="bank_account" value="<?= htmlspecialchars($currentSettings['bank_account'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>ชื่อบัญชี</label>
                    <input type="text" name="bank_owner" value="<?= htmlspecialchars($currentSettings['bank_owner'] ?? '') ?>" required>
                </div>
            </div>

            <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; margin-top: 30px;">การจัดส่ง (Shipping)</h3>
            <div class="form-group">
                <label>ค่าจัดส่ง (บาท)</label>
                <input type="number" name="shipping_cost" value="<?= htmlspecialchars($currentSettings['shipping_cost'] ?? '50') ?>" required min="0">
            </div>
            
            <button type="submit">บันทึกข้อมูลร้านค้า</button>
        </form>
    </div>


</div>

</body>
</html>
