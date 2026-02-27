<?php
require_once 'includes/init.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

// Calculate Total
$total = 0;
$cartItems = [];

// Parse cart keys
$cartParsed = [];
foreach ($_SESSION['cart'] as $key => $quantity) {
    $parts = explode('_', $key);
    $productId = (int)$parts[0];
    $variantId = isset($parts[1]) ? (int)$parts[1] : 0;
    if ($productId > 0 && $variantId > 0) {
        $cartParsed[$key] = ['pid' => $productId, 'vid' => $variantId, 'qty' => $quantity];
    }
}

if (!empty($cartParsed)) {
    $variantIds = array_column($cartParsed, 'vid');
    $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
    
    $stmt = $pdo->prepare("
        SELECT p.name, v.id AS variant_id, v.size, v.price
        FROM products p
        JOIN product_variants v ON v.product_id = p.id
        WHERE v.id IN ($placeholders)
    ");
    $stmt->execute($variantIds);
    $results = $stmt->fetchAll();
    
    $resultMap = [];
    foreach ($results as $row) {
        $resultMap[$row['variant_id']] = $row;
    }
    
    foreach ($cartParsed as $key => $cartData) {
        if (isset($resultMap[$cartData['vid']])) {
            $row = $resultMap[$cartData['vid']];
            $subtotal = $row['price'] * $cartData['qty'];
            $total += $subtotal;
            $cartItems[] = [
                'name' => $row['name'],
                'size' => $row['size'],
                'price' => $row['price'],
                'qty' => $cartData['qty'],
                'subtotal' => $subtotal
            ];
        }
    }
}

// Fetch Shop Settings
$settings = [];
$stmtSettings = $pdo->query("SELECT * FROM settings");
while ($row = $stmtSettings->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$shippingCost = (float)($settings['shipping_cost'] ?? 50);

// Free shipping for logged-in members
if (isset($_SESSION['user_id'])) {
    $shippingCost = 0;
}

$total += $shippingCost;

include 'includes/header.php';
?>

<div class="container" style="padding: 60px 0;">
    <h1 style="text-align:center; margin-bottom: 40px; font-size: 2.5rem;"><?= __('chk_title') ?></h1>

    <div class="checkout-grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 60px;">
        
        <!-- Shipping Form -->
        <div class="shipping-form">
            <h3 style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;"><?= __('chk_shipping_details') ?></h3>
            <form action="process_order.php" method="POST" id="checkout-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="form-group">
                    <label><?= __('chk_full_name') ?></label>
                    <input type="text" name="name" required value="<?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : '' ?>" style="width:100%; padding:12px; border:1px solid #ccc;">
                </div>
                
                <div class="form-group">
                    <label><?= __('chk_email') ?></label>
                    <input type="email" name="email" required <?= isset($_SESSION['user_id']) ? 'readonly style="background: #f0f0f0; cursor: not-allowed; width:100%; padding:12px; border:1px solid #ccc;"' : 'style="width:100%; padding:12px; border:1px solid #ccc;"' ?> value="<?= isset($_SESSION['user_id']) ? htmlspecialchars(isset($_SESSION['email']) ? $_SESSION['email'] : '') : '' ?>">
                </div>
                
                <div class="form-group">
                    <label><?= __('chk_phone') ?></label>
                    <input type="tel" name="phone" required style="width:100%; padding:12px; border:1px solid #ccc;">
                </div>
                
                <div class="form-group">
                    <label><?= __('chk_address') ?></label>
                    <textarea name="address" rows="4" required style="width:100%; padding:12px; border:1px solid #ccc;"></textarea>
                </div>
                
                <h3 style="margin: 30px 0 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;"><?= __('chk_payment_method') ?></h3>
                <div class="payment-methods">
                    <label style="display:block; margin-bottom:15px; cursor:pointer;">
                        <input type="radio" name="payment_method" value="BANK_TRANSFER" checked> 
                        <span style="font-weight:600; margin-left:10px;"><?= __('chk_bank_transfer') ?></span>
                    </label>
                </div>

                <div id="slip-upload" style="margin-top:20px; background:#f9f9f9; padding:20px; border-radius:8px; border: 1px solid #eee;">
                     <label style="display:block; margin-bottom:10px; font-weight:600;"><?= __('chk_transfer_to') ?></label>
                     <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 15px;">
                        <strong><?= htmlspecialchars($settings['bank_name'] ?? __('chk_not_set')) ?></strong><br>
                        <?= __('chk_acc_number') ?> <span style="font-size: 1.1em; color: #007bff;"><?= htmlspecialchars($settings['bank_account'] ?? '-') ?></span><br>
                        <?= __('chk_acc_name') ?> <?= htmlspecialchars($settings['bank_owner'] ?? '-') ?>
                     </div>
                     
                     <label style="display:block; margin-bottom:10px; font-weight:600;"><?= __('chk_attach_slip') ?> <span style="color:red">*</span></label>
                     <input type="file" name="slip" accept="image/*" required>
                </div>

                <button type="submit" class="btn" style="width:100%; margin-top: 20px; font-size: 1.1rem; padding: 18px;"><?= __('chk_confirm_order') ?></button>
            </form>

        </div>

        <!-- Order Summary -->
        <div class="order-summary" style="background: #f9f9f9; padding: 30px; height: fit-content;">
            <h3 style="margin-bottom: 20px;"><?= __('chk_order_summary') ?></h3>
            
            <div class="summary-items" style="margin-bottom: 20px;">
                <?php foreach ($cartItems as $item): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.9rem;">
                    <span><?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['size']) ?>) x <?= $item['qty'] ?></span>
                    <span><?= formatPrice($item['subtotal']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px; display:flex; justify-content:space-between; font-size: 0.9rem;">
                <span><?= __('chk_shipping') ?></span>
                <span><?= isset($_SESSION['user_id']) ? '<span style="color: #22c55e; font-weight: 600;">' . ($_SESSION['lang'] === 'th' ? 'ฟรี (สมาชิก)' : 'Free (Member)') . '</span>' : formatPrice($shippingCost) ?></span>
            </div>
            
            <div style="border-top: 2px solid #333; padding-top: 20px; margin-top: 10px; font-weight: 700; display:flex; justify-content:space-between; font-size: 1.2rem;">
                <span><?= __('chk_total_amount') ?></span>
                <span><?= formatPrice($total) ?></span>
            </div>
            <p style="text-align:right; font-size:0.8rem; color:#666; margin-top:5px;"><?= __('chk_vat_included') ?></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
