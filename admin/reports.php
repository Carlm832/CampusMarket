<?php
// admin/reports.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php';

// Specifically check for admin role
requireAdmin();

$pageTitle = "Moderation Reports";

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['report_id'])) {
        $action = sanitize($_POST['action']);
        $reportId = (int) $_POST['report_id'];
        
        if ($action === 'dismiss') {
            $stmt = $pdo->prepare("UPDATE reports SET status = 'dismissed' WHERE id = ?");
            $stmt->execute([$reportId]);
            setFlash('success', 'Report dismissed successfully.');
            
        } elseif ($action === 'flag') {
            // Retrieve associated product
            $stmt = $pdo->prepare("SELECT product_id FROM reports WHERE id = ?");
            $stmt->execute([$reportId]);
            $productId = $stmt->fetchColumn();
            
            if ($productId) {
                // Flag the product (Hide from marketplace)
                $pdo->prepare("UPDATE products SET status = 'flagged' WHERE id = ?")->execute([$productId]);
                // Set report to reviewed
                $pdo->prepare("UPDATE reports SET status = 'reviewed' WHERE id = ?")->execute([$reportId]);
                setFlash('error', 'Product flagged and removed from active listings.');
            }
        }
        redirect(BASE_URL . '/admin/reports.php');
    }
}

// Fetch pending reports
$stmt = $pdo->query("
    SELECT r.id, r.reason, r.created_at, 
           p.id as product_id, p.title as product_title, 
           u.username as reporter_name
    FROM reports r
    JOIN products p ON r.product_id = p.id
    JOIN users u ON r.reporter_id = u.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at ASC
");
$reports = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="mt-8 mb-8">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="btn btn-secondary btn-sm">← Dashboard</a>
            <h1 class="mb-0">Reports Moderation</h1>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reporter</th>
                    <th>Product</th>
                    <th>Reason</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <svg style="width: 48px; height: 48px; margin: 0 auto 1rem; color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <p>No pending reports to moderate! Great job.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo sanitize(timeAgo($report['created_at'])); ?></td>
                            <td><strong><?php echo sanitize($report['reporter_name']); ?></strong></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/pages/product.php?id=<?php echo $report['product_id']; ?>" target="_blank">
                                    <?php echo sanitize($report['product_title']); ?>
                                </a>
                            </td>
                            <td><?php echo sanitize($report['reason']); ?></td>
                            <td>
                                <form method="POST" class="flex gap-2" onsubmit="return confirm('Are you sure you want to take this action?');">
                                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                    <button type="submit" name="action" value="dismiss" class="btn btn-secondary btn-sm">Dismiss</button>
                                    <button type="submit" name="action" value="flag" class="btn btn-danger btn-sm">Flag Product</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
