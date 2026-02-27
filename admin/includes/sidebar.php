<div class="sidebar">
    <h2>Xivex Admin</h2>
    <div style="margin-bottom: 20px; text-align: center;">
        <a href="../change_language.php?lang=th" style="display:inline; padding:5px; <?= $_SESSION['lang'] === 'th' ? 'font-weight:bold; color:white;' : 'color:#666;' ?>">TH</a> | 
        <a href="../change_language.php?lang=en" style="display:inline; padding:5px; <?= $_SESSION['lang'] === 'en' ? 'font-weight:bold; color:white;' : 'color:#666;' ?>">EN</a>
    </div>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><?= __('admin_dashboard') ?></a>
    <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>"><?= __('admin_products') ?></a>
    <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>"><?= __('admin_orders') ?></a>
    <a href="chat.php" class="<?= basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : '' ?>"><?= __('admin_chat') ?></a>
    <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>"><?= __('admin_users') ?></a>
    <a href="promotions.php" class="<?= basename($_SERVER['PHP_SELF']) == 'promotions.php' ? 'active' : '' ?>"><?= __('admin_promotions') ?></a>
    <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>"><?= __('admin_settings') ?></a>
    <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border:none;"><?= __('admin_logout') ?></a>
</div>
