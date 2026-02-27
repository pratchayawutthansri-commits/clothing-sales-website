<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

// Fetch Orders with Filter
$status = $_GET['status'] ?? '';
$sql = "SELECT * FROM orders";
$params = [];

if ($status && in_array($status, ['pending', 'paid', 'shipped', 'completed', 'cancelled'])) {
    $sql .= " WHERE status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY order_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Fetch Counts for Cards
$stmtPending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$pendingOrders = $stmtPending->fetchColumn();

$stmtToShip = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'");
$toShipCount = $stmtToShip->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Page-specific: Orders */
        th { text-transform: uppercase; font-size: 0.85rem; }
        th, td { padding: 18px; }
        tr:hover { background: #fafafa; }
        
        .btn-view { padding: 6px 15px; background: #000; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: 0.3s; }
        .btn-view:hover { background: #444; }

        /* ─── Modern Stat Cards ─── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 35px;
        }
        .ostat-card {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 28px 32px;
            border-radius: 20px;
            text-decoration: none;
            color: #fff;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 120px;
        }
        .ostat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        .ostat-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            transition: transform 0.5s;
        }
        .ostat-card:hover::before {
            transform: scale(1.3);
        }
        .ostat-card::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -5%;
            width: 140px;
            height: 140px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .ostat-card.card-pending {
            background: linear-gradient(135deg, #f97316, #fb923c, #fdba74);
        }
        .ostat-card.card-toship {
            background: linear-gradient(135deg, #3b82f6, #60a5fa, #93c5fd);
        }

        .ostat-info {
            position: relative;
            z-index: 1;
        }
        .ostat-info h3 {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.85;
        }
        .ostat-info .ostat-number {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.1;
            margin-top: 6px;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .ostat-info .ostat-sub {
            font-size: 0.78rem;
            opacity: 0.7;
            margin-top: 4px;
        }

        .ostat-icon {
            position: relative;
            z-index: 1;
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s;
        }
        .ostat-card:hover .ostat-icon {
            background: rgba(255,255,255,0.3);
            transform: rotate(5deg) scale(1.1);
        }
        .ostat-icon svg {
            width: 28px;
            height: 28px;
            color: #fff;
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="header">
        <h1><?= __('ao_title') ?></h1>
    </div>
    
    <div class="stats-grid">
        <a href="orders.php?status=pending" class="ostat-card card-pending">
            <div class="ostat-info">
                <h3><?= __('ao_pending') ?></h3>
                <div class="ostat-number"><?= $pendingOrders ?></div>
                <div class="ostat-sub"><?= $_SESSION['lang'] === 'th' ? 'คำสั่งซื้อรอตรวจสอบ' : 'Orders awaiting review' ?></div>
            </div>
            <div class="ostat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </a>
        <a href="orders.php?status=paid" class="ostat-card card-toship">
            <div class="ostat-info">
                <h3><?= __('ao_to_ship') ?></h3>
                <div class="ostat-number"><?= $toShipCount ?></div>
                <div class="ostat-sub"><?= $_SESSION['lang'] === 'th' ? 'ชำระแล้ว พร้อมจัดส่ง' : 'Paid & ready to ship' ?></div>
            </div>
            <div class="ostat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            </div>
        </a>
    </div>
    
    <div style="margin-bottom: 20px;">
        <a href="orders.php" class="btn" style="background: #666;"><?= __('ao_filter_all') ?></a>
        <a href="orders.php?status=pending" class="btn" style="background: #fd7e14;"><?= __('ao_filter_pending') ?></a>
        <a href="orders.php?status=paid" class="btn" style="background: #28a745;"><?= __('ao_filter_paid') ?></a>
        <a href="orders.php?status=shipped" class="btn" style="background: #007bff;"><?= __('ao_filter_shipped') ?></a>
        <a href="orders.php?status=completed" class="btn" style="background: #198754;"><?= __('ao_filter_completed') ?></a>
        <a href="orders.php?status=cancelled" class="btn" style="background: #dc3545;"><?= __('ao_filter_cancelled') ?></a>
    </div>

    <table>
        <thead>
            <tr>
                <th><?= __('ao_th_order_id') ?></th>
                <th><?= __('ao_th_date') ?></th>
                <th><?= __('ao_th_customer') ?></th>
                <th><?= __('ao_th_total') ?></th>
                <th><?= __('ao_th_payment') ?></th>
                <th><?= __('ao_th_status') ?></th>
                <th><?= __('ao_th_action') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                    <td>
                        <b><?= htmlspecialchars($order['customer_name']) ?></b><br>
                        <span style="font-size:0.8rem; color:#888;"><?= htmlspecialchars($order['phone']) ?></span>
                    </td>
                    <td>฿<?= number_format($order['total_price'], 0) ?></td>
                    <td><?= htmlspecialchars(strtoupper($order['payment_method'])) ?></td>
                    <td>
                        <span class="badge status-<?= htmlspecialchars($order['status']) ?>">
                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <a href="order_details.php?id=<?= $order['id'] ?>" class="btn-view" style="margin-right: 5px;"><?= __('ao_btn_view') ?></a>
                        <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn-view" style="background: #6c757d;"><?= __('ao_btn_print') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 50px;">
                        <?= __('ao_no_orders') ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
