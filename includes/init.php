<?php
// Prevent multiple inclusions
if (defined('INIT_LOADED')) {
    return;
}
define('INIT_LOADED', true);

// Start Session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Turn on output buffering to prevent header errors
ob_start();

// Load Core Files
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Set Timezone (Optional but good practice)
date_default_timezone_set('Asia/Bangkok');
?>
