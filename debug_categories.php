<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM products");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Categories found in DB:</h3>";
    echo "<ul>";
    foreach ($categories as $cat) {
        echo "<li>'" . htmlspecialchars($cat) . "'</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
