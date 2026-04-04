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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern SaaS Dashboard Layout */
        body { background: #f3f4f6; font-family: 'Kanit', sans-serif; }
        
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 1.8rem; margin: 0; color: #111827; }

        .promo-grid { display: grid; grid-template-columns: 1fr 380px; gap: 28px; }
        @media (max-width: 1024px) { .promo-grid { grid-template-columns: 1fr; } }

        /* Form Card */
        .modern-card { 
            background: white; border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03); 
            border: 1px solid #e5e7eb; padding: 32px;
        }

        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-weight: 500; color: #374151; margin-bottom: 8px; font-size: 0.95rem; }
        .form-group .required { color: #ef4444; }
        
        .form-control { 
            width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 8px; 
            font-family: 'Kanit', sans-serif; font-size: 1rem; color: #111827; transition: 0.2s; 
            background: #fff; box-sizing: border-box;
        }
        .form-control:focus { outline: none; border-color: #000; box-shadow: 0 0 0 3px rgba(0,0,0,0.05); }
        .form-control:disabled { background: #f9fafb; color: #6b7280; cursor: not-allowed; }
        
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; }

        textarea.form-control { resize: vertical; min-height: 120px; }

        .btn-modern { 
            display: inline-flex; align-items: center; justify-content: center; 
            padding: 14px 28px; background: #000; color: #fff; border: none; 
            border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; 
            transition: 0.2s; gap: 8px; font-family: 'Kanit', sans-serif; width: 100%;
        }
        .btn-modern:hover { background: #374151; transform: translateY(-1px); }

        .alert-success { background: #dcfce7; color: #166534; padding: 16px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 16px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px; border: 1px solid #fecaca; }

        /* Right Sidebar: Recent Promos */
        .sidebar-title { font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 600; color: #111827; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        
        .promo-list { display: flex; flex-direction: column; gap: 12px; }
        
        .promo-item { 
            background: white; border-radius: 12px; padding: 20px; 
            border: 1px solid #e5e7eb; transition: 0.2s;
        }
        .promo-item:hover { border-color: #d1d5db; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        .promo-item-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .promo-type-badge { 
            width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .badge-promo { background: #fef3c7; color: #d97706; }
        .badge-alert { background: #fee2e2; color: #ef4444; }
        .badge-info { background: #eff6ff; color: #3b82f6; }
        .promo-type-badge svg { width: 14px; height: 14px; }
        
        .promo-item-title { font-weight: 600; font-size: 0.95rem; color: #111827; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .promo-item-body { font-size: 0.85rem; color: #6b7280; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; }
        
        .promo-item-footer { display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #9ca3af; border-top: 1px solid #f3f4f6; padding-top: 12px; }
        .read-badge { background: #f3f4f6; padding: 2px 8px; border-radius: 10px; font-weight: 600; }

        .empty-state { background: white; border-radius: 12px; padding: 40px; text-align: center; color: #9ca3af; border: 1px solid #e5e7eb; }
        .empty-state svg { margin-bottom: 12px; opacity: 0.4; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="page-header">
        <h1><?= __('apromo_title') ?></h1>
    </div>

    <?php if ($success): ?>
        <div class="alert-success">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="promo-grid">
        <!-- Left: Create Form -->
        <div class="modern-card">
            <form action="promotions.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token'] ?? '') ?>">

                <div class="form-group">
                    <label><?= __('apromo_audience') ?></label>
                    <input type="text" value="<?= __('apromo_all_members') ?>" disabled class="form-control">
                </div>

                <div class="form-group">
                    <label><?= __('apromo_type') ?></label>
                    <select name="type" class="form-control">
                        <option value="promo"><?= __('apromo_type_promo') ?></option>
                        <option value="alert"><?= __('apromo_type_alert') ?></option>
                        <option value="info"><?= __('apromo_type_info') ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= __('apromo_subject') ?> <span class="required">*</span></label>
                    <input type="text" name="title" required placeholder="<?= __('apromo_subject_placeholder') ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label><?= __('apromo_body') ?> <span class="required">*</span></label>
                    <textarea name="message" required rows="5" placeholder="<?= __('apromo_body_placeholder') ?>" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn-modern">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    <?= __('apromo_btn_send') ?>
                </button>
            </form>
        </div>

        <!-- Right: Past Promos -->
        <div>
            <h2 class="sidebar-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= __('apromo_recent') ?>
            </h2>
            <div class="promo-list">
                <?php if (empty($past_promos)): ?>
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <div><?= __('apromo_no_messages') ?></div>
                    </div>
                <?php else: ?>
                    <?php foreach ($past_promos as $promo): ?>
                        <div class="promo-item">
                            <div class="promo-item-header">
                                <?php if ($promo['type'] === 'promo'): ?>
                                    <div class="promo-type-badge badge-promo">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                    </div>
                                <?php elseif ($promo['type'] === 'alert'): ?>
                                    <div class="promo-type-badge badge-alert">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                    </div>
                                <?php else: ?>
                                    <div class="promo-type-badge badge-info">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                <?php endif; ?>
                                <h3 class="promo-item-title"><?= htmlspecialchars($promo['title']) ?></h3>
                            </div>
                            <p class="promo-item-body"><?= htmlspecialchars($promo['message']) ?></p>
                            <div class="promo-item-footer">
                                <span><?= date('d M Y', strtotime($promo['created_at'])) ?></span>
                                <span class="read-badge"><?= __('apromo_read_status') ?> <?= $promo['read_count'] ?> / <?= $promo['total_sent'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
