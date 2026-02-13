<?php
require_once 'includes/init.php';

// Sanitize Cart (Remove legacy items without variant ID)
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $qty) {
        if (strpos((string)$key, '_') === false) {
            unset($_SESSION['cart'][$key]);
        }
    }
}

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XIVEX | สตรีทแวร์พรีเมียม</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;600;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .nav-link, .cart-icon {
            font-family: 'Kanit', sans-serif !important;
            font-weight: 500 !important;
            letter-spacing: 0.5px;
            font-size: 1rem !important;
        }
        .logo {
            font-family: 'Playfair Display', serif !important;
            font-weight: 900 !important;
        }
    </style>
    <!-- Favicon -->
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
</head>
<body>

<header>
    <div class="container">
        <a href="index.php" class="logo">XIVEX</a>

        <div class="mobile-toggle">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>

        <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">HOME</a></li>
                    <li><a href="shop.php" class="nav-link">SHOP ALL</a></li>
                    <li><a href="shop.php?new=true" class="nav-link">NEW DROPS</a></li>
                    <li><a href="about.php" class="nav-link">ABOUT</a></li>
                    <li><a href="contact.php" class="nav-link">CONTACT</a></li>
                    <li>
                        <a href="cart.php" class="cart-icon">
                            CART 
                            <?php if($cart_count > 0): ?>
                                <span class="cart-count"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
        </nav>
    </div>
</header>
