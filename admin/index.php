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

<div class="mt-8 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="mb-0">Dashboard Overview</h1>
        <div class="flex gap-4">
            <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn btn-secondary">Manage Categories</a>
            <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="btn btn-primary">
                View Reports 
                <?php if($pendingReports > 0): ?>
                    <span class="badge badge-pending" style="margin-left: 0.5rem; background: #fff; color: var(--primary);"><?php echo $pendingReports; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="card card-hover">
            <div class="card-body">
                <p class="form-label mb-2">Total Users</p>
                <h2 style="font-size: 2.5rem; margin-bottom: 0; color: var(--primary);"><?php echo number_format($totalUsers); ?></h2>
            </div>
        </div>

        <div class="card card-hover">
            <div class="card-body">
                <p class="form-label mb-2">Active Products</p>
                <h2 style="font-size: 2.5rem; margin-bottom: 0; color: var(--secondary);"><?php echo number_format($activeProducts); ?></h2>
            </div>
        </div>

        <div class="card card-hover">
            <div class="card-body">
                <p class="form-label mb-2">Pending Orders</p>
                <h2 style="font-size: 2.5rem; margin-bottom: 0; color: var(--warning);"><?php echo number_format($pendingOrders); ?></h2>
            </div>
        </div>

        <div class="card card-hover" style="border-left: 4px solid <?php echo $pendingReports > 0 ? 'var(--error)' : 'var(--success)'; ?>">
            <div class="card-body">
                <p class="form-label mb-2">Pending Reports</p>
                <h2 style="font-size: 2.5rem; margin-bottom: 0; color: <?php echo $pendingReports > 0 ? 'var(--error)' : 'var(--success)'; ?>;"><?php echo number_format($pendingReports); ?></h2>
            </div>
        </div>

    </div>
    
    <div class="mt-8 p-6 bg-white" style="border-radius: var(--radius-lg); border: 1px solid var(--border-light);">
        <h3>Welcome to the Admin Panel</h3>
        <p>Use the buttons above to manage marketplace categories or review user-flagged products to keep the community safe.</p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
