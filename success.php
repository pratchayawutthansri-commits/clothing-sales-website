<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id === 0) {
    redirect('index.php');
}

// Fetch Order Details for confirmation (Optional: could just show ID)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container" style="padding: 100px 0; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="font-size: 5rem; color: #4CAF50; margin-bottom: 20px;">✓</div>
        <h1 style="font-size: 2.5rem; margin-bottom: 15px;">ขอบคุณสำหรับการสั่งซื้อ!</h1>
        <p style="font-size: 1.2rem; color: #666; margin-bottom: 20px;">
            หมายเลขคำสั่งซื้อของคุณคือ: <strong>#<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></strong>
        </p>
        
        <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #c8e6c9; color: #2e7d32;">
            <p style="margin: 0; font-weight: 500;">
                📧 เลขพัสดุจะถูกส่งไปยังอีเมล <strong><?= htmlspecialchars($order['email']) ?></strong> เมื่อทางร้านจัดส่งสินค้าแล้ว
            </p>
        </div>
        
        <div style="background: #f9f9f9; padding: 30px; text-align: left; border-radius: 8px;">
            <h3>รายละเอียดการจัดส่ง</h3>
            <p style="margin-top: 10px;"><strong>คุณ:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p style="margin-top: 15px; font-size: 1.1rem;">
                <strong>ยอดสุทธิ:</strong> <?= formatPrice($order['total_price']) ?> 
                (โอนเงิน)
            </p>
        </div>

        <div style="margin-top: 40px;">
            <a href="index.php" class="btn">กลับไปเลือกซื้อสินค้าต่อ</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
