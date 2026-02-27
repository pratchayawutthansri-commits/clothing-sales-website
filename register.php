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
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($name) || empty($email) || empty($password)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "Username or Email already exists.";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password, role) VALUES (?, ?, ?, ?, 'user')");
                if ($stmt->execute([$username, $name, $email, $hashed_password])) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "An error occurred during registration. Please try again.";
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 100px 0; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 500px;">
        <h2 style="text-align: center; margin-bottom: 10px; font-family: 'Outfit', sans-serif; font-size: 2rem;"><?= __('register_title') ?></h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;"><?= __('register_subtitle') ?></p>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.95rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #dcfce7; color: #22c55e; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.95rem;">
                <?= htmlspecialchars($success) ?>
                <div style="margin-top: 10px;">
                    <a href="login.php" class="btn" style="padding: 8px 20px; font-size: 0.9rem;"><?= __('go_to_login') ?></a>
                </div>
            </div>
        <?php else: ?>
            <form action="register.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('full_name') ?> <span style="color:red">*</span></label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('username') ?> <span style="color:red">*</span></label>
                    <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('email_addr') ?> <span style="color:red">*</span></label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('password') ?> <span style="color:red">*</span></label>
                    <input type="password" name="password" required placeholder="<?= __('password_min') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.95rem;"><?= __('confirm_password') ?> <span style="color:red">*</span></label>
                    <input type="password" name="confirm_password" required placeholder="<?= __('retype_password') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#000'" onblur="this.style.borderColor='#e5e7eb'">
                </div>

                <button type="submit" style="width: 100%; background: #000; color: #fff; border: none; padding: 15px; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#000'">
                    <?= __('btn_register') ?>
                </button>
            </form>

            <p style="text-align: center; margin-top: 25px; color: #666; font-size: 0.95rem;">
                <?= __('have_account') ?> 
                <a href="login.php" style="color: #000; font-weight: 600; text-decoration: none;"><?= __('login_here') ?></a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
