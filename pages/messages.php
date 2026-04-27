<?php
$pageTitle = "Messages";
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$productId = (int)($_GET['product_id'] ?? 0);
$otherUserId = (int)($_GET['other_user_id'] ?? 0);
$currentUserId = currentUserId();

if (!$productId || !$otherUserId) {
    setFlash('error', 'Invalid conversation context.');
    redirect(BASE_URL . '/pages/inbox.php');
}

// Fetch context info
$stmt = $pdo->prepare("SELECT title FROM products WHERE id = :id");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute([':id' => $otherUserId]);
$otherUser = $stmt->fetch();

if (!$product || !$otherUser) {
    setFlash('error', 'Product or User no longer exists.');
    redirect(BASE_URL . '/pages/inbox.php');
}

// Keep product chat context valid: conversations should involve the seller for this product.
$stmt = $pdo->prepare("SELECT user_id FROM products WHERE id = :pid");
$stmt->execute([':pid' => $productId]);
$sellerId = (int) $stmt->fetchColumn();
$isValidConversation = $sellerId > 0
    && ($currentUserId === $sellerId || $otherUserId === $sellerId)
    && ($currentUserId !== $otherUserId);

if (!$isValidConversation) {
    setFlash('error', 'Invalid chat context for this product.');
    redirect(BASE_URL . '/pages/inbox.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container main-content" style="max-width: 800px; margin-top: 2rem;">
    <h2>Chat with <?= htmlspecialchars($otherUser['username']) ?></h2>
    <p>Regarding: <a href="<?= BASE_URL ?>/pages/product.php?id=<?= $productId ?>"><strong><?= htmlspecialchars($product['title']) ?></strong></a></p>
    
    <div id="chat-box" style="height: 400px; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem;">
        <!-- Messages will be loaded here via JS -->
    </div>
    
    <form id="chat-form" style="display: flex; gap: 10px;">
        <input type="text" id="chat-input" class="form-control" style="flex: 1;" placeholder="Type a message..." required autocomplete="off">
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

<script>
const productId = <?= $productId ?>;
const otherUserId = <?= $otherUserId ?>;
const chatBox = document.getElementById('chat-box');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');

function fetchMessages() {
    fetch(`api_messages.php?action=fetch&product_id=${productId}&other_user_id=${otherUserId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderMessages(data.messages);
            }
        });
}

function renderMessages(messages) {
    chatBox.innerHTML = '';
    messages.forEach(msg => {
        const msgDiv = document.createElement('div');
        msgDiv.style.maxWidth = '70%';
        msgDiv.style.padding = '10px 15px';
        msgDiv.style.borderRadius = '20px';
        
        if (msg.is_mine) {
            msgDiv.style.alignSelf = 'flex-end';
            msgDiv.style.background = 'var(--primary-color, #007bff)';
            msgDiv.style.color = '#fff';
        } else {
            msgDiv.style.alignSelf = 'flex-start';
            msgDiv.style.background = 'var(--border-color, #e0e0e0)';
            msgDiv.style.color = 'var(--text-main, #333)';
        }
        
        msgDiv.innerHTML = `
            <div style="font-size: 0.8em; margin-bottom: 3px; opacity: 0.8;">
                ${msg.is_mine ? 'You' : msg.sender_name} &bull; ${msg.created_at}
            </div>
            <div>${msg.body}</div>
        `;
        chatBox.appendChild(msgDiv);
    });
    // Scroll to bottom
    chatBox.scrollTop = chatBox.scrollHeight;
}

chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return;
    
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('product_id', productId);
    formData.append('receiver_id', otherUserId);
    formData.append('body', text);
    
    // Optimistic clear
    chatInput.value = '';
    
    fetch('api_messages.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fetchMessages();
        } else {
            alert('Error sending message: ' + data.error);
        }
    });
});

// Initial fetch
fetchMessages();

// Long polling every 3 seconds
setInterval(fetchMessages, 3000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
