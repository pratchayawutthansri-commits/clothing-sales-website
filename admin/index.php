<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

// --- KPI Stats Logic ---

// 1. Total Revenue (Paid, Shipped, Completed)
$stmtRevenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status IN ('paid', 'shipped', 'completed')");
$totalRevenue = $stmtRevenue->fetchColumn() ?: 0;

// 2. Pending Orders (Waiting for Admin Check)
$stmtPending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$pendingOrders = $stmtPending->fetchColumn();

// 3. To Ship (Paid but not shipped)
$stmtToShip = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'");
$toShipCount = $stmtToShip->fetchColumn();

// 4. Low Stock Items (Stock < 10)
$stmtLowStock = $pdo->query("SELECT COUNT(*) FROM product_variants WHERE stock < 10");
$lowStockCount = $stmtLowStock->fetchColumn();

// 4. Monthly Sales (Current Month)
$currentMonth = date('Y-m');
$stmtMonthly = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE status IN ('paid', 'shipped', 'completed') AND DATE_FORMAT(order_date, '%Y-%m') = ?");
$stmtMonthly->execute([$currentMonth]);
$monthlySales = $stmtMonthly->fetchColumn() ?: 0;

// 5. Chart Data (Last 6 Months) - Single query
$months = [];
$sales = [];
$startMonth = date('Y-m', strtotime("-5 months"));

$stmtChart = $pdo->prepare("
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(total_price) AS total
    FROM orders
    WHERE status IN ('paid', 'shipped', 'completed')
      AND DATE_FORMAT(order_date, '%Y-%m') >= ?
    GROUP BY month
    ORDER BY month ASC
");
$stmtChart->execute([$startMonth]);
$chartData = [];
while ($row = $stmtChart->fetch()) {
    $chartData[$row['month']] = (float)$row['total'];
}

// Fill in all 6 months (including months with zero sales)
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $months[] = $label;
    $sales[] = $chartData[$m] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xivex Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Page-specific: Dashboard */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-title { color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .stat-value { font-size: 2rem; font-weight: bold; color: #333; }
        .stat-desc { font-size: 0.8rem; color: #888; margin-top: 5px; }
        
        .card-green { border-bottom: 4px solid #28a745; }
        .card-blue { border-bottom: 4px solid #007bff; }
        .card-orange { border-bottom: 4px solid #fd7e14; }
        .card-red { border-bottom: 4px solid #dc3545; }
        
        .recent-orders { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .recent-orders h2 { margin-top: 0; margin-bottom: 20px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <h1>แผงควบคุมหลัก</h1>
    
    <div class="stats-grid">
        <div class="stat-card card-green">
            <div class="stat-title">ยอดขายทั้งหมด</div>
            <div class="stat-value">฿<?= number_format($totalRevenue, 0) ?></div>
            <div class="stat-desc">รายได้รวมตั้งแต่เริ่ม</div>
        </div>
        <div class="stat-card card-blue">
            <div class="stat-title">ยอดขายเดือนนี้</div>
            <div class="stat-value">฿<?= number_format($monthlySales, 0) ?></div>
            <div class="stat-desc">เดือน <?= date('F Y') ?></div>
        </div>
        <div class="stat-card card-orange">
            <div class="stat-title">รอตรวจสอบ</div>
            <div class="stat-value"><?= $pendingOrders ?></div>
            <div class="stat-desc">ออเดอร์รอดำเนินการ</div>
        </div>
        <div class="stat-card" style="border-bottom: 4px solid #6f42c1;">
            <div class="stat-title">รอจัดส่ง</div>
            <div class="stat-value"><?= $toShipCount ?></div>
            <div class="stat-desc">ชำระเงินแล้ว รอส่ง</div>
        </div>
        <div class="stat-card card-red">
            <div class="stat-title">สินค้าใกล้หมด</div>
            <div class="stat-value"><?= $lowStockCount ?></div>
            <div class="stat-desc">สต็อกน้อยกว่า 10 ชิ้น</div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px;">
        <h2 style="margin-top: 0; margin-bottom: 20px;">สรุปยอดขายรายเดือน (Monthly Revenue)</h2>
        <canvas id="salesChart" style="width: 100%; height: 300px;"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: <?= json_encode($sales) ?>,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                borderColor: 'rgba(0, 0, 0, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' }
                },
                x: {
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    </script>
    
</div>

</body>
</html>
