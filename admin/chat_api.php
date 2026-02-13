<?php
// admin/chat_api.php
header('Content-Type: application/json');
require_once 'includes/config.php';
require_once '../includes/db.php';

// Ensure Admin
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$sessionId = $_GET['session'] ?? '';

if ($sessionId) {
    $stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
    $stmt->execute([$sessionId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'messages' => $messages]);
} else {
    echo json_encode(['status' => 'success', 'messages' => []]);
}
?>
