<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        $error = "Security Check Failed: Invalid Token";
    } else {
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'promo';

        if (empty($title) || empty($message)) {
            $error = "Please fill in all required fields.";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Insert into notifications table
                $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
                $stmt->execute([$title, $message, $type]);
                $notification_id = $pdo->lastInsertId();

                // 2. Insert into user_notifications for ALL active users
                $stmtUsers = $pdo->query("SELECT id FROM users WHERE role = 'user'");
                $users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($users)) {
                    $insertValues = [];
                    $params = [];
                    foreach ($users as $u_id) {
                        $insertValues[] = "(?, ?)";
                        $params[] = $u_id;
                        $params[] = $notification_id;
                    }
                    
                    // Batch insert to user_notifications
                    $sql = "INSERT INTO user_notifications (user_id, notification_id) VALUES " . implode(', ', $insertValues);
                    $stmtBatch = $pdo->prepare($sql);
                    $stmtBatch->execute($params);
                }

                $pdo->commit();
                $success = "Message successfully sent to " . count($users) . " members!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "An error occurred while sending the message.";
                error_log("Promo Error: " . $e->getMessage());
            }
        }
    }
}

// Fetch past promotions
$stmtPast = $pdo->query("SELECT n.*, (SELECT count(id) FROM user_notifications WHERE notification_id = n.id AND is_read = 1) as read_count, (SELECT count(id) FROM user_notifications WHERE notification_id = n.id) as total_sent FROM notifications n ORDER BY n.created_at DESC LIMIT 10");
$past_promos = $stmtPast->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Promotion - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

<main style="flex: 1; padding: 30px; background: #f8f9fa;">
        <h1 style="font-family: 'Outfit', sans-serif; margin-bottom: 30px;"><?= __('apromo_title') ?></h1>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            
            <!-- Left: Create Form -->
            <div>
                <?php if ($success): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 30px;">
                    <form action="promotions.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token'] ?? '') ?>">

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('apromo_audience') ?></label>
                            <input type="text" value="<?= __('apromo_all_members') ?>" disabled style="width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; background: #f8fafc; color: #64748b;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('apromo_type') ?></label>
                            <select name="type" style="width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; background: #fff;">
                                <option value="promo"><?= __('apromo_type_promo') ?></option>
                                <option value="alert"><?= __('apromo_type_alert') ?></option>
                                <option value="info"><?= __('apromo_type_info') ?></option>
                            </select>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('apromo_subject') ?> <span style="color:red">*</span></label>
                            <input type="text" name="title" required placeholder="<?= __('apromo_subject_placeholder') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none;">
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('apromo_body') ?> <span style="color:red">*</span></label>
                            <textarea name="message" required rows="5" placeholder="<?= __('apromo_body_placeholder') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; resize: vertical;"></textarea>
                        </div>

                        <button type="submit" style="background: #000; color: #fff; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            <?= __('apromo_btn_send') ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Past Promos -->
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; margin-bottom: 20px;"><?= __('apromo_recent') ?></h2>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php if (empty($past_promos)): ?>
                        <div style="background: white; border-radius: 12px; padding: 30px; text-align: center; color: #64748b; font-size: 0.9rem;">
                            <?= __('apromo_no_messages') ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($past_promos as $promo): ?>
                            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px -1px rgba(0,0,0,0.05);">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <?php
                                        $icon = 'ℹ️';
                                        if ($promo['type'] === 'promo') $icon = '🎉';
                                        if ($promo['type'] === 'alert') $icon = '⚠️';
                                    ?>
                                    <span><?= $icon ?></span>
                                    <h3 style="font-size: 1rem; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"><?= htmlspecialchars($promo['title']) ?></h3>
                                </div>
                                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($promo['message']) ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                                    <span><?= date('M j, Y', strtotime($promo['created_at'])) ?></span>
                                    <span><?= __('apromo_read_status') ?> <?= $promo['read_count'] ?> / <?= $promo['total_sent'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        </div>
</div>

</body>
</html>
