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
        try {
            // Build single JOIN query for all items
            $variantIds = array_column($cartParsed, 'vid');
            $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
            
            $stmt = $pdo->prepare("
                SELECT p.*, v.id AS variant_id, v.size, v.price AS variant_price, v.stock
                FROM products p
                JOIN product_variants v ON v.product_id = p.id
                WHERE v.id IN ($placeholders)
            ");
            $stmt->execute($variantIds);
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Cart database error: " . $e->getMessage());
            $_SESSION['cart'] = [];
            $cartItems = [];
            $total = 0;
            $results = [];
        }
        
        $resultMap = [];
        foreach ($results as $row) {
            $resultMap[$row['variant_id']] = $row;
        }
        
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
                unset($_SESSION['cart'][$key]);
            }
        }
    }
}
?>

<style>
/* ── Modern Dark minimal Cart ── */
.modern-cart-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 80px 5%;
    font-family: 'Kanit', sans-serif;
    color: #fff;
}
.cart-header {
    margin-bottom: 60px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 20px;
}
.cart-header h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 3rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -1px;
    margin: 0;
}
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 80px;
}
.cart-items-wrap {
    display: flex;
    flex-direction: column;
    gap: 30px;
}
.cart-item-modern {
    display: grid;
    grid-template-columns: 120px 1fr auto;
    gap: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.item-img-card {
    background: #0a0a0a;
    aspect-ratio: 3/4;
    overflow: hidden;
}
.item-img-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.item-details {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.item-title {
    font-family: 'Kanit', sans-serif;
    font-size: 1.2rem;
    font-weight: 500;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.item-cat {
    color: #666;
    font-size: 0.85rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 15px;
}
.item-meta {
    font-size: 0.95rem;
    color: #999;
}
.item-meta span {
    color: #fff;
    margin-left: 5px;
}
.item-actions {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-end;
}
.item-price {
    font-family: 'Outfit', sans-serif;
    font-size: 1.3rem;
    font-weight: 600;
}
.qty-form-modern {
    display: flex;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.2);
    padding: 5px 10px;
}
.qty-form-modern input {
    background: transparent;
    border: none;
    color: #fff;
    width: 40px;
    text-align: center;
    font-family: 'Outfit', sans-serif;
    font-size: 1rem;
    outline: none;
}
.remove-btn-modern {
    background: transparent;
    border: none;
    color: #666;
    text-decoration: underline;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    cursor: pointer;
    transition: color 0.3s;
}
.remove-btn-modern:hover { color: #ff2a2a; }

/* Summary Box */
.cart-summary-modern {
    background: #080808;
    padding: 40px;
    border: 1px solid rgba(255,255,255,0.05);
    height: fit-content;
    position: sticky;
    top: 120px;
}
.cart-summary-modern h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    text-transform: uppercase;
    margin-bottom: 30px;
    border-bottom: 2px solid #222;
    padding-bottom: 15px;
}
.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    color: #aaa;
    font-size: 0.95rem;
}
.summary-total-line {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #333;
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
}
.checkout-btn-modern {
    display: block;
    width: 100%;
    margin-top: 40px;
    background: #fff;
    color: #000;
    text-align: center;
    padding: 20px;
    text-decoration: none;
    text-transform: uppercase;
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    letter-spacing: 2px;
    transition: all 0.3s;
}
.checkout-btn-modern:hover {
    background: #ccc;
    transform: translateY(-2px);
}

@media (max-width: 992px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 60px;
    }
}
@media (max-width: 600px) {
    .cart-item-modern {
        grid-template-columns: 80px 1fr;
        grid-template-rows: auto auto;
    }
    .item-actions {
        grid-column: 1 / -1;
        flex-direction: row;
        align-items: center;
        margin-top: 15px;
    }
}

/* ── Light Mode Overrides ── */
body.light-mode .modern-cart-container { color: var(--text-color); }
body.light-mode .cart-header { border-bottom: 1px solid var(--border-medium); }
body.light-mode .cart-header h1 { color: var(--text-color); }
body.light-mode .cart-item-modern { border-bottom: 1px solid var(--border-light); }
body.light-mode .item-img-card { background: var(--bg-secondary); }
body.light-mode .item-title { color: var(--text-color); }
body.light-mode .item-cat { color: var(--text-secondary); }
body.light-mode .item-meta { color: var(--text-muted); }
body.light-mode .item-meta span { color: var(--text-color); }
body.light-mode .item-price { color: var(--text-color); }
body.light-mode .qty-form-modern { border: 1px solid var(--border-medium); }
body.light-mode .qty-form-modern label { color: var(--text-secondary) !important; }
body.light-mode .qty-form-modern input { color: var(--text-color); }
body.light-mode .cart-summary-modern { background: var(--bg-secondary); border: 1px solid var(--border-medium); }
body.light-mode .cart-summary-modern h3 { color: var(--text-color); border-bottom-color: var(--border-medium); }
body.light-mode .summary-line { color: var(--text-secondary); }
body.light-mode .summary-line span:nth-child(2) { color: var(--text-color) !important; }
body.light-mode .summary-total-line { border-top: 1px solid var(--border-medium); color: var(--text-color); }
body.light-mode .checkout-btn-modern { background: var(--text-color); color: var(--bg-primary); }
body.light-mode .checkout-btn-modern:hover { background: var(--text-muted); }
</style>

<div class="modern-cart-container">
    <div class="cart-header">
        <h1><?= __('cart_title') ?></h1>
    </div>

    <?php if (isset($_SESSION['cart_error'])): ?>
        <div style="background-color: rgba(255, 42, 42, 0.1); border-left: 4px solid #ff2a2a; color: #ff2a2a; padding: 20px; margin-bottom: 40px; font-weight: 500;">
            <?= htmlspecialchars($_SESSION['cart_error']) ?>
        </div>
        <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div style="text-align: center; padding: 100px 0;">
            <p style="font-size: 1.2rem; color: #666; margin-bottom: 30px;"><?= __('cart_empty') ?></p>
            <a href="shop.php" class="checkout-btn-modern" style="display: inline-block; width: auto; padding: 16px 40px; background: transparent; color: #fff; border: 1px solid #fff;"><?= __('cart_continue') ?></a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Left: Items List -->
            <div class="cart-items-wrap">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item-modern">
                    <div class="item-img-card">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>
                    
                    <div class="item-details">
                        <div class="item-title"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="item-cat"><?= htmlspecialchars($item['category']) ?></div>
                        <div class="item-meta"><?= __('cart_th_size') ?>: <span><?= htmlspecialchars($item['size']) ?></span></div>
                    </div>
                    
                    <div class="item-actions">
                        <div class="item-price">฿<?= number_format($item['price'], 0) ?></div>
                        
                        <form action="cart_action.php" method="POST" class="qty-form-modern">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($item['cart_key']) ?>">
                            <label style="color:#666; font-size:0.8rem; margin-right:5px;">QTY</label>
                            <input type="number" name="quantity" value="<?= $item['qty'] ?>" min="1" max="<?= isset($item['stock']) ? $item['stock'] : 100 ?>" onchange="this.form.submit()">
                        </form>
                        
                        <form action="cart_action.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($item['cart_key']) ?>">
                            <button type="submit" class="remove-btn-modern"><?= __('cart_btn_remove') ?></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Right: Summary Box -->
            <div class="cart-summary-modern">
                <h3><?= __('chk_order_summary') ?? 'Order Summary' ?></h3>
                
                <div class="summary-line">
                    <span><?= __('cart_subtotal') ?></span>
                    <span style="color:#fff;">฿<?= number_format($total, 0) ?></span>
                </div>
                <div class="summary-line">
                    <span><?= __('cart_shipping') ?></span>
                    <span><?= __('cart_shipping_calc') ?></span>
                </div>
                
                <div class="summary-total-line">
                    <span><?= __('cart_th_total') ?></span>
                    <span>฿<?= number_format($total, 0) ?></span>
                </div>
                
                <a href="checkout.php" class="checkout-btn-modern"><?= __('cart_checkout') ?></a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
