<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once '../includes/db.php'; // DB Connection required for auth
require_once '../includes/functions.php';

// CSRF Generation
if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $_POST['csrf_token'])) {
        die("Security Error: Invalid CSRF Token");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check against Database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'admin' LIMIT 1");
    $stmt->execute([$username, $username]); // Allow login by username or email
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_id'] = $user['id']; // Store ID for changing password later
        session_regenerate_id(true); 
        header("Location: index.php");
        exit;
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xivex Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            background: #f4f4f4; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
        }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box; /* Fix padding issue */
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #000; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1rem; 
        }
        button:hover { background: #333; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Xivex Admin Login</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['admin_csrf_token'] ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
