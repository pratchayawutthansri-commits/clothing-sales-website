<?php
require_once 'includes/init.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = "Security Check Failed: Invalid Token";
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = __('Please enter your email address.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = __('Invalid email format.');
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND role = 'user'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Clean up expired tokens and old tokens for this email
                $pdo->prepare("DELETE FROM password_resets WHERE expires_at < NOW() OR email = ?")->execute([$email]);

                $stmtInsert = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                if ($stmtInsert->execute([$email, $token, $expires_at])) {
                    // Send Email
                    $reset_link = SITE_URL . "/reset_password.php?token=" . $token;
                    $to = $email;
                    $subject = "Password Reset Request - Xivex";
                    $message = "Hi " . $user['name'] . ",\n\n";
                    $message .= "You requested a password reset. Click the link below to set a new password:\n";
                    $message .= $reset_link . "\n\n";
                    $message .= "If you did not request this, please ignore this email.\nThis link will expire in 1 hour.";
                    $headers = "From: no-reply@xivex.com\r\n";
                    
                    @mail($to, $subject, $message, $headers); // Supress error on localhost
                    
                    $success = __('reset_link_sent') ?? 'A password reset link has been sent to your email (if it exists in our system).';
                } else {
                    $error = __('An error occurred. Please try again.');
                }
            } else {
                // For security, do not reveal if email exists or not, just show success
                $success = __('reset_link_sent') ?? 'A password reset link has been sent to your email (if it exists in our system).';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 100px 0; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 450px;">
        <h2 style="text-align: center; margin-bottom: 10px; font-family: 'Outfit', sans-serif; font-size: 2rem;"><?= __('forgot_pass_title') ?? 'Forgot Password' ?></h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;"><?= __('forgot_pass_subtitle') ?? 'Enter your email to receive a reset link.' ?></p>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.95rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #dcfce7; color: #22c55e; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.95rem;">
                <?= htmlspecialchars($success) ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: #000; font-weight: 600; text-decoration: none;">&larr; <?= __('back_to_login') ?? 'Back to Login' ?></a>
            </div>
        <?php else: ?>
            <form action="forgot_password.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('email_addr') ?? 'Email Address' ?></label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 15px; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#000'">
                    <?= __('btn_send_reset_link') ?? 'Send Reset Link' ?>
                </button>
            </form>

            <div style="text-align: center; margin-top: 25px;">
                <a href="login.php" style="color: #666; font-size: 0.95rem; text-decoration: none;">&larr; <?= __('back_to_login') ?? 'Back to Login' ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
