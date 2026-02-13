<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h3>ORDER TABLE COLUMNS:</h3>";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
