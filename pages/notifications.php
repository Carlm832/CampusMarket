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
                <div class="card p-5 flex gap-5 items-start hover:bg-gray-50 transition-colors border-l-4 <?php echo $n['type'] === 'order' ? 'border-primary' : 'border-secondary'; ?>" style="margin-bottom: 1rem;">
                    <div class="p-3 rounded-xl bg-white shadow-sm">
                        <?php echo $n['type'] === 'order' ? '📦' : '✨'; ?>
                    </div>
                    <div class="flex-grow">
                        <div class="flex justify-between items-start">
                            <h4 class="mb-1 font-bold"><?php echo sanitize($n['title']); ?></h4>
                            <span class="text-muted small"><?php echo timeAgo($n['created_at']); ?></span>
                        </div>
                        <p class="text-muted small mb-0"><?php echo sanitize($n['body']); ?></p>
                        
                        <?php if ($n['type'] === 'order'): ?>
                            <a href="my_orders.php" class="btn btn-secondary btn-sm mt-3 py-1">View Order →</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
