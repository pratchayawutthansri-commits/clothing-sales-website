<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';

// Ensure Cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';

// Check CSRF Token for ANY action (Add, Update, Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("Security Check Failed: Invalid CSRF Token"); // Stop execution if token is invalid
    }
}

if ($action === 'add') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Strict Input Validation
    if ($quantity > 100) $quantity = 100; // Cap max quantity
    if ($quantity < 1) $quantity = 1;

    if ($product_id > 0 && $variant_id > 0) {
        $cartKey = $product_id . '_' . $variant_id; // Key: ProductID_VariantID

        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = $quantity;
        }
    }
    
    redirect('cart.php');

} elseif ($action === 'update') {
    $cartKey = $_POST['key'] ?? '';
    $quantity = (int)$_POST['quantity'];
    
    // Validate
    if ($quantity > 100) $quantity = 100;
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$cartKey]);
    } else {
        $_SESSION['cart'][$cartKey] = $quantity;
    }
    
    redirect('cart.php');

} elseif ($action === 'remove') {
    $cartKey = $_POST['key'] ?? '';
    unset($_SESSION['cart'][$cartKey]);
    
    redirect('cart.php');
}

redirect('index.php');
?>
