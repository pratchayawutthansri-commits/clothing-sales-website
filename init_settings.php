<?php
/**
 * Settings Initialization Script (Development Only)
 * Run ONCE to insert default settings into database.
 * This file should NOT be accessible in production.
 */

// Security: Only allow from CLI or localhost
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die("Access Denied: This script can only be run from localhost.");
}

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h3>Initializing Settings Table...</h3>";

try {
    // Insert Defaults (only if not already set)
    $defaults = [
        'bank_name' => 'ธนาคารกสิกรไทย (KBANK)',
        'bank_account' => '123-4-56789-0',
        'bank_owner' => 'บจก. ไซเวกซ์ สโตร์',
        'shipping_cost' => '50'
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $key => $val) {
        $stmt->execute([$key, $val]);
        echo "✅ Set '" . htmlspecialchars($key) . "' to '" . htmlspecialchars($val) . "'.<br>";
    }

    echo "<h3>Settings Ready! <a href='admin/settings.php'>Go to Admin Settings</a></h3>";

} catch (PDOException $e) {
    error_log("Init settings error: " . $e->getMessage());
    die("Error initializing settings. Check error log.");
}
?>
