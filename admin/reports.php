<?php
// admin/reports.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Auth Check
if (!isAdmin()) {
    setFlash('error', 'Unauthorized access.');
    redirect('../index.php');
}

$pageTitle = "Moderation Queue";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['report_id'])) {
    $action = sanitize($_POST['action']);
    $reportId = (int)$_POST['report_id'];
    
    if ($action === 'dismiss') {
        $pdo->prepare("UPDATE reports SET status = 'dismissed' WHERE id = ?")->execute([$reportId]);
        setFlash('success', 'Report dismissed.');
    } elseif ($action === 'flag') {
        $stmt = $pdo->prepare("SELECT product_id FROM reports WHERE id = ?");
        $stmt->execute([$reportId]);
        $productId = $stmt->fetchColumn();
        if ($productId) {
            $pdo->prepare("UPDATE products SET status = 'flagged' WHERE id = ?")->execute([$productId]);
            $pdo->prepare("UPDATE reports SET status = 'reviewed' WHERE id = ?")->execute([$reportId]);
            setFlash('error', 'Item flagged and hidden.');
        }
    }
    redirect('reports.php');
}

// Fetch Reports
$stmt = $pdo->query("
    SELECT r.*, p.title as product_title, u.username as reporter_name 
    FROM reports r 
    JOIN products p ON r.product_id = p.id 
    JOIN users u ON r.reporter_id = u.id 
    WHERE r.status = 'pending' 
    ORDER BY r.created_at ASC
");
$reports = $stmt->fetchAll();
?>

<div class="container mt-8">
    <div class="flex justify-between items-center mb-8">
        <h1>Safety Reports</h1>
        <div class="badge badge-warning"><?php echo count($reports); ?> Pending Reviews</div>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4">Reported Item</th>
                    <th class="p-4">Reason</th>
                    <th class="p-4">By User</th>
                    <th class="p-4">Time</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="5" class="p-12 text-center text-muted">
                            <div class="text-4xl mb-4">🛡️</div>
                            <p>All items comply with community standards. No pending reports!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 font-bold">
                                <?php echo sanitize($r['product_title']); ?>
                                <a href="../pages/product.php?id=<?php echo $r['product_id']; ?>" target="_blank" class="block text-primary small">View Item</a>
                            </td>
                            <td class="p-4 italic text-muted">"<?php echo sanitize($r['reason']); ?>"</td>
                            <td class="p-4">@<?php echo sanitize($r['reporter_name']); ?></td>
                            <td class="p-4 text-muted small"><?php echo timeAgo($r['created_at']); ?></td>
                            <td class="p-4 text-right">
                                <form method="POST" class="flex justify-end gap-2">
                                    <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" name="action" value="dismiss" class="btn btn-secondary btn-sm">Allow</button>
                                    <button type="submit" name="action" value="flag" class="btn btn-danger btn-sm">Flag & Hide</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
