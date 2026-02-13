<?php
require_once 'includes/config.php'; // Admin config
require_once '../includes/db.php'; // Main DB
checkAdminAuth();

// Fetch active sessions (users who messaged)
$stmt = $pdo->query("SELECT session_id, MAX(created_at) as last_msg, COUNT(*) as msg_count FROM chat_messages GROUP BY session_id ORDER BY last_msg DESC");
$sessions = $stmt->fetchAll();

$currentSession = $_GET['session'] ?? ($sessions[0]['session_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Chat - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; margin: 0; background: #f9f9f9; display: flex; }
        .sidebar { width: 250px; background: #1a1a1a; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; flex-shrink: 0; }
        .sidebar h2 { margin-top: 0; margin-bottom: 30px; letter-spacing: 1px;}
        .sidebar a { display: block; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #333; transition: 0.3s; }
        .sidebar a:hover { color: white; background: #333; padding-left: 20px; }
        .sidebar a.active { color: white; font-weight: bold; background: #333; border-left: 4px solid #fff; }
        .btn { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<div style="display: flex; height: 100vh; width: 100%;">
    <!-- Main Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Chat Inbox Sidebar -->
    <div style="width: 300px; background: white; border-right: 1px solid #ddd; overflow-y: auto;">
        <div style="padding: 20px; border-bottom: 1px solid #eee; font-weight: bold;">Inbox (<?= count($sessions) ?>)</div>
        <?php foreach ($sessions as $s): ?>
            <a href="?session=<?= $s['session_id'] ?>" style="display: block; padding: 15px 20px; border-bottom: 1px solid #f9f9f9; text-decoration: none; color: #333; <?= $currentSession === $s['session_id'] ? 'background:#f0f7ff;' : '' ?>">
                <div style="font-weight: 600; font-size: 0.9rem;">Guest #<?= substr($s['session_id'], 0, 8) ?></div>
                <div style="font-size: 0.8rem; color: #888; margin-top: 5px;"><?= date('H:i', strtotime($s['last_msg'])) ?> · <?= $s['msg_count'] ?> msgs</div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Chat Area -->
    <div style="flex: 1; display: flex; flex-direction: column; background: #f4f6f8;">
        <?php if ($currentSession): ?>
            <div style="padding: 15px 30px; background: white; border-bottom: 1px solid #ddd; font-weight: bold;">
                Chat verifying #<?= $currentSession ?>
            </div>
            
            <div id="adminChatMessages" style="flex: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
                <!-- Messages loaded via JS -->
            </div>

            <div style="padding: 20px; background: white; border-top: 1px solid #ddd;">
                <form id="adminChatForm" style="display: flex; gap: 10px;">
                    <input type="text" id="adminMsgInput" style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Type a reply...">
                    <button type="submit" class="btn">Send</button>
                </form>
            </div>
        <?php else: ?>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #999;">Select a conversation</div>
        <?php endif; ?>
    </div>
</div>

<script>
    const currentSession = "<?= $currentSession ?>";
    
    if (currentSession) {
        // Poll for messages
        setInterval(fetchAdminMessages, 3000);
        fetchAdminMessages();
        
        document.getElementById('adminChatForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('adminMsgInput');
            const msg = input.value.trim();
            if(!msg) return;
            
            // Optimistic
            appendAdminMessage(msg, 1); // 1 = admin
            input.value = '';
            
            await fetch('../api/chat.php', {
                method: 'POST',
                body: JSON.stringify({ message: msg, target_session: currentSession })
            });
        });
    }

    async function fetchAdminMessages() {
        const res = await fetch(`chat_api.php?session=${currentSession}`);
        const data = await res.json();
        
        const container = document.getElementById('adminChatMessages');
        container.innerHTML = '';
        data.messages.forEach(msg => {
            appendAdminMessage(msg.message, msg.is_admin);
        });
    }

    function appendAdminMessage(text, isAdmin) {
        const div = document.createElement('div');
        div.style.padding = '10px 15px';
        div.style.borderRadius = '8px';
        div.style.maxWidth = '60%';
        div.style.lineHeight = '1.5';
        
        if (isAdmin == 1) { // Admin sent
            div.style.background = '#007bff';
            div.style.color = 'white';
            div.style.alignSelf = 'flex-end';
        } else { // User sent
            div.style.background = 'white';
            div.style.border = '1px solid #ddd';
            div.style.alignSelf = 'flex-start';
        }
        
        div.textContent = text;
        document.getElementById('adminChatMessages').appendChild(div);
        
        // Auto scroll
        document.getElementById('adminChatMessages').scrollTop = document.getElementById('adminChatMessages').scrollHeight;
    }
</script>

</body>
</html>
