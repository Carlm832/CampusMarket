<?php
$pageTitle = "Inbox";
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUserId = currentUserId();

// Fetch the latest message for each conversation
// Grouping by product_id and the "other" user
$stmt = $pdo->prepare("
    SELECT 
        m.id, m.sender_id, m.receiver_id, m.product_id, m.body, m.is_read, m.created_at,
        p.title as product_title,
        IF(m.sender_id = :uid1, m.receiver_id, m.sender_id) as other_user_id,
        u.username as other_username
    FROM messages m
    JOIN products p ON m.product_id = p.id
    JOIN users u ON u.id = IF(m.sender_id = :uid2, m.receiver_id, m.sender_id)
    WHERE m.id IN (
        SELECT MAX(id)
        FROM messages 
        WHERE sender_id = :uid3 OR receiver_id = :uid4
        GROUP BY product_id, IF(sender_id = :uid5, receiver_id, sender_id)
    )
    ORDER BY m.created_at DESC
");
$stmt->execute([
    ':uid1' => $currentUserId,
    ':uid2' => $currentUserId,
    ':uid3' => $currentUserId,
    ':uid4' => $currentUserId,
    ':uid5' => $currentUserId
]);
$conversations = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container main-content" style="max-width: 800px; margin-top: 2rem;">
    <h2>My Inbox</h2>
    
    <?php if (empty($conversations)): ?>
        <p>You have no recent messages.</p>
    <?php else: ?>
        <div class="inbox-list" style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($conversations as $conv): ?>
                <?php
                    $isUnread = ($conv['receiver_id'] == $currentUserId && $conv['is_read'] == 0);
                    $cardBg = $isUnread ? 'var(--bg-card-highlight, #f0f8ff)' : 'var(--bg-card)';
                    $fontWeight = $isUnread ? 'bold' : 'normal';
                ?>
                <a href="<?= BASE_URL ?>/pages/messages.php?product_id=<?= $conv['product_id'] ?>&other_user_id=<?= $conv['other_user_id'] ?>" 
                   style="text-decoration: none; color: inherit;">
                   
                    <div class="card conversation-card" style="padding: 1rem; background: <?= $cardBg ?>; border: 1px solid var(--border-color); border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-weight: <?= $fontWeight ?>;">
                                <?= htmlspecialchars($conv['other_username']) ?> 
                                <span style="font-size: 0.8em; font-weight: normal; color: var(--text-muted);">
                                    re: <?= htmlspecialchars($conv['product_title']) ?>
                                </span>
                            </h4>
                            <p style="margin: 5px 0 0; font-weight: <?= $fontWeight ?>; color: var(--text-main);">
                                <?= ($conv['sender_id'] == $currentUserId) ? 'You: ' : '' ?>
                                <?= htmlspecialchars((strlen($conv['body']) > 50) ? substr($conv['body'], 0, 47) . '...' : $conv['body']) ?>
                            </p>
                        </div>
                        <div style="font-size: 0.8em; color: var(--text-muted); text-align: right;">
                            <?= timeAgo($conv['created_at']) ?>
                            <?php if ($isUnread): ?>
                                <br><span class="badge" style="background: red; color: white;">New</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
