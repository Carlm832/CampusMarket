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
    $action   = sanitize($_POST['action']);
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
            setFlash('error', 'Item flagged and hidden from the marketplace.');
        }
    }
    redirect('reports.php');
}

// Fetch pending reports
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

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="admin-breadcrumb"><a href="index.php">Dashboard</a> › Moderation</div>
            <h1>Safety Reports</h1>
        </div>
        <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.4rem 1rem;"><?php echo count($reports); ?> Pending Review<?php echo count($reports) != 1 ? 's' : ''; ?></span>
    </div>

    <div class="card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reported Item</th>
                        <th>Reason</th>
                        <th>Reported By</th>
                        <th>Time</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="admin-empty">
                                <span class="admin-empty-icon">🛡️</span>
                                <strong>All clear!</strong> No pending reports at the moment.
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700;"><?php echo sanitize($r['product_title']); ?></div>
                            <a href="../pages/product.php?id=<?php echo $r['product_id']; ?>" target="_blank" style="font-size: 0.78rem; color: var(--primary);">View Item ↗</a>
                        </td>
                        <td style="font-style: italic; color: var(--text-muted); max-width: 220px;">"<?php echo sanitize($r['reason']); ?>"</td>
                        <td>@<?php echo sanitize($r['reporter_name']); ?></td>
                        <td class="muted"><?php echo timeAgo($r['created_at']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                <div class="admin-actions">
                                    <button type="submit" name="action" value="dismiss" class="btn btn-secondary btn-sm">Allow</button>
                                    <button type="submit" name="action" value="flag" class="btn btn-danger btn-sm" onclick="return confirm('Flag this item and hide it from the marketplace?')">Flag &amp; Hide</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
