<?php
require_once 'includes/init.php';
include 'includes/header.php';

$cartItems = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    
    // Parse cart keys into product/variant IDs
    $cartParsed = [];
    foreach ($_SESSION['cart'] as $key => $quantity) {
        $parts = explode('_', $key);
        $productId = (int)$parts[0];
        $variantId = isset($parts[1]) ? (int)$parts[1] : 0;
        if ($productId > 0 && $variantId > 0) {
            $cartParsed[$key] = ['pid' => $productId, 'vid' => $variantId, 'qty' => $quantity];
        } else {
            unset($_SESSION['cart'][$key]);
        }
    }

    if (!empty($cartParsed)) {
        // Build single JOIN query for all items
        $variantIds = array_column($cartParsed, 'vid');
        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
        
        $stmt = $pdo->prepare("
            SELECT p.*, v.id AS variant_id, v.size, v.price AS variant_price
            FROM products p
            JOIN product_variants v ON v.product_id = p.id
            WHERE v.id IN ($placeholders)
        ");
        $stmt->execute($variantIds);
        $results = $stmt->fetchAll();
        
        // Index results by variant_id for quick lookup
        $resultMap = [];
        foreach ($results as $row) {
            $resultMap[$row['variant_id']] = $row;
        }
        
        // Build cart items from results
        foreach ($cartParsed as $key => $cartData) {
            if (isset($resultMap[$cartData['vid']])) {
                $row = $resultMap[$cartData['vid']];
                $row['cart_key'] = $key;
                $row['price'] = $row['variant_price'];
                $row['qty'] = $cartData['qty'];
                $row['subtotal'] = $row['price'] * $cartData['qty'];
                $total += $row['subtotal'];
                $cartItems[] = $row;
            } else {
                // Product or Variant not found (Ghost item) -> Remove from session
                unset($_SESSION['cart'][$key]);
            }
        }
    }
}
?>

<div class="cart-page">
    <div class="container">
        <h1>ตะกร้าสินค้า</h1>
        
        <?php if (empty($cartItems)): ?>
            <p style="margin-top: 20px;">ตะกร้าของคุณว่างเปล่า <a href="shop.php" style="text-decoration:underline;">เลือกซื้อสินค้า</a></p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th>ไซส์</th>
                        <th>ราคา</th>
                        <th>จำนวน</th>
                        <th>รวม</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-item-info">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div>
                                    <h3 style="font-size: 1rem;"><?= htmlspecialchars($item['name']) ?></h3>
                                    <span style="color:#888; font-size: 0.8rem;"><?= htmlspecialchars($item['category']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($item['size']) ?></td>
                        <td>฿<?= number_format($item['price'], 0) ?></td>
                        <td>
                            <form action="cart_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($item['cart_key']) ?>">
                                <input type="number" name="quantity" value="<?= $item['qty'] ?>" class="qty-input" min="1" max="100" onchange="this.form.submit()">
                            </form>
                        </td>
                        <td>฿<?= number_format($item['subtotal'], 0) ?></td>
                        <td>
                            <form action="cart_action.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($item['cart_key']) ?>">
                                <button type="submit" class="remove-btn">ลบ</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>ยอดรวมสินค้า</span>
                    <span>฿<?= number_format($total, 0) ?></span>
                </div>
                <div class="summary-row">
                    <span>ค่าจัดส่ง</span>
                    <span>คำนวณในขั้นตอนถัดไป</span>
                </div>
                <div class="summary-row summary-total">
                    <span>ยอดสุทธิ</span>
                    <span>฿<?= number_format($total, 0) ?></span>
                </div>
                
                <a href="checkout.php" class="btn" style="width:100%; margin-top:20px; text-align:center; display:block;">ดำเนินการชำระเงิน</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
