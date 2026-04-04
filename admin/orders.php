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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Modern SaaS Dashboard Layout */
        body { background: #f3f4f6; font-family: 'Kanit', sans-serif; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 1.8rem; margin: 0; color: #111827; }

        /* ─── Modern Stat Cards ─── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 30px;
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
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
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
            background: linear-gradient(135deg, #111827, #374151, #4b5563);
        }
        .ostat-card.card-toship {
            background: linear-gradient(135deg, #2563eb, #3b82f6, #60a5fa);
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
            font-family: 'Kanit', sans-serif;
        }
        .ostat-info .ostat-number {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.1;
            margin-top: 6px;
            font-family: 'Outfit', sans-serif;
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
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s;
        }
        .ostat-card:hover .ostat-icon {
            background: rgba(255,255,255,0.2);
            transform: rotate(5deg) scale(1.1);
        }
        .ostat-icon svg { width: 28px; height: 28px; color: #fff; }

        /* Filter Tabs */
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
        .filter-tab { padding: 8px 16px; border-radius: 20px; font-weight: 500; font-size: 0.9rem; text-decoration: none; color: #6b7280; background: #fff; border: 1px solid #e5e7eb; transition: 0.2s; }
        .filter-tab:hover { background: #f9fafb; color: #111827; }
        .filter-tab.active-all, .filter-tab.active-pending, .filter-tab.active-paid, .filter-tab.active-shipped, .filter-tab.active-completed, .filter-tab.active-cancelled {
            border-color: transparent; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .filter-tab.active-all { background: #111827; }
        .filter-tab.active-pending { background: #ea580c; }
        .filter-tab.active-paid { background: #2563eb; }
        .filter-tab.active-shipped { background: #0ea5e9; }
        .filter-tab.active-completed { background: #16a34a; }
        .filter-tab.active-cancelled { background: #ef4444; }

        /* Modern Table */
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

        .order-id { color: #111827; font-family: 'Outfit', monospace; font-weight: 600; font-size: 0.95rem; }
        .primary-text { font-weight: 600; color: #111827; margin-bottom: 2px; }
        .secondary-text { font-size: 0.85rem; color: #6b7280; font-family: 'Outfit', sans-serif;}
        
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .status-pending { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .status-paid { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .status-shipped { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }
        .status-completed { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .status-cancelled { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .action-flex { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-action { padding: 8px 12px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; border: none; }
        
        .btn-view { background: #111827; color: white; }
        .btn-view:hover { background: #374151; }
        
        .btn-print { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .btn-print:hover { background: #e5e7eb; color: #111827; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="page-header">
        <h1><?= __('ao_title') ?></h1>
    </div>
    
    <div class="stats-grid">
        <a href="orders.php?status=pending" class="ostat-card card-pending">
            <div class="ostat-info">
                <h3><?= __('ao_pending') ?></h3>
                <div class="ostat-number"><?= $pendingOrders ?></div>
                <div class="ostat-sub"><?= __('ao_sub_pending') ?></div>
            </div>
            <div class="ostat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </a>
        <a href="orders.php?status=paid" class="ostat-card card-toship">
            <div class="ostat-info">
                <h3><?= __('ao_to_ship') ?></h3>
                <div class="ostat-number"><?= $toShipCount ?></div>
                <div class="ostat-sub"><?= __('ao_sub_toship') ?></div>
            </div>
            <div class="ostat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            </div>
        </a>
    </div>
    
    <div class="filter-tabs">
        <a href="orders.php" class="filter-tab <?= empty($status) ? 'active-all' : '' ?>"><?= __('ao_filter_all') ?></a>
        <a href="orders.php?status=pending" class="filter-tab <?= $status === 'pending' ? 'active-pending' : '' ?>"><?= __('ao_filter_pending') ?></a>
        <a href="orders.php?status=paid" class="filter-tab <?= $status === 'paid' ? 'active-paid' : '' ?>"><?= __('ao_filter_paid') ?></a>
        <a href="orders.php?status=shipped" class="filter-tab <?= $status === 'shipped' ? 'active-shipped' : '' ?>"><?= __('ao_filter_shipped') ?></a>
        <a href="orders.php?status=completed" class="filter-tab <?= $status === 'completed' ? 'active-completed' : '' ?>"><?= __('ao_filter_completed') ?></a>
        <a href="orders.php?status=cancelled" class="filter-tab <?= $status === 'cancelled' ? 'active-cancelled' : '' ?>"><?= __('ao_filter_cancelled') ?></a>
    </div>

    <div class="modern-table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 100px;"><?= __('ao_th_order_id') ?></th>
                    <th><?= __('ao_th_date') ?></th>
                    <th><?= __('ao_th_customer') ?></th>
                    <th><?= __('ao_th_total') ?></th>
                    <th><?= __('ao_th_payment') ?></th>
                    <th><?= __('ao_th_status') ?></th>
                    <th style="text-align: right;"><?= __('ao_th_action') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><span class="order-id">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                        <td>
                            <div class="primary-text"><?= date('d/m/Y', strtotime($order['order_date'])) ?></div>
                            <div class="secondary-text"><?= date('H:i', strtotime($order['order_date'])) ?></div>
                        </td>
                        <td>
                            <div class="primary-text"><?= htmlspecialchars($order['customer_name']) ?></div>
                            <div class="secondary-text"><?= htmlspecialchars($order['phone']) ?></div>
                        </td>
                        <td class="primary-text">฿<?= number_format($order['total_price'], 0) ?></td>
                        <td><div class="secondary-text"><?= htmlspecialchars(strtoupper($order['payment_method'])) ?></div></td>
                        <td>
                            <span class="badge status-<?= htmlspecialchars($order['status']) ?>">
                                <?php
                                    $statusText = ucfirst(htmlspecialchars($order['status']));
                                    if ($order['status'] === 'pending') {
                                        echo '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> ';
                                    } elseif ($order['status'] === 'paid') {
                                        echo '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ';
                                    } elseif ($order['status'] === 'shipped') {
                                        echo '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg> ';
                                    }
                                    echo $statusText;
                                ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-flex">
                                <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn-action btn-print" title="<?= __('ao_btn_print') ?>">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </a>
                                <a href="order_details.php?id=<?= $order['id'] ?>" class="btn-action btn-view" title="<?= __('ao_btn_view') ?>">
                                    รายละเอียด
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #9ca3af; padding: 50px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:48px; height:48px; margin-bottom:15px; opacity:0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2v0"></path></svg><br>
                            <?= __('ao_no_orders') ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
