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
    ':uid5' => $currentUserId,
]);
$conversations = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container main-content relative min-h-screen pt-8 pb-20">
    <!-- Background Accents -->
    <div style="position: absolute; top: -5%; left: -5%; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(99,102,241,0.05) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-10">
            <h1 class="mb-0 text-main font-bold" style="font-size: 2.75rem; letter-spacing: -0.5px;">Messages</h1>
            <?php 
                $unreadCount = array_reduce($conversations, function($carry, $item) use ($currentUserId) {
                    return $carry + ($item['receiver_id'] == $currentUserId && $item['is_read'] == 0 ? 1 : 0);
                }, 0);
            ?>
            <?php if ($unreadCount > 0): ?>
                <span class="badge shadow-md px-3 py-1 font-bold" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; display:flex; align-items:center; justify-content:center; border-radius: 9999px; font-size: 1.1rem;"><?= $unreadCount ?> New</span>
            <?php endif; ?>
        </div>
        
        <?php if (empty($conversations)): ?>
            <div class="glass-panel p-16 text-center shadow-sm" style="border-radius: var(--radius-xl); border: 2px dashed rgba(0,0,0,0.05);">
                <div class="mb-6 text-6xl opacity-30">📭</div>
                <h3 class="font-bold text-main text-2xl mb-2">Your inbox is empty</h3>
                <p class="text-muted text-lg max-w-md mx-auto">When you reach out to sellers or receive inquiries about your items, your conversations will appear here.</p>
                <a href="<?= BASE_URL ?>/pages/browse.php" class="btn btn-primary mt-6 hover-scale shadow-md" style="border-radius: var(--radius-full); padding: 0.8rem 2rem; font-weight: bold;">Browse Items</a>
            </div>
        <?php else: ?>
            <div class="inbox-list grid gap-4">
                <?php foreach ($conversations as $conv): ?>
                    <?php
                        $isUnread = ($conv['receiver_id'] == $currentUserId && $conv['is_read'] == 0);
                        $cardBg = $isUnread ? 'linear-gradient(135deg, rgba(99,102,241,0.06), rgba(99,102,241,0.02))' : 'white';
                        $cardBorder = $isUnread ? 'border-primary' : 'border-gray-100';
                        $dotDisplay = $isUnread ? 'block' : 'none';
                    ?>
                    <a href="<?= BASE_URL ?>/pages/messages.php?product_id=<?= $conv['product_id'] ?>&other_user_id=<?= $conv['other_user_id'] ?>" 
                       class="glass-panel hover-scale relative overflow-hidden transition-all duration-300" 
                       style="text-decoration: none; color: inherit; padding: 1.5rem; background: <?= $cardBg ?>; border-radius: var(--radius-lg); border: 1px solid transparent; display: block; box-shadow: var(--shadow-sm);">
                       
                        <!-- Read Status Indicator -->
                        <div style="position: absolute; top: 0; left: 0; bottom: 0; width: 4px; background: var(--primary); display: <?= $dotDisplay ?>;"></div>

                        <div class="flex items-center gap-5">
                            <!-- Avatar -->
                            <div style="width: 56px; height: 56px; flex-shrink: 0; background: linear-gradient(135deg, var(--secondaryLight), var(--primaryLight)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.25rem; color: var(--primary); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                                <?php echo strtoupper(substr($conv['other_username'], 0, 2)); ?>
                            </div>

                            <div style="flex: 1; min-width: 0;">
                                <div class="flex justify-between items-baseline mb-1">
                                    <h4 class="mb-0 text-main truncate" style="font-size: 1.15rem; font-weight: <?= $isUnread ? '800' : '600' ?>;">
                                        <?= htmlspecialchars($conv['other_username']) ?> 
                                    </h4>
                                    <span class="text-sm shrink-0 whitespace-nowrap" style="color: <?= $isUnread ? 'var(--primary)' : 'var(--text-muted)' ?>; font-weight: <?= $isUnread ? 'bold' : 'normal' ?>;">
                                        <?= timeAgo($conv['created_at']) ?>
                                    </span>
                                </div>
                                <div class="text-xs uppercase tracking-wider font-bold mb-1 truncate" style="color: var(--primary);">
                                    Regarding: <?= htmlspecialchars($conv['product_title']) ?>
                                </div>
                                <p class="mb-0 truncate" style="color: <?= $isUnread ? 'var(--text-main)' : 'var(--text-muted)' ?>; font-weight: <?= $isUnread ? '600' : 'normal' ?>;">
                                    <?php if ($conv['sender_id'] == $currentUserId): ?>
                                        <span style="opacity: 0.6; font-size: 0.85em;">You:</span> 
                                    <?php endif; ?>
                                    <?= htmlspecialchars($conv['body']) ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
