<?php
// Admin Configuration

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Translation Engine for Admin
if (!defined('LANG_LOADED')) {
    define('LANG_LOADED', true);
    require_once __DIR__ . '/../../includes/lang.php';
}

// Auth Check Function
function checkAdminAuth() {
    // Check for is_admin flag AND admin_id (DB Auth)
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true || !isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
    // Ensure CSRF token always exists for admin
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
