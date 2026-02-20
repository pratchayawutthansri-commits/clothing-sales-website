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
    <title>รายการคำสั่งซื้อ - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; margin: 0; background: #f9f9f9; display: flex; }
        .sidebar { width: 250px; background: #1a1a1a; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { margin-top: 0; margin-bottom: 30px; letter-spacing: 1px;}
        .sidebar a { display: block; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #333; transition: 0.3s; }
        .sidebar a:hover { color: white; background: #333; padding-left: 20px; }
        .sidebar a.active { color: white; font-weight: bold; background: #333; border-left: 4px solid #fff; }
        
        .content { flex: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th, td { padding: 18px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f4f4f4; text-transform: uppercase; font-size: 0.85rem; color: #666; font-weight: 500; }
        tr:hover { background: #fafafa; }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .btn-view { padding: 6px 15px; background: #000; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: 0.3s; }
        .btn-view:hover { background: #444; }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            margin-right: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: none;
            font-size: 0.9rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            opacity: 0.9;
        }
        
        /* Stat Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; transition: 0.3s; cursor: pointer; text-decoration: none; color: inherit; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .stat-info h3 { margin: 0; font-size: 0.9rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-info p { margin: 5px 0 0; font-size: 2rem; font-weight: bold; color: #333; }
        .stat-icon { font-size: 2rem; opacity: 0.2; }
        
        .card-orange { border-left: 5px solid #fd7e14; }
        .card-blue { border-left: 5px solid #007bff; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <div class="header">
        <h1>รายการคำสั่งซื้อ</h1>
    </div>
    
    <div class="stats-grid">
        <a href="orders.php?status=pending" class="stat-card card-orange">
            <div class="stat-info">
                <h3>รอตรวจสอบ (Pending)</h3>
                <p><?= $pendingOrders ?></p>
            </div>
            <div class="stat-icon">🕒</div>
        </a>
        <a href="orders.php?status=paid" class="stat-card card-blue">
            <div class="stat-info">
                <h3>ต้องจัดส่ง (To Ship)</h3>
                <p><?= $toShipCount ?></p>
            </div>
            <div class="stat-icon">📦</div>
        </a>
    </div>
    
    <div style="margin-bottom: 20px;">
        <a href="orders.php" class="btn" style="background: #666;">ทั้งหมด</a>
        <a href="orders.php?status=pending" class="btn" style="background: #fd7e14;">รอชำระ/ตรวจ</a>
        <a href="orders.php?status=paid" class="btn" style="background: #28a745;">รอส่ง</a>
        <a href="orders.php?status=shipped" class="btn" style="background: #007bff;">ส่งแล้ว</a>
        <a href="orders.php?status=completed" class="btn" style="background: #198754;">สำเร็จ</a>
        <a href="orders.php?status=cancelled" class="btn" style="background: #dc3545;">ยกเลิก</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>วันที่</th>
                <th>ลูกค้า</th>
                <th>ยอดรวม</th>
                <th>วิธีชำระ</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
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
                        <a href="order_details.php?id=<?= $order['id'] ?>" class="btn-view" style="margin-right: 5px;">ดูรายละเอียด</a>
                        <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn-view" style="background: #6c757d;">พิมพ์ใบปะหน้า</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 50px;">
                        ไม่พบข้อมูลคำสั่งซื้อ
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
