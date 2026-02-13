<?php
// Admin Configuration

// Auth Check Function
function checkAdminAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Check for is_admin flag AND admin_id (DB Auth)
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true || !isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
