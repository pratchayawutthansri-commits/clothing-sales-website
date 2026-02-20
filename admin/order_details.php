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
    die("ไม่พบคำสั่งซื้อ");
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
    <title>รายละเอียดคำสั่งซื้อ #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?> - Xivex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; margin: 0; background: #f9f9f9; display: flex; }
        .sidebar { width: 250px; background: #1a1a1a; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { margin-top: 0; margin-bottom: 30px; letter-spacing: 1px;}
        .sidebar a { display: block; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #333; transition: 0.3s; }
        .sidebar a:hover { color: white; background: #333; padding-left: 20px; }
        .content { flex: 1; padding: 40px; }
        
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .order-header h1 { margin: 0; font-size: 1.8rem; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .info-group h3 { margin-top: 0; color: #555; font-size: 1rem; text-transform: uppercase; }
        .info-group p { margin: 5px 0; color: #333; }
        
        .table-items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-items th, .table-items td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        .table-items th { background: #f8f8f8; }
        
        .status-form { display: flex; gap: 10px; align-items: center; }
        
        /* Modern Input Styling */
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
        
        .btn {
            display: inline-block;
            padding: 10px 25px;
            color: white;
            background: #000; /* Default Black */
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-right: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            font-size: 0.95rem;
            cursor: pointer;
            font-family: 'Kanit', sans-serif;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            background: #333;
        }
        
        /* Specific Button Colors */
        .btn-secondary {
            background: #6c757d !important;
        }
        .btn-secondary:hover {
            background: #5a6268 !important;
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <a href="orders.php" class="btn-back">← กลับไปหน้ารายการ</a>

    <div class="box">
        <div class="order-header">
            <h1>คำสั่งซื้อ #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h1>
        </div>
        <div class="actions">
            <h2>จัดการคำสั่งซื้อ</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                
                <div class="form-group">
                    <label>เลขพัสดุ (Tracking Number)</label>
                    <input type="text" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>" placeholder="เช่น TH12345678" style="width:100%; padding:10px; border:1px solid #ddd; margin-bottom:15px;">
                </div>

                <div class="form-group">
                    <label>สถานะคำสั่งซื้อ</label>
                    <select name="status" style="width:100%; padding: 10px; margin-bottom: 20px;">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>รอชำระเงิน / ตรวจสอบ (Pending)</option>
                        <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>>ชำระเงินแล้ว / รอส่ง (Paid)</option>
                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>จัดส่งแล้ว (Shipped)</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>ลูกค้าได้รับของแล้ว (Completed)</option>
                    </select>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <button type="submit" name="update_order" class="btn">อัปเดตข้อมูล</button>
                        <a href="print_label.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-secondary">พิมพ์ใบปะหน้า</a>
                    </div>
                </div>
            </form>

            <?php if ($order['status'] != 'cancelled' && $order['status'] != 'completed'): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <h3 style="color: #dc3545; font-size: 1rem; margin-top: 0;">Danger Zone</h3>
                <form method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกคำสั่งซื้อนี้?');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?? '' ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <input type="hidden" name="tracking_number" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>">
                    <button type="submit" name="update_order" class="btn" style="background: #dc3545;">ยกเลิกคำสั่งซื้อ (Cancel Order)</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-group">
                <h3>ข้อมูลลูกค้า</h3>
                <p><b>ชื่อ:</b> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><b>อีเมล:</b> <?= htmlspecialchars($order['email']) ?></p>
                <p><b>เบอร์โทร:</b> <?= htmlspecialchars($order['phone']) ?></p>
                <p><b>ที่อยู่จัดส่ง:</b> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
            </div>
            <div class="info-group">
                <h3>ข้อมูลการชำระเงิน</h3>
                <p><b>วันที่สั่งซื้อ:</b> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
                <p><b>วิธีชำระเงิน:</b> <?= strtoupper($order['payment_method']) ?></p>
                <p><b>ยอดรวมทั้งสิ้น:</b> <span style="font-size: 1.5rem; font-weight: bold;">฿<?= number_format($order['total_price'], 0) ?></span></p>
                
                <?php if ($order['payment_slip']): ?>
                    <div style="margin-top: 15px;">
                        <p><b>หลักฐานการโอนเงิน:</b></p>
                        <a href="../<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank">
                            <img src="../<?= htmlspecialchars($order['payment_slip']) ?>" style="max-width: 200px; border-radius: 4px; border: 1px solid #ddd;">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <h3>รายการสินค้า</h3>
        <table class="table-items">
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>ไซส์</th>
                    <th>ราคาต่อชิ้น</th>
                    <th>จำนวน</th>
                    <th>รวม</th>
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
