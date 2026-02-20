<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-col">
                <a href="#" class="logo" style="color:#fff; font-size:1.5rem;">XIVEX</a>
                <p style="margin-top:20px; color:#aaa; font-size:0.9rem;">
                    นิยามใหม่ของคนรุ่นใหม่ ด้วยสตรีทแวร์พรีเมียมและดีไซน์ที่แตกต่าง
                </p>
            </div>
            <div class="footer-col">
                <h4>เลือกซื้อ</h4>
                <ul>
                    <li><a href="shop.php">สินค้าทั้งหมด</a></li>
                    <li><a href="shop.php?cat=Tops">เสื้อ</a></li>
                    <li><a href="shop.php?cat=Bottoms">กางเกง</a></li>
                    <li><a href="shop.php?cat=Accessories">อุปกรณ์เสริม</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>ช่วยเหลือ</h4>
                <ul>
                    <li><a href="#">คำถามที่พบบ่อย</a></li>
                    <li><a href="#">การจัดส่ง & คืนสินค้า</a></li>
                    <li><a href="#">ตารางไซส์</a></li>
                    <li><a href="#">ติดต่อเรา</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>รับข่าวสาร</h4>
                <p style="color:#aaa; font-size:0.9rem;">ติดตามคอลเลคชั่นใหม่ก่อนใคร</p>
                <form class="newsletter-form" action="#" method="POST">
                    <input type="email" name="newsletter_email" placeholder="อีเมลของคุณ" required>
                    <button type="submit">→</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom" style="display:flex; justify-content:space-between; align-items:center;">
            <p>&copy; <?php echo date('Y'); ?> XIVEX. All rights reserved.</p>
            <a href="admin/login.php" style="color:#333; font-size:0.8rem; text-decoration:none;">Admin</a>
        </div>
    </div>
</footer>

<script src="js/script.js"></script>
<?php include 'includes/chat_widget.php'; ?>
</body>
</html>
