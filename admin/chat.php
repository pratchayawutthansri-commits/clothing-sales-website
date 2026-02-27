<?php
require_once 'includes/config.php'; // Admin config
checkAdminAuth();
require_once '../includes/db.php'; // Main DB

// Fetch active sessions (users who messaged) for initial load
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
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* admin.css handles sidebar+layout */
        .session-link {
            display: block; 
            padding: 15px 20px; 
            border-bottom: 1px solid #f9f9f9; 
            text-decoration: none; 
            color: #333;
        }
        .session-link.active {
            background: #f0f7ff;
            border-left: 4px solid #007bff;
        }
        .session-link:hover {
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<div style="display: flex; height: 100vh; width: 100%;">
    <!-- Main Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Chat Inbox Sidebar -->
    <div style="width: 300px; background: white; border-right: 1px solid #ddd; display: flex; flex-direction: column;">
        <div style="padding: 20px; border-bottom: 1px solid #eee; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
            <span id="inboxCount">Inbox (<?= count($sessions) ?>)</span>
            <button onclick="fetchSidebarSessions()" style="background:none; border:none; cursor:pointer; color:#007bff;" title="Refresh">↻</button>
        </div>
        <div id="sessionList" style="flex: 1; overflow-y: auto;">
            <?php foreach ($sessions as $s): ?>
                <a href="?session=<?= htmlspecialchars($s['session_id'], ENT_QUOTES, 'UTF-8') ?>" class="session-link <?= $currentSession === $s['session_id'] ? 'active' : '' ?>">
                    <div style="font-weight: 600; font-size: 0.9rem;">Guest #<?= htmlspecialchars(substr($s['session_id'], 0, 8)) ?></div>
                    <div style="font-size: 0.8rem; color: #888; margin-top: 5px;"><?= date('H:i', strtotime($s['last_msg'])) ?> · <?= (int)$s['msg_count'] ?> msgs</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div style="flex: 1; display: flex; flex-direction: column; background: #f4f6f8;">
        <?php if ($currentSession): ?>
            <div style="padding: 15px 30px; background: white; border-bottom: 1px solid #ddd; font-weight: bold;">
                Chat with #<?= htmlspecialchars($currentSession) ?>
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
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #999;">Select a conversation to start chatting</div>
        <?php endif; ?>
    </div>
</div>

<script>
    const currentSession = "<?= htmlspecialchars($currentSession ?? '', ENT_QUOTES, 'UTF-8') ?>";
    
    // Auto-refresh sidebar every 5 seconds
    setInterval(fetchSidebarSessions, 5000);

    async function fetchSidebarSessions() {
        try {
            const res = await fetch('chat_api.php?action=get_sessions');
            const data = await res.json();
            
            if (data.status === 'success' && data.sessions) {
                document.getElementById('inboxCount').textContent = `Inbox (${data.sessions.length})`;
                const sessionList = document.getElementById('sessionList');
                sessionList.innerHTML = ''; // Clear current
                
                data.sessions.forEach(s => {
                    const isActive = currentSession === s.session_id ? 'active' : '';
                    const shortId = s.session_id.substring(0, 8);
                    
                    // Format time
                    const d = new Date(s.last_msg.replace(' ', 'T'));
                    const timeStr = d.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12: false});
                    
                    const link = document.createElement('a');
                    link.href = `?session=${s.session_id}`;
                    link.className = `session-link ${isActive}`;
                    link.innerHTML = `
                        <div style="font-weight: 600; font-size: 0.9rem;">Guest #${shortId}</div>
                        <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">${timeStr} · ${s.msg_count} msgs</div>
                    `;
                    sessionList.appendChild(link);
                });
            }
        } catch (e) {
            console.error("Failed to fetch sessions", e);
        }
    }

    if (currentSession) {
        // Poll for current conversation messages every 2 seconds
        setInterval(fetchAdminMessages, 2000);
        fetchAdminMessages(); // Initial fetch
        
        document.getElementById('adminChatForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('adminMsgInput');
            const msg = input.value.trim();
            if(!msg) return;
            
            // Optimistic rendering
            appendAdminMessage(msg, 1); // 1 = admin
            input.value = '';
            
            await fetch('../api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ message: msg, target_session: currentSession })
            });
            
            // Re-fetch to ensure sync
            fetchAdminMessages();
        });
    }

    async function fetchAdminMessages() {
        try {
            const res = await fetch(`chat_api.php?session=${currentSession}`);
            const data = await res.json();
            
            if (data.status === 'success' && data.messages) {
                const container = document.getElementById('adminChatMessages');
                
                // Only rewrite if count changed to avoid flickering (simple check)
                if (container.children.length !== data.messages.length) {
                    container.innerHTML = '';
                    data.messages.forEach(msg => {
                        appendAdminMessage(msg.message, msg.is_admin);
                    });
                }
            }
        } catch (e) {
            console.error("Failed to fetch admin messages", e);
        }
    }

    function appendAdminMessage(text, isAdmin) {
        const div = document.createElement('div');
        div.style.padding = '10px 15px';
        div.style.borderRadius = '8px';
        div.style.maxWidth = '60%';
        div.style.lineHeight = '1.5';
        div.style.wordBreak = 'break-word';
        
        if (isAdmin == 1) { // Admin sent
            div.style.background = '#007bff';
            div.style.color = 'white';
            div.style.alignSelf = 'flex-end';
            div.style.borderBottomRightRadius = '0px';
        } else { // User sent
            div.style.background = 'white';
            div.style.border = '1px solid #ddd';
            div.style.alignSelf = 'flex-start';
            div.style.borderBottomLeftRadius = '0px';
        }
        
        div.textContent = text;
        
        const container = document.getElementById('adminChatMessages');
        const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 10;
        
        container.appendChild(div);
        
        // Auto scroll if already at bottom or if it's our own message
        if (isScrolledToBottom || isAdmin == 1) {
            container.scrollTop = container.scrollHeight;
        }
    }
</script>

</body>
</html>
