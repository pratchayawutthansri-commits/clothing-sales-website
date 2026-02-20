<?php
// api/chat.php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$session_id = session_id();

// Handle Request
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['message']) || empty(trim($input['message']))) {
        echo json_encode(['status' => 'error', 'message' => 'Empty message']);
        exit;
    }

    $message = trim($input['message']);
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

    // For admin, we need user_session_id from post data to reply to specific user
    $targetSession = $session_id;
    if ($isAdmin && isset($input['target_session'])) {
        $targetSession = $input['target_session'];
    }

    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, message, is_admin, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$targetSession, $message, $isAdmin ? 1 : 0]);

    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'fetch') {
    // If Admin, fetching logic is different (fetched via admin panel usually), 
    // but here we might be fetching for the current user (guest/customer)
    $stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
    $stmt->execute([$session_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'messages' => $messages]);
    exit;
}
?>
