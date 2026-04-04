<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Order FIRST (needed by POST handler for email)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found");
}

// Fetch Items
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

// Handle Status/Tracking Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        die("Security Error: Invalid CSRF Token");
    }

    $allowedStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
    $newStatus = $_POST['status'];
    $tracking = trim($_POST['tracking_number'] ?? '');

    if (!in_array($newStatus, $allowedStatuses)) {
        die("Invalid status value");
    }
    
    $pdo->beginTransaction();
    try {
        $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
        $stmtUpdate->execute([$newStatus, $tracking, $id]);

        // Refund stock if cancelling
        if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
            $stmtRefundItems = $pdo->prepare("SELECT variant_id, quantity FROM order_items WHERE order_id = ?");
            $stmtRefundItems->execute([$id]);
            $refundItems = $stmtRefundItems->fetchAll();
            
            $stmtRefundStock = $pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");
            foreach ($refundItems as $ri) {
                if ($ri['variant_id'] > 0) { // Only refund for variant items
                    $stmtRefundStock->execute([$ri['quantity'], $ri['variant_id']]);
                }
            }
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order update error: " . $e->getMessage());
        die("Error updating order. Please try again.");
    }
    
    // Send Email Notification
    $to = $order['email'];
    $subject = "Update on Order #" . str_pad($id, 6, '0', STR_PAD_LEFT);
    $message = "Your order status is now: " . ucfirst($newStatus);
    if ($tracking) {
        $message .= "\nTracking Number: " . $tracking;
    }
    $headers = "From: no-reply@xivex.com";
    
    // Attempt to send email (suppress errors on localhost)
    @mail($to, $subject, $message, $headers);

    // Refresh
    header("Location: order_details.php?id=" . $id . "&mail_sent=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?> - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern Order Details */
        body { background: #f3f4f6; font-family: 'Kanit', sans-serif; }
        
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px;
        }
        .page-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem; margin: 0; color: #111827; 
            display: flex; align-items: center; gap: 12px;
        }
        .status-badge {
            font-family: 'Kanit', sans-serif;
            font-size: 0.85rem; padding: 4px 12px; border-radius: 9999px; font-weight: 500;
        }
        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-paid { background: #ecfdf5; color: #047857; }
        .badge-shipped { background: #eff6ff; color: #1d4ed8; }
        .badge-completed { background: #f3f4f6; color: #374151; }
        .badge-cancelled { background: #fef2f2; color: #b91c1c; }

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
        
        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { 
            text-align: left; padding: 12px 8px; color: #6b7280; font-size: 0.85rem; 
            font-weight: 500; text-transform: uppercase; border-bottom: 1px solid #e5e7eb;
        }
        .items-table td { padding: 16px 8px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
        .items-table tr:last-child td { border-bottom: none; }
        
        .product-cell { font-weight: 500; color: #111827; }
        
        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 500; color: #374151; margin-bottom: 8px; }
        .modern-input, .modern-select {
            width: 100%; box-sizing: border-box;
            padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; background: #f9fafb;
            transition: all 0.2s;
        }
        .modern-input:focus, .modern-select:focus {
            outline: none; border-color: #000; box-shadow: 0 0 0 3px rgba(0,0,0,0.05); background: #fff;
        }
        
        /* Buttons */
        .btn-modern {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 8px;
            font-family: inherit; font-weight: 500; font-size: 0.95rem; cursor: pointer;
            transition: all 0.2s; text-decoration: none; gap: 8px;
        }
        .btn-modern:hover { background: #374151; transform: translateY(-1px); }
        .btn-outline-modern { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        .btn-outline-modern:hover { background: #f9fafb; border-color: #9ca3af; }
        .btn-danger-modern { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .btn-danger-modern:hover { background: #fee2e2; border-color: #fca5a5; }

        /* Detail List */
        .detail-list { display: flex; flex-direction: column; gap: 12px; }
        .detail-item { display: flex; flex-direction: column; }
        .detail-item .label { font-size: 0.8rem; color: #6b7280; text-transform: uppercase; font-weight: 500; }
        .detail-item .value { font-size: 1rem; color: #111827; margin-top: 2px; }
        .total-price { font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 700; color: #000; }
        
        .slip-img { width: 100%; max-width: 350px; border-radius: 8px; border: 1px solid #e5e7eb; margin-top: 10px; transition: transform 0.3s; }
        .slip-img:hover { transform: scale(1.02); cursor: pointer; }

        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="page-header">
        <h1>
            <?= __('aod_order') ?> #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
            <span class="status-badge badge-<?= htmlspecialchars($order['status']) ?>">
                <?= ucfirst(htmlspecialchars($order['status'])) ?>
            </span>
        </h1>
        <a href="orders.php" class="btn-modern btn-outline-modern">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <?= __('aod_back') ?>
        </a>
    </div>

    <?php if (isset($_GET['mail_sent'])): ?>
        <div style="background: #d1e7dd; color: #0f5132; padding: 12px 20px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #badbcc;">
            <?= __('aod_btn_update') ?> Success - Notification Email Sent.
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Left Column: Items & Actions -->
        <div class="grid-left">
            <div class="modern-card">
                <h2><?= __('aod_items') ?></h2>
                <div style="overflow-x: auto;">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th><?= __('aod_th_product') ?></th>
                                <th><?= __('aod_th_size') ?></th>
                                <th><?= __('aod_th_price') ?></th>
                                <th><?= __('aod_th_qty') ?></th>
                                <th style="text-align: right;"><?= __('aod_th_total') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="product-cell"><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= htmlspecialchars($item['size']) ?></td>
                                <td>฿<?= number_format($item['price'], 0) ?></td>
                                <td>x<?= $item['quantity'] ?></td>
                                <td style="text-align: right; font-weight: 500;">฿<?= number_format($item['subtotal'], 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modern-card">
                <h2><?= __('aod_manage') ?></h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label><?= __('aod_status') ?></label>
                            <select name="status" class="modern-select">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>><?= __('aod_status_pending') ?></option>
                                <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>><?= __('aod_status_paid') ?></option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>><?= __('aod_status_shipped') ?></option>
                                <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>><?= __('aod_status_completed') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?= __('aod_tracking') ?></label>
                            <input type="text" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="<?= __('aod_tracking_placeholder') ?>" class="modern-input">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap;">
                        <button type="submit" name="update_order" class="btn-modern">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <?= __('aod_btn_update') ?>
                        </button>
                        <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn-modern btn-outline-modern">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <?= __('aod_btn_print') ?>
                        </a>
                    </div>
                </form>

                <?php if ($order['status'] != 'cancelled' && $order['status'] != 'completed'): ?>
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f3f4f6;">
                    <form method="POST" onsubmit="return confirm('<?= __('aod_cancel_confirm') ?>');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                        <input type="hidden" name="status" value="cancelled">
                        <input type="hidden" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>">
                        <button type="submit" name="update_order" class="btn-modern btn-danger-modern">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <?= __('aod_cancel_order') ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Info Summaries -->
        <div class="grid-right">
            <div class="modern-card">
                <h2><?= __('aod_customer_info') ?></h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <span class="label"><?= __('aod_name') ?></span>
                        <span class="value" style="font-weight: 500;"><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?= __('aod_phone') ?></span>
                        <span class="value"><?= htmlspecialchars($order['phone']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?= __('aod_email') ?></span>
                        <span class="value"><?= htmlspecialchars($order['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?= __('aod_address') ?></span>
                        <span class="value" style="line-height: 1.5;"><?= nl2br(htmlspecialchars($order['address'])) ?></span>
                    </div>
                </div>
            </div>

            <div class="modern-card">
                <h2><?= __('aod_payment_info') ?></h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <span class="label"><?= __('aod_order_date') ?></span>
                        <span class="value"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?= __('aod_method') ?></span>
                        <span class="value" style="font-weight: 600;">
                            <?= strtoupper($order['payment_method']) ?>
                        </span>
                    </div>
                    <div class="detail-item" style="margin-top: 8px;">
                        <span class="label"><?= __('aod_total') ?></span>
                        <span class="total-price">฿<?= number_format($order['total_price'], 0) ?></span>
                    </div>
                    
                    <?php if ($order['payment_slip']): ?>
                        <div class="detail-item" style="margin-top: 15px;">
                            <span class="label"><?= __('aod_slip') ?></span>
                            <a href="../<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank" title="Click to view full image">
                                <img src="../<?= htmlspecialchars($order['payment_slip']) ?>" class="slip-img" alt="Payment Slip">
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
