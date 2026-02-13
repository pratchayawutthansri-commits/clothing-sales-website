<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h3>Initializing Settings Table...</h3>";

try {
    // Create Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )");
    echo "✅ Table 'settings' created.<br>";

    // Insert Defaults
    $defaults = [
        'bank_name' => 'ธนาคารกสิกรไทย (KBANK)',
        'bank_account' => '123-4-56789-0',
        'bank_owner' => 'บจก. ไซเวกซ์ สโตร์',
        'shipping_cost' => '50'
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $key => $val) {
        $stmt->execute([$key, $val]);
        echo "✅ Set '$key' to '$val'.<br>";
    }

    echo "<h3>Settings Ready! <a href='admin/settings.php'>Go to Admin Settings</a></h3>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
