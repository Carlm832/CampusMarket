<?php
// admin/index.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php';

// Specifically check for admin role
requireAdmin();

$pageTitle = "Admin Dashboard";

// Fetch key statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$pendingReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

require_once '../includes/header.php';
?>

    </div>
    
    <div class="mt-8 p-6 bg-white" style="border-radius: var(--radius-lg); border: 1px solid var(--border-light);">
        <h3>Welcome to the Admin Panel</h3>
        <p>Use the buttons above to manage marketplace categories or review user-flagged products to keep the community safe.</p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
