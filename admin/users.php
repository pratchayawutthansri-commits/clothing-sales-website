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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern SaaS Dashboard Layout */
        body { background: #f3f4f6; font-family: 'Kanit', sans-serif; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 1.8rem; margin: 0; color: #111827; }
        
        .btn-modern { 
            display: inline-flex; align-items: center; justify-content: center; 
            padding: 10px 20px; background: #000; color: #fff; border: none; 
            border-radius: 8px; font-weight: 600; font-size: 0.95rem; text-decoration: none; 
            transition: 0.2s; gap: 8px; 
        }
        .btn-modern:hover { background: #374151; transform: translateY(-1px); }

        .modern-table-card { 
            background: white; border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03); 
            overflow: hidden; border: 1px solid #e5e7eb; 
        }
        .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .modern-table th { 
            background: #f9fafb; font-weight: 600; color: #4b5563; font-size: 0.85rem; 
            text-transform: uppercase; letter-spacing: 0.05em; padding: 16px 24px; 
            text-align: left; border-bottom: 1px solid #e5e7eb; 
        }
        .modern-table td { 
            padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; 
            transition: background 0.2s; 
        }
        .modern-table tbody tr:hover td { background: #f9fafb; }
        .modern-table tbody tr:last-child td { border-bottom: none; }

        .user-id { color: #6b7280; font-family: 'Outfit', monospace; font-size: 0.9rem; }
        .primary-text { font-weight: 600; color: #111827; margin-bottom: 2px; }
        .secondary-text { font-size: 0.85rem; color: #6b7280; }
        
        .btn-danger { 
            background: #fee2e2; color: #ef4444; border: none; padding: 8px 12px; 
            border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; 
            transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; 
        }
        .btn-danger:hover { background: #fecaca; }

        .alert-success { background: #dcfce7; color: #166534; padding: 16px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px; border: 1px solid #bbf7d0;}
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="page-header">
        <h1><?= __('au_title') ?></h1>
        <a href="promotions.php" class="btn-modern">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <?= __('au_btn_promo') ?>
        </a>
    </div>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert-success">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?= __('au_msg_deleted') ?>
        </div>
    <?php endif; ?>

    <div class="modern-table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 80px;"><?= __('au_th_id') ?></th>
                    <th><?= __('au_th_member') ?></th>
                    <th><?= __('au_th_email') ?></th>
                    <th><?= __('au_th_joined') ?></th>
                    <th><?= __('au_th_orders') ?></th>
                    <th><?= __('au_th_spent') ?></th>
                    <th style="text-align: right;"><?= __('au_th_action') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #6b7280; font-size: 1.1rem;">
                           <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:48px; height:48px; margin-bottom:10px; opacity:0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg><br>
                           <?= __('au_no_users') ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="user-id">#<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="primary-text"><?= htmlspecialchars($user['name']) ?></div>
                                <div class="secondary-text">@<?= htmlspecialchars($user['username']) ?></div>
                            </td>
                            <td class="secondary-text"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="secondary-text"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td class="primary-text"><?= $user['order_count'] ?></td>
                            <td class="primary-text"><?= $user['total_spent'] ? '฿' . number_format($user['total_spent'], 0) : '-' ?></td>
                            <td style="text-align: right;">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('<?= __('au_confirm_delete') ?>');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn-danger" title="<?= __('au_btn_delete') ?>">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        ลบข้อมูล
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
