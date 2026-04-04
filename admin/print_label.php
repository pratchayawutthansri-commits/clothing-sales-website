<?php
require_once 'includes/config.php';
checkAdminAuth();
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die("Order not found");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Shipping Label #<?= $order['id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&family=Libre+Barcode+39+Text&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            margin: 0; padding: 20px; 
            background: #e5e7eb; 
            display: flex; justify-content: center;
        }
        * { box-sizing: border-box; }
        
        .label-container { 
            width: 100mm; height: 150mm; 
            background: white; 
            border: 2px solid #000;
            padding: 15px; 
            display: flex; flex-direction: column; 
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .sender-box { 
            font-size: 11px; line-height: 1.4; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 10px;
            display: flex; justify-content: space-between;
        }
        
        .payment-badge {
            font-size: 20px; font-weight: 700; border: 3px solid #000; padding: 5px 10px; 
            text-align: center; border-radius: 4px; border-style: dashed;
            transform: rotate(-5deg); margin-top: 5px;
        }
        
        .receiver-box { font-size: 14px; flex-grow: 1; display: flex; flex-direction: column; }
        .receiver-title { font-weight: 700; font-size: 16px; margin-bottom: 5px; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px; align-self: flex-start;}
        .receiver-name { font-size: 26px; font-weight: 700; margin: 10px 0 5px; line-height: 1.2; }
        .receiver-address { font-size: 18px; line-height: 1.5; margin: 10px 0; font-weight: 500;}
        .receiver-phone { font-size: 20px; font-weight: 700; margin-top: 10px; }
        
        .footer-box { 
            border-top: 2px solid #000; padding-top: 10px; 
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        
        .barcode {
            font-family: 'Libre Barcode 39 Text', cursive;
            font-size: 52px; line-height: 1; margin-bottom: 5px;
        }
        
        .order-id { font-size: 14px; font-weight: 600; font-family: monospace; }
        
        @media print {
            body { background: white; padding: 0; }
            .label-container { border: 1px solid #000; box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<?php
// Fetch sender info 
$stmtShopName = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'shop_name'");
$shopName = $stmtShopName->fetchColumn() ?: 'XIVEX STORE';
$stmtShopAddr = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'shop_address'");
$shopAddress = $stmtShopAddr->fetchColumn() ?: '999/9 Siam Paragon, Rama 1 Road, Pathum Wan, Bangkok 10330';
$stmtShopPhone = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'shop_phone'");
$shopPhone = $stmtShopPhone->fetchColumn() ?: '02-999-9999';

// Fake barcode representation
$barcodeRef = "*" . str_pad($order['id'], 6, '0', STR_PAD_LEFT) . "*";
?>

<div class="label-container">
    <div class="sender-box">
        <div>
            <strong>ผู้ส่ง (SENDER):</strong><br>
            <strong><?= htmlspecialchars($shopName) ?></strong><br>
            <?= nl2br(htmlspecialchars($shopAddress)) ?><br>
            TEL: <?= htmlspecialchars($shopPhone) ?>
        </div>
        <div>
            <?php if ($order['payment_method'] == 'COD'): ?>
                <div class="payment-badge" style="background:#000; color:#fff; border-color:#000;">COD ฿<?= number_format($order['total_price']) ?></div>
            <?php else: ?>
                <div class="payment-badge">PAID</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="receiver-box">
        <span class="receiver-title">ผู้รับ (RECEIVER):</span>
        <div class="receiver-name">คุณ <?= htmlspecialchars($order['customer_name']) ?></div>
        <div class="receiver-address"><?= htmlspecialchars($order['address']) ?></div>
        <div class="receiver-phone">TEL: <?= htmlspecialchars($order['phone']) ?></div>
        
        <div style="margin-top:auto; font-size: 12px; color: #555;">
            หมายเหตุ: <?= htmlspecialchars($order['tracking_number'] ?: 'ไม่มีหมายเลขแทร็คกิ้ง') ?>
        </div>
    </div>

    <div class="footer-box">
        <div class="barcode"><?= $barcodeRef ?></div>
        <div class="order-id">ORDER ID: #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
        <div style="font-size: 10px; margin-top:5px; font-weight: bold; letter-spacing: 1px;">XIVEX PREMIUM STREETWEAR</div>
    </div>
</div>

</body>
</html>
