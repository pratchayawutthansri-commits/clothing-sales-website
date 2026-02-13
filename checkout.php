<?php
require_once 'includes/init.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

// Calculate Total
$total = 0;
$cartItems = [];

foreach ($_SESSION['cart'] as $key => $quantity) {
    $parts = explode('_', $key);
    $productId = $parts[0];
    $variantId = isset($parts[1]) ? $parts[1] : 0;
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    $stmtv = $pdo->prepare("SELECT * FROM product_variants WHERE id = ?");
    $stmtv->execute([$variantId]);
    $variant = $stmtv->fetch();
    
    if ($product && $variant) {
        $price = $variant['price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
        $cartItems[] = [
            'name' => $product['name'],
            'size' => $variant['size'],
            'price' => $price,
            'qty' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// Fetch Shop Settings
$settings = [];
$stmtSettings = $pdo->query("SELECT * FROM settings");
while ($row = $stmtSettings->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$shippingCost = (float)($settings['shipping_cost'] ?? 50);
$total += $shippingCost;

include 'includes/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <h1 style="text-align:center; margin-bottom: 40px; font-size: 2.5rem;">ชำระเงิน (Checkout)</h1>

    <div class="checkout-grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 60px;">
        
        <!-- Shipping Form -->
        <div class="shipping-form">
            <h3 style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">ที่อยู่จัดส่ง</h3>
            <form action="process_order.php" method="POST" id="checkout-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="name" required style="width:100%; padding:12px; border:1px solid #ccc;">
                </div>
                
                <div class="form-group">
                    <label>อีเมล</label>
                    <input type="email" name="email" required style="width:100%; padding:12px; border:1px solid #ccc;">
                </div>
                
                <div class="form-group">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="tel" name="phone" required style="width:100%; padding:12px; border:1px solid #ccc;">
                </div>
                
                <div class="form-group">
                    <label>ที่อยู่จัดส่ง</label>
                    <textarea name="address" rows="4" required style="width:100%; padding:12px; border:1px solid #ccc;"></textarea>
                </div>
                
                <h3 style="margin: 30px 0 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">วิธีการชำระเงิน</h3>
                <h3 style="margin: 30px 0 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">วิธีการชำระเงิน</h3>
                <div class="payment-methods">
                    <label style="display:block; margin-bottom:15px; cursor:pointer;">
                        <input type="radio" name="payment_method" value="BANK_TRANSFER" checked> 
                        <span style="font-weight:600; margin-left:10px;">โอนเงินผ่านธนาคาร (Bank Transfer)</span>
                    </label>
                </div>

                <div id="slip-upload" style="margin-top:20px; background:#f9f9f9; padding:20px; border-radius:8px; border: 1px solid #eee;">
                     <label style="display:block; margin-bottom:10px; font-weight:600;">โอนเงินมาที่ (Transfer To)</label>
                     <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 15px;">
                        <strong><?= htmlspecialchars($settings['bank_name']) ?></strong><br>
                        เลขบัญชี: <span style="font-size: 1.1em; color: #007bff;"><?= htmlspecialchars($settings['bank_account']) ?></span><br>
                        ชื่อบัญชี: <?= htmlspecialchars($settings['bank_owner']) ?>
                     </div>
                     
                     <label style="display:block; margin-bottom:10px; font-weight:600;">แนบสลิปการโอนเงิน (Payment Slip) <span style="color:red">*</span></label>
                     <input type="file" name="slip" accept="image/*" required>
                </div>

                <button type="submit" class="btn" style="width:100%; margin-top: 20px; font-size: 1.1rem; padding: 18px;">ยืนยันคำสั่งซื้อ</button>
            </form>

            <script>
            function toggleSlip(radio) {
                const slip = document.getElementById('slip-upload');
                if (radio.value === 'BANK_TRANSFER') {
                    slip.style.display = 'block';
                    slip.querySelector('input').required = true;
                } else {
                    slip.style.display = 'none';
                    slip.querySelector('input').required = false;
                }
            }
            </script>
        </div>

        <!-- Order Summary -->
        <div class="order-summary" style="background: #f9f9f9; padding: 30px; height: fit-content;">
            <h3 style="margin-bottom: 20px;">สรุปคำสั่งซื้อ</h3>
            
            <div class="summary-items" style="margin-bottom: 20px;">
                <?php foreach ($cartItems as $item): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.9rem;">
                    <span><?= htmlspecialchars($item['name']) ?> (<?= $item['size'] ?>) x <?= $item['qty'] ?></span>
                    <span><?= formatPrice($item['subtotal']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px; display:flex; justify-content:space-between; font-size: 0.9rem;">
                <span>ค่าจัดส่ง (Shipping)</span>
                <span><?= formatPrice($shippingCost) ?></span>
            </div>
            
            <div style="border-top: 2px solid #333; padding-top: 20px; margin-top: 10px; font-weight: 700; display:flex; justify-content:space-between; font-size: 1.2rem;">
                <span>ยอดรวมสุทธิ</span>
                <span><?= formatPrice($total) ?></span>
            </div>
            <p style="text-align:right; font-size:0.8rem; color:#666; margin-top:5px;">(รวมภาษีมูลค่าเพิ่มแล้ว)</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
