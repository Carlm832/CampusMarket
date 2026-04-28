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
$stmt = $pdo->prepare("SELECT title, price FROM products WHERE id = :id");
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

<div class="container main-content mb-20" style="max-width: 900px; margin-top: 2rem;">
    <!-- Chat Header Context -->
    <div class="glass-panel mb-4 p-4 flex justify-between items-center" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
        <div class="flex items-center gap-4">
            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--secondaryLight), var(--primaryLight)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.25rem; color: var(--primary);">
                <?php echo strtoupper(substr($otherUser['username'], 0, 2)); ?>
            </div>
            <div>
                <h3 class="mb-0 font-bold" style="line-height: 1.2;">@<?= htmlspecialchars($otherUser['username']) ?></h3>
                <p class="text-muted small mb-0 flex items-center gap-1">
                    <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span> Active recently
                </p>
            </div>
        </div>
        <div class="text-right hidden sm:block">
            <p class="mb-0 text-muted small uppercase tracking-wider font-bold" style="font-size: 0.65rem;">Regarding Item</p>
            <a href="<?= BASE_URL ?>/pages/product.php?id=<?= $productId ?>" class="font-bold text-main hover:text-primary transition-colors" style="text-decoration: none;">
                <?= htmlspecialchars($product['title']) ?>
            </a>
            <p class="text-primary font-bold mb-0"><?= formatPrice($product['price']) ?></p>
        </div>
    </div>
    
    <!-- Chat Container -->
    <div class="glass-panel" style="border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); background: white;">
        
        <!-- Messages Area -->
        <div id="chat-box" style="height: 500px; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; background: #f8fafc; scroll-behavior: smooth;">
            <!-- Loading Indicator -->
            <div class="flex justify-center items-center h-full text-muted" id="chat-loading">
                <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
            <!-- Messages will be loaded here via JS -->
        </div>
        
        <!-- Action area -->
        <?php if ($currentUserId !== $sellerId): ?>
            <!-- Buyer's context: showing order proposal button -->
            <div class="bg-white border-t border-gray-100 p-3 flex justify-between items-center" style="background: linear-gradient(135deg, rgba(245,158,11,0.05), rgba(217,119,6,0.05));">
                <span class="text-sm font-medium text-main">Ready to buy? Send a formal purchase request.</span>
                <form action="api_messages.php" method="POST" class="m-0">
                    <input type="hidden" name="action" value="propose">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm font-bold uppercase tracking-wide hover-scale" style="font-size: 0.75rem; padding: 0.4rem 1rem; border-radius: var(--radius-full); background: white; color: #d97706; border-color: #fcd34d;" onclick="this.form.submit()">Propose Order Details</button>
                    <!-- In a full app, this button would trigger a modal to set meetup details -->
                </form>
            </div>
        <?php endif; ?>

        <!-- Input Area -->
        <div style="background: white; border-top: 1px solid var(--border-light); padding: 1rem;">
            <form id="chat-form" class="flex gap-3 relative m-0">
                <input type="text" id="chat-input" class="premium-input bg-gray-50 flex-grow" style="padding: 1rem 1.5rem; border-radius: var(--radius-full); border: 1px solid var(--border-light); font-size: 1rem;" placeholder="Type your message..." required autocomplete="off">
                <button type="submit" class="btn btn-primary hover-scale shadow-md" style="border-radius: var(--radius-full); width: 54px; height: 54px; padding: 0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Send">
                    <svg style="width: 24px; height: 24px; transform: translateX(2px);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for chat */
#chat-box::-webkit-scrollbar { width: 6px; }
#chat-box::-webkit-scrollbar-track { background: transparent; }
#chat-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
#chat-box::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
const productId = <?= $productId ?>;
const otherUserId = <?= $otherUserId ?>;
const chatBox = document.getElementById('chat-box');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');
let loadingDiv = document.getElementById('chat-loading');

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
    if (loadingDiv) {
        loadingDiv.remove();
        loadingDiv = null;
    }
    
    // Remember if we were at the bottom to auto-scroll
    const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 50;

    chatBox.innerHTML = '';
    
    if (messages.length === 0) {
        chatBox.innerHTML = `
            <div class="text-center text-muted my-auto flex flex-col items-center justify-center opacity-50">
                <div class="text-5xl mb-2">👋</div>
                <p>Say hello to start the conversation!</p>
            </div>
        `;
        return;
    }

    let lastDate = null;

    messages.forEach(msg => {
        // Simple date group check
        const msgDate = new Date(msg.created_at).toLocaleDateString();
        if (msgDate !== lastDate) {
            const dateDiv = document.createElement('div');
            dateDiv.style.textAlign = 'center';
            dateDiv.style.margin = '1rem 0';
            dateDiv.innerHTML = `<span style="background: rgba(0,0,0,0.05); padding: 0.2rem 0.8rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: bold; color: var(--text-muted);">${msgDate}</span>`;
            chatBox.appendChild(dateDiv);
            lastDate = msgDate;
        }

        const msgDiv = document.createElement('div');
        msgDiv.style.maxWidth = '75%';
        msgDiv.style.padding = '0.75rem 1.2rem';
        msgDiv.style.boxShadow = 'var(--shadow-sm)';
        msgDiv.style.position = 'relative';
        msgDiv.style.lineHeight = '1.5';
        
        if (msg.is_mine) {
            msgDiv.style.alignSelf = 'flex-end';
            msgDiv.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
            msgDiv.style.color = '#fff';
            msgDiv.style.borderTopLeftRadius = '18px';
            msgDiv.style.borderTopRightRadius = '18px';
            msgDiv.style.borderBottomLeftRadius = '18px';
            msgDiv.style.borderBottomRightRadius = '4px';
        } else {
            msgDiv.style.alignSelf = 'flex-start';
            msgDiv.style.background = 'white';
            msgDiv.style.color = 'var(--text-main)';
            msgDiv.style.border = '1px solid var(--border-light)';
            msgDiv.style.borderTopLeftRadius = '18px';
            msgDiv.style.borderTopRightRadius = '18px';
            msgDiv.style.borderBottomRightRadius = '18px';
            msgDiv.style.borderBottomLeftRadius = '4px';
        }
        
        let timeStr = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        msgDiv.innerHTML = `
            <div style="font-size: 1rem;">${msg.body}</div>
            <div style="font-size: 0.65rem; text-align: right; margin-top: 4px; opacity: ${msg.is_mine ? '0.8' : '0.5'};">
                ${timeStr}
            </div>
        `;
        chatBox.appendChild(msgDiv);
    });
    
    if (isScrolledToBottom) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return;
    
    // Add optimistic message
    const msgDiv = document.createElement('div');
    msgDiv.style.maxWidth = '75%';
    msgDiv.style.padding = '0.75rem 1.2rem';
    msgDiv.style.alignSelf = 'flex-end';
    msgDiv.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
    msgDiv.style.color = '#fff';
    msgDiv.style.borderTopLeftRadius = '18px';
    msgDiv.style.borderTopRightRadius = '18px';
    msgDiv.style.borderBottomLeftRadius = '18px';
    msgDiv.style.borderBottomRightRadius = '4px';
    msgDiv.style.opacity = '0.7'; // Indicate sending
    msgDiv.innerHTML = `<div style="font-size: 1rem;">${text}</div><div style="font-size: 0.65rem; text-align: right; margin-top: 4px;">Sending...</div>`;
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;

    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('product_id', productId);
    formData.append('receiver_id', otherUserId);
    formData.append('body', text);
    
    chatInput.value = '';
    chatInput.focus();
    
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
            msgDiv.remove(); // Remove failed optimistic message
            chatInput.value = text; // Restore input
        }
    });
});

// Initial fetch
fetchMessages();

// Long polling every 3 seconds
setInterval(fetchMessages, 3000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
