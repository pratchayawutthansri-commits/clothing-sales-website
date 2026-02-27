<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

// Auto-fix: ensure all non-admin users have role = 'user'
$pdo->exec("UPDATE users SET role = 'user' WHERE role != 'admin' AND (role IS NULL OR role = '' OR role NOT IN ('user','admin'))");

// Fetch users
$stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(id) FROM orders WHERE user_id = u.id) as order_count, (SELECT SUM(total_price) FROM orders WHERE user_id = u.id AND status != 'cancelled') as total_spent FROM users u WHERE u.role = 'user' ORDER BY u.created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Delete (POST only with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        die("Security Error: Invalid CSRF Token");
    }
    $idToDelete = (int)$_POST['delete_user'];
    try {
        $pdo->beginTransaction();
        // Nullify orders first (before FK constraint kicks in)
        $stmtNullify = $pdo->prepare("UPDATE orders SET user_id = NULL WHERE user_id = ?");
        $stmtNullify->execute([$idToDelete]);
        // Delete user
        $stmtDelete = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmtDelete->execute([$idToDelete]);
        $pdo->commit();
        header("Location: users.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("User Delete Error: " . $e->getMessage());
        header("Location: users.php?msg=error");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

<div class="content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-family: 'Outfit', sans-serif;"><?= __('au_title') ?></h1>
            <a href="promotions.php" style="background: #000; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500;"><?= __('au_btn_promo') ?></a>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                <?= __('au_msg_deleted') ?>
            </div>
        <?php endif; ?>

        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_id') ?></th>
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_member') ?></th>
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_email') ?></th>
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_joined') ?></th>
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_orders') ?></th>
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_spent') ?></th>
                        <th style="padding: 15px; font-weight: 600; color: #475569;"><?= __('au_th_action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: #64748b;"><?= __('au_no_users') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 15px; color: #64748b;">#<?= $user['id'] ?></td>
                                <td style="padding: 15px;">
                                    <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($user['name']) ?></div>
                                    <div style="font-size: 0.85rem; color: #64748b;">@<?= htmlspecialchars($user['username']) ?></div>
                                </td>
                                <td style="padding: 15px; color: #475569;"><?= htmlspecialchars($user['email']) ?></td>
                                <td style="padding: 15px; color: #475569;"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td style="padding: 15px; color: #0f172a; font-weight: 500;"><?= $user['order_count'] ?></td>
                                <td style="padding: 15px; color: #0f172a; font-weight: 500;"><?= $user['total_spent'] ? '฿' . number_format($user['total_spent'], 0) : '-' ?></td>
                                <td style="padding: 15px;">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('<?= __('au_confirm_delete') ?>');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                                        <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">
                                        <button type="submit" style="background:none; border:none; color:#ef4444; font-weight:600; font-size:0.9rem; cursor:pointer; padding:0;"><?= __('au_btn_delete') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
</div>

</body>
</html>
