-- Create Database
CREATE DATABASE IF NOT EXISTS xivex_store;

USE xivex_store;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    base_price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(50),
    badge VARCHAR(50) DEFAULT NULL,
    is_visible TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product Variants Table (For Size-Based Pricing)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(10) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 100,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- Orders Table (Updated for Guest Checkout)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'COD',
    payment_slip VARCHAR(255) DEFAULT NULL,
    tracking_number VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

-- Chat Messages Table
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128) NOT NULL,
    message TEXT NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
);

-- Order Items Table (New)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL, -- Snapshot of name at time of purchase
    size VARCHAR(10), -- Snapshot of size
    price DECIMAL(10, 2) NOT NULL, -- Snapshot of price
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id)
);

-- Seed Data for Products (THAI Content)
INSERT INTO
    products (
        name,
        description,
        base_price,
        image,
        category
    )
VALUES (
        'ฮู้ดดี้โอเวอร์ไซส์ สีเบจ (Oversized Beige Hoodie)',
        'ฮู้ดดี้ผ้าฝ้ายเกรดพรีเมียม ทรงโอเวอร์ไซส์ สีเบจทราย สวมใส่สบาย ระบายอากาศได้ดี เหมาะกับทุกสภาพอากาศในไทย ดีไซน์มินิมอล',
        1290.00,
        'images/product_hoodie.jpg',
        'Tops'
    ),
    (
        'กางเกงคาร์โก้ แทคติคอล (Tactical Cargo Pants)',
        'กางเกงคาร์โก้สีดำ ทรงกระบอกเล็ก พร้อมกระเป๋าอเนกประสงค์รอบตัว เนื้อผ้าทนทาน ยืดหยุ่นได้ดี ดีไซน์เท่ ดุดัน',
        1590.00,
        'images/product_pants.jpg',
        'Bottoms'
    ),
    (
        'เสื้อแจ็คเก็ตกันลม (Monochrome Windbreaker)',
        'แจ็คเก็ตสีดำแมท กันลมและละอองน้ำ น้ำหนักเบา ระบายอากาศได้ดี เหมาะสำหรับใส่คลุมกันแดดหรือใส่ขับมอเตอร์ไซค์',
        1890.00,
        'images/product_jacket.jpg',
        'Outerwear'
    ),
    (
        'เสื้อยืด Heavy Tee (Signature Heavy Tee)',
        'เสื้อยืดผ้าฝ้ายหนานุ่ม ทรงกล่อง (Boxy Fit) คอกระชับ ไม่ย้วยง่าย สีขาวคลีน ใส่ได้ทุกวัน',
        590.00,
        'images/product_tee.jpg',
        'Tops'
    ),
    (
        'เสื้อกั๊กยีนส์ (Raw Edge Denim Vest)',
        'เสื้อกั๊กยีนส์สีดำฟอก ดีไซน์ชายรุ่ย เพิ่มความดิบ เท่ ไม่ซ้ำใคร แมทช์ง่ายกับเสื้อยืด',
        1490.00,
        'images/product_vest.jpg',
        'Outerwear'
    ),
    (
        'กระเป๋าสะพายข้าง (Utility Crossbody Bag)',
        'กระเป๋าสะพายข้างสีดำ ขนาดกะทัดรัด จุของได้เยอะ สายปรับระดับได้ กันน้ำ เหมาะสำหรับวันลุยๆ',
        890.00,
        'images/product_bag.jpg',
        'Accessories'
    );

-- Seed Data for Variants (Size Pricing)
-- Hoodie
INSERT INTO
    product_variants (product_id, size, price)
VALUES (1, 'S', 1290.00),
    (1, 'M', 1290.00),
    (1, 'L', 1390.00),
    (1, 'XL', 1490.00);

-- Pants
INSERT INTO
    product_variants (product_id, size, price)
VALUES (2, 'S', 1590.00),
    (2, 'M', 1590.00),
    (2, 'L', 1690.00),
    (2, 'XL', 1790.00);

-- Jacket
INSERT INTO
    product_variants (product_id, size, price)
VALUES (3, 'S', 1890.00),
    (3, 'M', 1890.00),
    (3, 'L', 1990.00),
    (3, 'XL', 2090.00);

-- Tee
INSERT INTO
    product_variants (product_id, size, price)
VALUES (4, 'S', 590.00),
    (4, 'M', 590.00),
    (4, 'L', 650.00),
    (4, 'XL', 690.00);

-- Vest
INSERT INTO
    product_variants (product_id, size, price)
VALUES (5, 'S', 1490.00),
    (5, 'M', 1490.00),
    (5, 'L', 1590.00),
    (5, 'XL', 1690.00);

-- Bag (Free Size)
INSERT INTO
    product_variants (product_id, size, price)
VALUES (6, 'Free', 890.00);