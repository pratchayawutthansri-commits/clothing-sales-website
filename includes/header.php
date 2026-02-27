<?php

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

// Calculate unread notifications count + latest notifications
$unread_notifications_count = 0;
$latest_notifications = [];
if (isset($_SESSION['user_id'])) {
    $stmtUnread = $pdo->prepare("SELECT COUNT(id) FROM user_notifications WHERE user_id = ? AND is_read = 0");
    $stmtUnread->execute([$_SESSION['user_id']]);
    $unread_notifications_count = $stmtUnread->fetchColumn();

    // Fetch latest 5 notifications for the dropdown
    $stmtLatest = $pdo->prepare("
        SELECT n.id, n.title, n.type, n.created_at, un.is_read 
        FROM notifications n 
        JOIN user_notifications un ON n.id = un.notification_id 
        WHERE un.user_id = ? 
        ORDER BY n.created_at DESC LIMIT 5
    ");
    $stmtLatest->execute([$_SESSION['user_id']]);
    $latest_notifications = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="XIVEX - สตรีทแวร์พรีเมียมสไตล์ไทย เสื้อผ้าแฟชั่นคุณภาพ ออกแบบด้วยความใส่ใจในทุกรายละเอียด">
    <link rel="icon" href="<?= defined('SITE_URL') ? SITE_URL : '' ?>/favicon.ico" type="image/x-icon">
    <title>XIVEX | สตรีทแวร์พรีเมียม</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Kanit:wght@300;400;500;600&family=Outfit:wght@400;600;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .nav-link, .cart-icon, .lang-switch {
            font-family: 'Kanit', sans-serif !important;
            font-weight: 500 !important;
            letter-spacing: 0.5px;
            font-size: 1rem !important;
        }
        .logo {
            font-family: 'Playfair Display', serif !important;
            font-weight: 900 !important;
        }
        .lang-switch {
            margin-left: 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .lang-switch a {
            color: #999;
            text-decoration: none;
            transition: color 0.3s;
        }
        .lang-switch a.active {
            color: #000;
            font-weight: 700 !important;
        }

        /* ─── Notification Bell ─── */
        .notif-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            margin-right: 18px;
        }
        .notif-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            cursor: pointer;
            transition: background 0.25s, transform 0.25s;
            text-decoration: none;
        }
        .notif-btn:hover {
            background: #e8e8e8;
            transform: scale(1.08);
        }
        .notif-btn svg {
            width: 20px;
            height: 20px;
            color: #333;
            transition: color 0.25s;
        }
        .notif-btn:hover svg { color: #000; }

        .notif-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            background: #ef4444;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            line-height: 1;
            font-family: 'Inter', sans-serif;
        }
        .notif-badge.has-notif {
            animation: notifPulse 2s ease-in-out infinite;
        }

        @keyframes notifPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
            50% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }

        /* Dropdown */
        .notif-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: -20px;
            width: 340px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: opacity 0.25s, visibility 0.25s, transform 0.25s;
            z-index: 9999;
            overflow: hidden;
        }
        .notif-wrapper:hover .notif-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .notif-dropdown::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 30px;
            width: 12px;
            height: 12px;
            background: #fff;
            transform: rotate(45deg);
            box-shadow: -2px -2px 4px rgba(0,0,0,0.04);
        }
        .notif-dd-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .notif-dd-header h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
            font-family: 'Kanit', sans-serif;
        }
        .notif-dd-header span {
            font-size: 0.75rem;
            background: #ef4444;
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }
        .notif-dd-list {
            max-height: 320px;
            overflow-y: auto;
        }
        .notif-dd-item {
            display: flex;
            gap: 12px;
            padding: 14px 20px;
            text-decoration: none;
            transition: background 0.2s;
            border-bottom: 1px solid #f8f8f8;
        }
        .notif-dd-item:hover { background: #fafafa; }
        .notif-dd-item.unread { background: #f0fdf4; }
        .notif-dd-item.unread:hover { background: #dcfce7; }
        .notif-dd-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .notif-dd-icon.promo { background: #fef3c7; }
        .notif-dd-icon.alert { background: #fee2e2; }
        .notif-dd-icon.info  { background: #dbeafe; }
        .notif-dd-body { flex: 1; min-width: 0; }
        .notif-dd-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
            font-family: 'Kanit', sans-serif;
        }
        .notif-dd-time {
            font-size: 0.72rem;
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
        }
        .notif-dd-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 6px;
        }
        .notif-dd-footer {
            text-align: center;
            padding: 12px;
            border-top: 1px solid #f0f0f0;
        }
        .notif-dd-footer a {
            font-size: 0.85rem;
            color: #000;
            font-weight: 600;
            text-decoration: none;
            font-family: 'Kanit', sans-serif;
            transition: color 0.2s;
        }
        .notif-dd-footer a:hover { color: #555; }
        .notif-dd-empty {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
            font-size: 0.85rem;
            font-family: 'Kanit', sans-serif;
        }
    </style>
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
                    <li><a href="index.php" class="nav-link"><?= mb_strtoupper(__('home')) ?></a></li>
                    <li><a href="shop.php" class="nav-link"><?= mb_strtoupper(__('shop_all')) ?></a></li>
                    <li><a href="shop.php?new=true" class="nav-link"><?= mb_strtoupper(__('new_drops')) ?></a></li>
                    <li><a href="about.php" class="nav-link"><?= mb_strtoupper(__('about')) ?></a></li>
                    <li><a href="contact.php" class="nav-link"><?= mb_strtoupper(__('contact')) ?></a></li>
                    <li>
                        <a href="cart.php" class="cart-icon">
                            <?= mb_strtoupper(__('cart')) ?> 
                            <?php if($cart_count > 0): ?>
                                <span class="cart-count"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li style="margin-left: 15px; display: flex; align-items: center;">
                        <!-- Premium Notification Bell -->
                        <div class="notif-wrapper">
                            <a href="notifications.php" class="notif-btn" title="<?= __('notifications') ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <?php if($unread_notifications_count > 0): ?>
                                    <span class="notif-badge has-notif"><?= $unread_notifications_count ?></span>
                                <?php endif; ?>
                            </a>

                            <!-- Dropdown Preview -->
                            <div class="notif-dropdown">
                                <div class="notif-dd-header">
                                    <h4><?= __('notifications') ?></h4>
                                    <?php if($unread_notifications_count > 0): ?>
                                        <span><?= $unread_notifications_count ?> <?= $_SESSION['lang'] === 'th' ? 'ใหม่' : 'new' ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="notif-dd-list">
                                    <?php if(empty($latest_notifications)): ?>
                                        <div class="notif-dd-empty"><?= __('no_notifications') ?></div>
                                    <?php else: ?>
                                        <?php foreach($latest_notifications as $ln): ?>
                                            <?php
                                                $nIcon = 'ℹ️'; $nClass = 'info';
                                                if($ln['type'] === 'promo') { $nIcon = '🎉'; $nClass = 'promo'; }
                                                if($ln['type'] === 'alert') { $nIcon = '⚠️'; $nClass = 'alert'; }
                                                
                                                $diff = time() - strtotime($ln['created_at']);
                                                if ($diff < 60) $timeAgo = ($_SESSION['lang'] === 'th' ? 'เมื่อสักครู่' : 'Just now');
                                                elseif ($diff < 3600) $timeAgo = floor($diff/60) . ($_SESSION['lang'] === 'th' ? ' นาทีที่แล้ว' : 'm ago');
                                                elseif ($diff < 86400) $timeAgo = floor($diff/3600) . ($_SESSION['lang'] === 'th' ? ' ชั่วโมงที่แล้ว' : 'h ago');
                                                else $timeAgo = floor($diff/86400) . ($_SESSION['lang'] === 'th' ? ' วันที่แล้ว' : 'd ago');
                                            ?>
                                            <a href="notifications.php?view=<?= $ln['id'] ?>" class="notif-dd-item <?= !$ln['is_read'] ? 'unread' : '' ?>">
                                                <div class="notif-dd-icon <?= $nClass ?>"><?= $nIcon ?></div>
                                                <div class="notif-dd-body">
                                                    <div class="notif-dd-title"><?= htmlspecialchars($ln['title']) ?></div>
                                                    <div class="notif-dd-time"><?= $timeAgo ?></div>
                                                </div>
                                                <?php if(!$ln['is_read']): ?>
                                                    <div class="notif-dd-dot"></div>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="notif-dd-footer">
                                    <a href="notifications.php"><?= $_SESSION['lang'] === 'th' ? 'ดูทั้งหมด →' : 'View All →' ?></a>
                                </div>
                            </div>
                        </div>

                        <span style="font-weight: 600; font-size: 0.95rem;"><?= __('hi_user') ?><a href="profile.php" style="color: #000; text-decoration: underline;"><?= htmlspecialchars($_SESSION['username']) ?></a>!</span>
                        <a href="logout.php" style="margin-left:10px; font-size: 0.85rem; color: #dc3545; text-decoration: none; font-weight: 600;"><?= mb_strtoupper(__('nav_logout')) ?></a>
                    </li>
                    <?php else: ?>
                    <li style="margin-left: 15px;">
                        <a href="login.php" class="nav-link" style="font-size: 0.95rem !important; display: inline-block;"><?= mb_strtoupper(__('login')) ?></a>
                        <span style="color:#ccc; margin: 0 5px;">/</span>
                        <a href="register.php" class="nav-link" style="font-size: 0.95rem !important; display: inline-block;"><?= mb_strtoupper(__('nav_register')) ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="lang-switch" style="margin-left: 20px;">
                        <a href="change_language.php?lang=th" class="<?= $_SESSION['lang'] === 'th' ? 'active' : '' ?>">TH</a> 
                        <span style="color:#ccc;">|</span> 
                        <a href="change_language.php?lang=en" class="<?= $_SESSION['lang'] === 'en' ? 'active' : '' ?>">EN</a>
                    </li>
                </ul>
        </nav>
    </div>
</header>
