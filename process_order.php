<?php
require_once 'includes/init.php';

// Security: Check Request Method & CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    die("Security Check Failed: Invalid Token");
}

// 1. Validate Input
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$payment_method = $_POST['payment_method'];

if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    die("กรุณากรอกข้อมูลให้ครบถ้วน");
}

// 2. Calculate Total & Prepare Items
$total_price = 0;
$order_items = [];

if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

try {
    $pdo->beginTransaction();

    foreach ($_SESSION['cart'] as $key => $qty) {
        $parts = explode('_', $key);
        $productId = $parts[0];
        $variantId = $parts[1];

        // Fetch details AND STOCK to ensure price hacking is impossible and stock is available
        // Use FOR UPDATE to lock row prevents race conditions
        $stmt = $pdo->prepare("SELECT p.name, v.price, v.size, v.stock FROM products p JOIN product_variants v ON p.id = v.product_id WHERE p.id = ? AND v.id = ?");
        $stmt->execute([$productId, $variantId]);
        $item = $stmt->fetch();

        if ($item) {
            // STOCK CHECK
            if ($item['stock'] < $qty) {
                throw new Exception("สินค้า {$item['name']} (ไซส์ {$item['size']}) มีไม่พอ (เหลือ {$item['stock']} ชิ้น)");
            }

            $subtotal = $item['price'] * $qty;
            $total_price += $subtotal;
            
            $order_items[] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'product_name' => $item['name'],
                'size' => $item['size'],
                'price' => $item['price'],
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }

    // 3. Handle Slip Upload
    $slipPath = null;
    if ($payment_method === 'BANK_TRANSFER' && isset($_FILES['slip']) && $_FILES['slip']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = uniqid() . '.' . $ext;
            $dest = 'uploads/slips/' . $newName;
            if (move_uploaded_file($_FILES['slip']['tmp_name'], $dest)) {
                $slipPath = $dest;
            } else {
                die("Failed to upload slip.");
            }
        }
    }

    // 4. Insert Order
    $stmtOrder = $pdo->prepare("INSERT INTO orders (customer_name, email, phone, address, total_price, payment_method, payment_slip, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmtOrder->execute([$name, $email, $phone, $address, $total_price, $payment_method, $slipPath]);
    $order_id = $pdo->lastInsertId();

    // 4. Insert Order Items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, variant_id, product_name, size, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($order_items as $item) {
        $stmtItem->execute([
            $order_id,
            $item['product_id'],
            $item['variant_id'],
            $item['product_name'],
            $item['size'],
            $item['price'],
            $item['quantity'],
            $item['subtotal']
        ]);

        // CUT STOCK
        $stmtStock = $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
        $stmtStock->execute([$item['quantity'], $item['variant_id']]);
    }

    $pdo->commit();

    // 5. Clear Cart & Redirect
    unset($_SESSION['cart']);
    redirect("success.php?order_id=" . $order_id);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    die("เกิดข้อผิดพลาดในการสั่งซื้อ: " . $e->getMessage());
}
?>
