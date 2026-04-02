<?php
require_once 'includes/init.php';
include 'includes/header.php';

// Fetch featured products (newest 4 visible products)
$featured_products = Product::getFeatured(4);
?> loam

<!-- Premium Dynamic Styles -->
<link rel="stylesheet" href="css/home.css">

<!-- Cinematic Hero Section -->
<section class="hero-premium">
    <img src="images/hero_premium.png" alt="Premium Streetwear" class="hero-bg" id="parallax-bg">
    <div class="hero-overlay"></div>
    <div class="hero-content-premium">
        <h1 class="hero-brand"><?= __('hero_title_1') ?><br><?= __('hero_title_2') ?></h1>
        <p class="hero-subtitle"><?= __('hero_subtitle') ?></p>
        <a href="shop.php" class="btn-magnetic" id="magnetic-btn">
            <span>EXPLORE COLLECTION <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="feather feather-arrow-right" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span>
        </a>
    </div>
</section>

<!-- Featured Section (Scroll Revealed) -->
<section class="featured">
    <div class="container">
        <div class="section-header reveal-item" style="text-align: center; margin-bottom: 60px; flex-direction: column; align-items: center;">
            <h2 class="section-title"><?= __('new_arrivals') ?></h2>
            <a href="shop.php" class="btn-outline-dark" style="margin-top: 15px;"><?= __('view_all_products') ?></a>
        </div>

        <div class="product-grid">
            <?php foreach ($featured_products as $index => $product): ?>
            <!-- Adding a staggered delay based on index -->
            <div class="product-card reveal-item" style="transition-delay: <?= $index * 0.1 ?>s;">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <div class="product-image-wrapper">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <div class="quick-add-btn"><?= __('view_details') ?></div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <span class="product-cat"><?= htmlspecialchars($product['category']) ?></span>
                        <span class="product-price">฿<?= number_format($product['base_price'], 0) ?></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Avant-Garde Promo Banner -->
<section class="promo-banner reveal-item">
    <div class="promo-content">
        <h2><?= __('promo_title') ?? 'The Monochrome' ?></h2>
        <p style="margin-bottom:40px; color:#aaa; font-family: 'Kanit', sans-serif; letter-spacing: 4px; text-transform: uppercase;">Elevated Simplicity</p>
        <a href="shop.php?cat=Monochrome" class="btn-magnetic" style="background: transparent; color: #fff; border: 1px solid #fff;"><span><?= mb_strtoupper(__('shop_series') ?? 'Explore') ?></span></a>
    </div>
</section>

<!-- JavaScript for Interactions -->
<script src="js/home.js" defer></script>

<?php include 'includes/footer.php'; ?>
