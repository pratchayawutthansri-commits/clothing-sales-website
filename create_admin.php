<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    // กำหนดข้อมูลแอดมินใหม่
    $username = 'webadmin';
    $password_plain = 'admin1234';
    $email = 'webadmin@xivex.com'; // เปลี่ยนอีเมลเพื่อป้องกันความซ้ำซ้อน
    $name = 'Web Administrator';
    
    // เข้ารหัสพาสเวิร์ด
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    // ตรวจสอบว่าแอดมินคนนี้มีอยู่แล้วไหม
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        // อัปเดตรหัสผ่านใหม่
        $stmt_update = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
        $stmt_update->execute([$password_hashed, $username]);
        echo "♻️ อัปเดตรหัสผ่านสำเร็จ!<br>";
    } else {
        // สมัครใหม่
        $stmt_insert = $pdo->prepare("INSERT INTO users (username, name, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt_insert->execute([$username, $name, $email, $password_hashed]);
        echo "✅ สร้างบัญชีแอดมินใหม่สำเร็จ!<br>";
    }
    
    // อัปเดตรหัสแอดมินเดิม (ถ้ามี) ให้เผื่อไว้ด้วย
    $stmt_update_old = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
    $stmt_update_old->execute([$password_hashed]);

    echo "<br>";
    echo "<b>ข้อมูลสำหรับเข้าสู่ระบบของแอดมิน (Admin Login Info):</b><br>";
    echo "Username: <b>$username</b> หรือ <b>admin</b><br>";
    echo "Password: <b>$password_plain</b><br>";
    echo "<br><a href='admin/login.php'>ไปที่หน้าเข้าสู่ระบบแอดมิน (Go to Admin Login)</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
