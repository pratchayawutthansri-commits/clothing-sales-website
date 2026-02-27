<?php
require_once 'includes/init.php';

// Clear session variables
$_SESSION = array();

// Destroy session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy session
session_destroy();

// Redirect contextually
if (isset($_GET['admin']) && $_GET['admin'] === 'true') {
    header("Location: admin/login.php");
} else {
    header("Location: index.php");
}
exit;
?>
