<?php
$pageTitle = "Notifications";
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUserId = currentUserId();

// Handle specific actions like marking single/all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'mark_all') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid");
        $stmt->execute([':uid' => $currentUserId]);
        setFlash('success', 'All notifications marked as read.');
    }
    // Additional logic to handle single read could go here
    redirect(BASE_URL . '/pages/notifications.php');
}

// Automatically mark viewed notifications as read after fetching them
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50");
$stmt->execute([':uid' => $currentUserId]);
$notifications = $stmt->fetchAll();

// Now implicitly mark them as read in DB if they aren't
$unreadIds = array_column(array_filter($notifications, fn($n) => $n['is_read'] == 0), 'id');
if (!empty($unreadIds)) {
    $placeholders = str_repeat('?,', count($unreadIds) - 1) . '?';
    $updateStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)");
    $updateStmt->execute($unreadIds);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container main-content" style="max-width: 800px; margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Notifications</h2>
        <?php if (!empty($notifications)): ?>
            <form method="post">
                <button type="submit" name="action" value="mark_all" class="btn btn-secondary btn-sm">Mark All as Read</button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (empty($notifications)): ?>
        <p>You have no notifications.</p>
    <?php else: ?>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php foreach ($notifications as $n): ?>
                <?php
                    // Determine link context based on type
                    $link = '#';
                    if ($n['type'] === 'message') {
                        // Assuming reference_id here is product_id, maybe we need the other_user_id though
                        // In CampusMarket MVP, we might just redirect to inbox where they can find the message thread
                        $link = BASE_URL . '/pages/inbox.php';
                    } elseif ($n['type'] === 'order') {
                        $link = BASE_URL . '/pages/my_orders.php';
                    }
                    
                    $bg = ($n['is_read'] == 0) ? 'var(--bg-card-highlight, #f0f8ff)' : 'transparent';
                ?>
                <li style="background: <?= $bg ?>; padding: 1rem; border-bottom: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 5px;">
                    <strong style="font-size: 1.1em;">
                        <?php if ($link !== '#'): ?>
                            <a href="<?= $link ?>" style="text-decoration: none; color: inherit;"><?= htmlspecialchars($n['title']) ?></a>
                        <?php else: ?>
                            <?= htmlspecialchars($n['title']) ?>
                        <?php endif; ?>
                    </strong>
                    <span><?= htmlspecialchars($n['body']) ?></span>
                    <span style="font-size: 0.8em; color: var(--text-muted);"><?= timeAgo($n['created_at']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
