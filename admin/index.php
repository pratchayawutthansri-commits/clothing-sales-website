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

// 6. Sales by Category (for Doughnut chart)
$stmtCat = $pdo->query("
    SELECT p.category, SUM(oi.subtotal) AS total
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('paid', 'shipped', 'completed')
    GROUP BY p.category
    ORDER BY total DESC
");
$catData = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
$catLabels = array_column($catData, 'category');
$catValues = array_map('floatval', array_column($catData, 'total'));

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xivex Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ─── Dashboard Premium ─── */
        .dash-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: #0a0a0a;
            letter-spacing: -0.5px;
            margin-bottom: 30px;
        }

        /* Stat Cards Grid */
        .dash-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 35px;
        }
        @media (max-width: 768px) {
            .dash-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* Individual Card */
        .dcard {
            position: relative;
            padding: 24px;
            border-radius: 18px;
            color: #fff;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dcard:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.18);
        }

        /* Decorative BG shapes */
        .dcard::before {
            content: '';
            position: absolute;
            top: -25%;
            right: -15%;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transition: transform 0.5s;
        }
        .dcard:hover::before { transform: scale(1.4); }
        .dcard::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 90px;
            height: 90px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        /* Card Variants */
        .dcard.c-revenue   { background: linear-gradient(135deg, #059669, #10b981, #34d399); }
        .dcard.c-monthly   { background: linear-gradient(135deg, #2563eb, #3b82f6, #60a5fa); }
        .dcard.c-pending   { background: linear-gradient(135deg, #d97706, #f59e0b, #fbbf24); }
        .dcard.c-toship    { background: linear-gradient(135deg, #7c3aed, #8b5cf6, #a78bfa); }
        .dcard.c-lowstock  { background: linear-gradient(135deg, #dc2626, #ef4444, #f87171); }

        /* Card Top Row */
        .dcard-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 1;
        }
        .dcard-label {
            font-size: 0.78rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            opacity: 0.85;
        }
        .dcard-icon {
            width: 42px;
            height: 42px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s;
        }
        .dcard:hover .dcard-icon {
            background: rgba(255,255,255,0.3);
            transform: rotate(8deg) scale(1.1);
        }
        .dcard-icon svg {
            width: 20px;
            height: 20px;
            color: #fff;
        }

        /* Card Bottom */
        .dcard-bottom {
            position: relative;
            z-index: 1;
        }
        .dcard-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            text-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .dcard-sub {
            font-size: 0.72rem;
            opacity: 0.7;
            margin-top: 4px;
        }

        /* ─── Chart Section ─── */
        .chart-box {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 40px;
        }
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .chart-header h2 {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }
        .chart-header .chart-badge {
            font-size: 0.78rem;
            background: #f0fdf4;
            color: #16a34a;
            padding: 5px 14px;
            border-radius: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <h1 class="dash-title"><?= __('dash_main') ?></h1>
    
    <div class="dash-grid">
        <!-- Total Revenue -->
        <div class="dcard c-revenue">
            <div class="dcard-top">
                <div class="dcard-label"><?= __('dash_total_rev') ?></div>
                <div class="dcard-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
            <div class="dcard-bottom">
                <div class="dcard-value">฿<?= number_format($totalRevenue, 0) ?></div>
                <div class="dcard-sub"><?= __('dash_all_time') ?></div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="dcard c-monthly">
            <div class="dcard-top">
                <div class="dcard-label"><?= __('dash_monthly_rev') ?></div>
                <div class="dcard-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
            </div>
            <div class="dcard-bottom">
                <div class="dcard-value">฿<?= number_format($monthlySales, 0) ?></div>
                <div class="dcard-sub"><?= date('F Y') ?></div>
            </div>
        </div>

        <!-- Pending -->
        <div class="dcard c-pending">
            <div class="dcard-top">
                <div class="dcard-label"><?= __('dash_pending') ?></div>
                <div class="dcard-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <div class="dcard-bottom">
                <div class="dcard-value"><?= $pendingOrders ?></div>
                <div class="dcard-sub"><?= __('dash_orders_verify') ?></div>
            </div>
        </div>

        <!-- To Ship -->
        <div class="dcard c-toship">
            <div class="dcard-top">
                <div class="dcard-label"><?= __('dash_to_ship') ?></div>
                <div class="dcard-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                </div>
            </div>
            <div class="dcard-bottom">
                <div class="dcard-value"><?= $toShipCount ?></div>
                <div class="dcard-sub"><?= __('dash_paid_waiting') ?></div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="dcard c-lowstock">
            <div class="dcard-top">
                <div class="dcard-label"><?= __('dash_low_stock') ?></div>
                <div class="dcard-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
            </div>
            <div class="dcard-bottom">
                <div class="dcard-value"><?= $lowStockCount ?></div>
                <div class="dcard-sub"><?= __('dash_less_than_10') ?></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 40px;">
        
        <!-- Bar Chart: Monthly Sales -->
        <div class="chart-box">
            <div class="chart-header">
                <h2><?= __('dash_chart_title') ?></h2>
                <span class="chart-badge"><?= $_SESSION['lang'] === 'th' ? '6 เดือนล่าสุด' : 'Last 6 months' ?></span>
            </div>
            <div style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Sales by Category -->
        <div class="chart-box">
            <div class="chart-header">
                <h2><?= $_SESSION['lang'] === 'th' ? 'ยอดขายตามหมวดหมู่' : 'Sales by Category' ?></h2>
            </div>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                <?php if (empty($catData)): ?>
                    <p style="color: #94a3b8; font-size: 0.9rem;"><?= $_SESSION['lang'] === 'th' ? 'ยังไม่มีข้อมูลยอดขาย' : 'No sales data yet' ?></p>
                <?php else: ?>
                    <canvas id="catChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // ─── Bar Chart: Monthly Revenue ───
    const ctx1 = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: '<?= $_SESSION['lang'] === 'th' ? 'รายได้ (บาท)' : 'Revenue (THB)' ?>',
                data: <?= json_encode($sales) ?>,
                backgroundColor: '#1e293b',
                hoverBackgroundColor: '#334155',
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        font: { family: "'Kanit', sans-serif", size: 12 },
                        color: '#94a3b8',
                        callback: function(value) {
                            if (value >= 1000) return '฿' + (value / 1000).toFixed(0) + 'K';
                            return '฿' + value;
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: "'Kanit', sans-serif", size: 12 }, color: '#94a3b8' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: "'Kanit', sans-serif", size: 13 },
                    bodyFont: { family: "'Kanit', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) { return '฿' + ctx.parsed.y.toLocaleString(); }
                    }
                }
            }
        }
    });

    // ─── Doughnut Chart: Sales by Category ───
    <?php if (!empty($catData)): ?>
    const ctx2 = document.getElementById('catChart').getContext('2d');
    const catColors = [
        '#059669', '#2563eb', '#d97706', '#7c3aed', '#dc2626',
        '#0891b2', '#ca8a04', '#be185d', '#4f46e5', '#16a34a'
    ];
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($catLabels) ?>,
            datasets: [{
                data: <?= json_encode($catValues) ?>,
                backgroundColor: catColors.slice(0, <?= count($catLabels) ?>),
                hoverOffset: 8,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        font: { family: "'Kanit', sans-serif", size: 12 },
                        color: '#334155',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: "'Kanit', sans-serif", size: 13 },
                    bodyFont: { family: "'Kanit', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.parsed / total) * 100).toFixed(1);
                            return ctx.label + ': ฿' + ctx.parsed.toLocaleString() + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    </script>
    
</div>

</body>
</html>
