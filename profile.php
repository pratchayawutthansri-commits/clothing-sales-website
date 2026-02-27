<?php
require_once 'includes/init.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

// Fetch order history
$stmtOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmtOrders->execute([$user_id]);
$orders = $stmtOrders->fetchAll();

include 'includes/header.php';
?>

<div class="container" style="padding: 60px 0; min-height: 70vh;">
    <h1 style="margin-bottom: 40px; font-family: 'Outfit', sans-serif; font-size: 2.5rem; text-align: center;"><?= __('prof_title') ?></h1>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px;">
        
        <!-- Sidebar Profile Info -->
        <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: fit-content;">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="width: 80px; height: 80px; background: #000; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin: 0 auto 15px;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h3 style="margin-bottom: 5px;"><?= htmlspecialchars($user['name']) ?></h3>
                <p style="color: #666; font-size: 0.9rem;">@<?= htmlspecialchars($user['username']) ?></p>
            </div>
            
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            
            <div style="margin-bottom: 15px;">
                <p style="font-size: 0.85rem; color: #888; margin-bottom: 2px;"><?= __('prof_email') ?></p>
                <p style="font-weight: 500;"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div style="margin-bottom: 15px;">
                <p style="font-size: 0.85rem; color: #888; margin-bottom: 2px;"><?= __('prof_member_since') ?></p>
                <p style="font-weight: 500;"><?= date('F j, Y', strtotime($user['created_at'])) ?></p>
            </div>
            
            <a href="logout.php" class="btn" style="width: 100%; display: block; text-align: center; margin-top: 30px; background: #fdf2f2; color: #ef4444; border: 1px solid #fca5a5;"><?= __('prof_logout') ?></a>
        </div>

        <!-- Order History -->
        <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
            <h2 style="margin-bottom: 25px; font-family: 'Outfit', sans-serif;"><?= __('prof_order_history') ?></h2>
            
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 50px 0; color: #888; background: #f9f9f9; border-radius: 8px;">
                    <p style="margin-bottom: 15px;"><?= __('prof_no_orders') ?></p>
                    <a href="shop.php" class="btn"><?= __('prof_start_shopping') ?></a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <style>
                        .order-card { border: 1px solid #eee; border-radius: 8px; padding: 20px; transition: 0.2s; }
                        .order-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
                    </style>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                                <div>
                                    <span style="font-weight: 700; font-size: 1.1rem;"><?= __('prof_order_num') ?><?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                    <p style="color: #888; font-size: 0.85rem; margin-top: 5px;"><?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></p>
                                </div>
                                <div style="text-align: right;">
                                    <?php 
                                        $statusColor = '#888';
                                        $statusBg = '#f0f0f0';
                                        $statusText = strtoupper($order['status']);
                                        
                                        if ($order['status'] === 'pending') { $statusColor = '#d97706'; $statusBg = '#fef3c7'; }
                                        elseif ($order['status'] === 'processing') { $statusColor = '#2563eb'; $statusBg = '#dbeafe'; }
                                        elseif ($order['status'] === 'shipped') { $statusColor = '#059669'; $statusBg = '#d1fae5'; }
                                        elseif ($order['status'] === 'cancelled') { $statusColor = '#dc2626'; $statusBg = '#fee2e2'; }
                                    ?>
                                    <span style="display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: <?= $statusColor ?>; background: <?= $statusBg ?>;">
                                        <?= $statusText ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="font-size: 0.9rem; color: #555; margin-bottom: 5px;"><?= __('prof_total_amount') ?></p>
                                    <span style="font-weight: 700; font-size: 1.1rem;"><?= formatPrice($order['total_price']) ?></span>
                                </div>
                                
                                <?php if (!empty($order['tracking_number'])): ?>
                                    <div style="text-align: right;">
                                        <p style="font-size: 0.9rem; color: #555; margin-bottom: 5px;"><?= __('prof_tracking') ?></p>
                                        <span style="font-family: monospace; font-size: 1.1rem; background: #f0f0f0; padding: 4px 8px; border-radius: 4px; border: 1px dashed #ccc;"><?= htmlspecialchars($order['tracking_number']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; text-align: right;">
                                <a href="success.php?order_id=<?= $order['id'] ?>" style="font-size: 0.9rem; color: #000; font-weight: 600; text-decoration: underline;"><?= __('prof_view_receipt') ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<?php include 'includes/footer.php'; ?>
