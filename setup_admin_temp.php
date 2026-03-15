<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $password = password_hash('admin1234', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin' AND role = 'admin'");
    $stmt->execute([$password]);
    if ($stmt->rowCount() > 0) {
        echo "Admin password updated successfully!";
    } else {
        echo "Admin account not found or password is the same.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
