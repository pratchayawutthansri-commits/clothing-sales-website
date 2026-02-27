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
    
    $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
    $stmtUpdate->execute([$newStatus, $tracking, $id]);

    // Refund stock if cancelling
    if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
        $stmtRefundItems = $pdo->prepare("SELECT variant_id, quantity FROM order_items WHERE order_id = ?");
        $stmtRefundItems->execute([$id]);
        $refundItems = $stmtRefundItems->fetchAll();
        
        $stmtRefundStock = $pdo->prepare("UPDATE product_variants SET stock = stock + ? WHERE id = ?");
        foreach ($refundItems as $ri) {
            $stmtRefundStock->execute([$ri['quantity'], $ri['variant_id']]);
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Page-specific: Order Details */
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .order-header h1 { margin: 0; font-size: 1.8rem; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .info-group h3 { margin-top: 0; color: #555; font-size: 1rem; text-transform: uppercase; }
        .info-group p { margin: 5px 0; color: #333; }
        
        .table-items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-items th, .table-items td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        .table-items th { background: #f8f8f8; }
        
        .status-form { display: flex; gap: 10px; align-items: center; }
        
        input[type="text"], 
        select { 
            padding: 10px 12px; 
            border-radius: 6px; 
            border: 1px solid #ddd; 
            font-family: 'Kanit'; 
            font-size: 0.95rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }
        input[type="text"]:focus, 
        select:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        .btn-update { padding: 8px 20px; background: #000; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #666; text-decoration: none; transition: 0.3s; }
        .btn-back:hover { color: #000; padding-left: 5px; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <a href="orders.php" class="btn-back"><?= __('aod_back') ?></a>

    <div class="box">
        <div class="order-header">
            <h1><?= __('aod_order') ?> #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h1>
        </div>
        <div class="actions">
            <h2><?= __('aod_manage') ?></h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                
                <div class="form-group">
                    <label><?= __('aod_tracking') ?></label>
                    <input type="text" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="<?= __('aod_tracking_placeholder') ?>" style="width:100%; padding:10px; border:1px solid #ddd; margin-bottom:15px;">
                </div>

                <div class="form-group">
                    <label><?= __('aod_status') ?></label>
                    <select name="status" style="width:100%; padding: 10px; margin-bottom: 20px;">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>><?= __('aod_status_pending') ?></option>
                        <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>><?= __('aod_status_paid') ?></option>
                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>><?= __('aod_status_shipped') ?></option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>><?= __('aod_status_completed') ?></option>
                    </select>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <button type="submit" name="update_order" class="btn"><?= __('aod_btn_update') ?></button>
                        <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-secondary"><?= __('aod_btn_print') ?></a>
                    </div>
                </div>
            </form>

            <?php if ($order['status'] != 'cancelled' && $order['status'] != 'completed'): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <h3 style="color: #dc3545; font-size: 1rem; margin-top: 0;"><?= __('aod_danger') ?></h3>
                <form method="POST" onsubmit="return confirm('<?= __('aod_cancel_confirm') ?>');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <input type="hidden" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>">
                    <button type="submit" name="update_order" class="btn" style="background: #dc3545;"><?= __('aod_cancel_order') ?></button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-group">
                <h3><?= __('aod_customer_info') ?></h3>
                <p><b><?= __('aod_name') ?></b> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><b><?= __('aod_email') ?></b> <?= htmlspecialchars($order['email']) ?></p>
                <p><b><?= __('aod_phone') ?></b> <?= htmlspecialchars($order['phone']) ?></p>
                <p><b><?= __('aod_address') ?></b> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
            </div>
            <div class="info-group">
                <h3><?= __('aod_payment_info') ?></h3>
                <p><b><?= __('aod_order_date') ?></b> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
                <p><b><?= __('aod_method') ?></b> <?= strtoupper($order['payment_method']) ?></p>
                <p><b><?= __('aod_total') ?></b> <span style="font-size: 1.5rem; font-weight: bold;">฿<?= number_format($order['total_price'], 0) ?></span></p>
                
                <?php if ($order['payment_slip']): ?>
                    <div style="margin-top: 15px;">
                        <p><b><?= __('aod_slip') ?></b></p>
                        <a href="../<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank">
                            <img src="../<?= htmlspecialchars($order['payment_slip']) ?>" style="max-width: 200px; border-radius: 4px; border: 1px solid #ddd;">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <h3><?= __('aod_items') ?></h3>
        <table class="table-items">
            <thead>
                <tr>
                    <th><?= __('aod_th_product') ?></th>
                    <th><?= __('aod_th_size') ?></th>
                    <th><?= __('aod_th_price') ?></th>
                    <th><?= __('aod_th_qty') ?></th>
                    <th><?= __('aod_th_total') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td>฿<?= number_format($item['price'], 0) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>฿<?= number_format($item['subtotal'], 0) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
