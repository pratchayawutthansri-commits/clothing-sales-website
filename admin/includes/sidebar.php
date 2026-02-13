<div class="sidebar">
    <h2>Xivex Admin</h2>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">ภาพรวม (Dashboard)</a>
    <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">จัดการสินค้า (Products)</a>
    <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">คำสั่งซื้อ (Orders)</a>
    <a href="chat.php" class="<?= basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : '' ?>">แชทลูกค้า (Live Chat)</a>
    <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">ตั้งค่า (Settings)</a>
    <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border:none;">ออกจากระบบ</a>
</div>
