<?php
require_once 'includes/init.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';
$valid_token = false;
$email = '';

// Verify Token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset_req = $stmt->fetch();

    if ($reset_req) {
        $valid_token = true;
        $email = $reset_req['email'];
    } else {
        $error = __('invalid_expired_token') ?? 'Invalid or expired reset token.';
    }
} else {
    redirect('login.php');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = "Security Check Failed: Invalid Token";
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $error = __('req_fields') ?? 'Please fill in all required fields.';
        } elseif (strlen($password) < 6) {
            $error = __('password_min') ?? 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $error = __('pass_mismatch') ?? 'Passwords do not match.';
        } else {
            try {
                $pdo->beginTransaction();

                // Update Password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'user'");
                $stmtUpdate->execute([$hashed_password, $email]);

                // Delete Token
                $stmtDelete = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmtDelete->execute([$token]);

                $pdo->commit();
                $success = __('pass_reset_success') ?? 'Password reset successfully. You can now log in.';
                $valid_token = false; // Hide form
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = __('An error occurred. Please try again.');
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 100px 0; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 450px;">
        <h2 style="text-align: center; margin-bottom: 20px; font-family: 'Outfit', sans-serif; font-size: 2rem;"><?= __('reset_pass_title') ?? 'Reset Password' ?></h2>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.95rem;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php if (!$valid_token && empty($success)): ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="forgot_password.php" style="color: #000; font-weight: 600; text-decoration: none;">&larr; <?= __('try_again') ?? 'Try Again' ?></a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #dcfce7; color: #22c55e; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.95rem;">
                <?= htmlspecialchars($success) ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" class="btn" style="padding: 10px 20px;"><?= __('go_to_login') ?? 'Go to Login' ?></a>
            </div>
        <?php elseif ($valid_token): ?>
            <p style="text-align: center; color: #666; margin-bottom: 30px;"><?= __('enter_new_pass') ?? 'Enter your new password below.' ?></p>

            <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('new_password') ?? 'New Password' ?> <span style="color:red">*</span></label>
                    <input type="password" name="password" required placeholder="<?= __('password_min') ?? 'At least 6 characters' ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('confirm_password') ?? 'Confirm Password' ?> <span style="color:red">*</span></label>
                    <input type="password" name="confirm_password" required style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 15px; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#000'">
                    <?= __('btn_reset_pass') ?? 'Reset Password' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
