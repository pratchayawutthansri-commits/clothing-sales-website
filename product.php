<?php
require_once 'includes/init.php';

// --- CONTROLLER LOGIC ---
// Enhanced input validation
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate product ID range to prevent abuse
if ($id <= 0 || $id > 999999) {
    error_log("Invalid product ID access attempt: {$id} from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    redirect('shop.php');
}

$product = null;
$variants = [];
$min_price = 0;
$max_price = 0;
$error_message = '';

try {
    // 1. Fetch Product
    $product = Product::find($id);

    if ($product) {
        // 2. Fetch Variants
        $variants = Product::getVariants($id);

        // 3. Determine Price Range
        $min_price = $product['base_price'];
        $max_price = $product['base_price'];

        if (count($variants) > 0) {
            $prices = array_column($variants, 'price');
            $min_price = min($prices);
            $max_price = max($prices);
        }
    } else {
        $error_message = __('prod_not_found');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $error_message = __('prod_error_load');
}

// --- VIEW RENDERING ---
include 'includes/header.php';
?>

<div class="container">
    <?php if ($error_message): ?>
        <div style="padding:100px 0; text-align:center;">
            <h2><?= htmlspecialchars($error_message) ?></h2>
            <a href="shop.php" class="btn"><?= __('prod_back_shop') ?></a>
        </div>
    <?php else: ?>
        <div class="product-detail-container">
            <div class="detail-image">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <div class="detail-info">
                <a href="shop.php" class="back-link">&larr; <?= __('prod_back_shop') ?></a>
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <p class="detail-price" id="display-price">
                    <?php if ($min_price != $max_price): ?>
                        <?= formatPrice($min_price) ?> - <?= formatPrice($max_price) ?>
                    <?php else: ?>
                        <?= formatPrice($min_price) ?>
                    <?php endif; ?>
                </p>
                
                <div class="detail-desc">
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <form action="cart_action.php" method="POST">
                    <!-- Security: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <?php if (count($variants) > 0): ?>
                    <div class="form-group">
                        <label><?= __('prod_select_size') ?></label>
                        <div class="size-selector">
                            <?php foreach ($variants as $index => $variant): ?>
                            <label class="size-option">
                                <input type="radio" name="variant_id" value="<?= $variant['id'] ?>" 
                                       data-price="<?= $variant['price'] ?>" 
                                       data-stock="<?= $variant['stock'] ?>"
                                       <?= $index === 0 ? 'checked' : '' ?> required>
                                <span><?= htmlspecialchars($variant['size']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- No variants: use product base_price directly -->
                    <input type="hidden" name="variant_id" value="0">
                    <?php endif; ?>
                    
                    
                    <div class="form-group">
                         <label><?= __('prod_qty') ?></label>
                         <?php $default_stock = count($variants) > 0 ? $variants[0]['stock'] : 100; ?>
                         <input type="number" name="quantity" id="qty-input" value="1" min="1" max="<?= $default_stock ?>" style="padding: 10px; width: 60px; text-align: center; border: 1px solid #ddd;">
                         <span id="stock-warning" style="color: #ff2a2a; margin-left: 10px; font-size: 0.85rem; display: none;"></span>
                    </div>

                    <button type="submit" class="btn add-to-cart-btn"><?= mb_strtoupper(__('prod_add_cart')) ?></button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($product): ?>
<script src="js/product.js" defer></script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
