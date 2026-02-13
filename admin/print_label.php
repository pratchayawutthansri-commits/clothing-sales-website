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
    <style>
        body { font-family: 'Sarabun', sans-serif; padding: 20px; }
        .label-box { 
            width: 100mm; height: 150mm; border: 2px solid black; padding: 20px; box-sizing: border-box; margin: 0 auto; 
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .sender { font-size: 12px; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; }
        .receiver { font-size: 16px; font-weight: bold; }
        .receiver p { font-size: 18px; line-height: 1.4; }
        .footer { border-top: 2px dashed black; padding-top: 10px; font-size: 12px; }
        @media print {
            body { padding: 0; }
            .label-box { border: none; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="label-box">
    <div class="sender">
        <strong>ผู้ส่ง (Sender):</strong><br>
        XIVEX STORE (Head Office)<br>
        999/9 Siam Paragon, Rama 1 Road<br>
        Pathum Wan, Bangkok 10330<br>
        Tel: 02-999-9999
    </div>

    <div class="receiver">
        <strong>ผู้รับ (Receiver):</strong><br>
        คุณ <?= htmlspecialchars($order['customer_name']) ?><br>
        <p style="white-space: pre-wrap;"><?= htmlspecialchars($order['address']) ?></p>
        <br>
        Tel: <?= htmlspecialchars($order['phone']) ?>
    </div>

    <div class="footer">
        <strong>Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong><br>
        <?= $order['payment_method'] == 'COD' ? '<h2 style="margin:5px 0;">เก็บเงินปลายทาง (COD) ฿'.number_format($order['total_price']).'</h2>' : 'ไม่ต้องเก็บเงิน (Paid)' ?>
    </div>
</div>

</body>
</html>
