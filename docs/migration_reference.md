# Migration & Setup Scripts Reference

> ไฟล์เหล่านี้ถูกลบออกจาก production เมื่อ 21 ก.พ. 2569
> เก็บ SQL และรายละเอียดไว้ที่นี่เพื่อเป็น reference

---

## 1. Database Schema — `chat_messages` table

```sql
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_id INT NULL,
    message TEXT NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (session_id),
    INDEX (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**ที่มา**: `admin/setup_chat_db.php`, `admin/fix_chat_table.php`, `admin/create_chat_table_final.php`

---

## 2. Orders Table — Additional Columns

```sql
ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255) NOT NULL AFTER user_id;
ALTER TABLE orders ADD COLUMN email VARCHAR(255) NOT NULL AFTER customer_name;
ALTER TABLE orders ADD COLUMN phone VARCHAR(50) NOT NULL AFTER email;
ALTER TABLE orders ADD COLUMN address TEXT NOT NULL AFTER phone;
ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'pending' AFTER total_price;
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'COD' AFTER total_price;
```

**ที่มา**: `add_checkout_columns.php`, `admin/fix_db.php`

---

## 3. Users Table — Admin Auth Setup

```sql
ALTER TABLE users ADD COLUMN role ENUM('customer', 'admin') DEFAULT 'customer' AFTER email;
ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER id;
```

**Admin user creation:**
```sql
INSERT INTO users (username, name, email, password, role)
VALUES ('admin', 'Admin', 'admin@xivex.com', '<bcrypt_hash>', 'admin');
```

> ⚠️ รหัสผ่านเริ่มต้น: `Xivex@2024` — ควรเปลี่ยนทันทีหลัง setup

**ที่มา**: `admin/setup_auth.php`

---

## 4. Debug Query — Check DB Columns

```sql
SHOW COLUMNS FROM orders;
```

**ที่มา**: `check_db_columns.php`

---

## ไฟล์ที่ถูกลบ (7 ไฟล์)

| ไฟล์ | วัตถุประสงค์ |
|------|-------------|
| `check_db_columns.php` | Debug: แสดง columns ของ orders table |
| `add_checkout_columns.php` | Migration: เพิ่ม customer_name, email, phone, address |
| `admin/setup_auth.php` | Setup: สร้าง admin user + role/username columns |
| `admin/setup_chat_db.php` | Setup: สร้าง chat_messages table |
| `admin/fix_chat_table.php` | Fix: สร้าง chat_messages table (duplicate) |
| `admin/fix_db.php` | Fix: เพิ่ม status + payment_method columns |
| `admin/create_chat_table_final.php` | Setup: สร้าง chat_messages table (duplicate) |

> 📌 SQL ทั้งหมดถูกรวมอยู่ใน `database.sql` เรียบร้อยแล้ว
