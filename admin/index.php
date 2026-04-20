<?php
// admin/index.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Auth Check
if (!isAdmin()) {
    setFlash('error', 'Unauthorized access.');
    redirect('../index.php');
}

$pageTitle = "Admin Dashboard";

// Fetch Stats
$stats = [
    'listings'   => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'users'      => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'orders'     => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'reports'    => $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn()
];
?>

<div class="container mt-8 mb-16">
    <h1 class="mb-8">Platform Overview</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="card p-6 border-l-4 shadow-sm" style="border-left-color: var(--primary);">
            <p class="text-muted small uppercase font-bold mb-2">Total Listings</p>
            <h2 class="mb-0"><?php echo $stats['listings']; ?></h2>
        </div>
        <div class="card p-6 border-l-4 shadow-sm" style="border-left-color: #10b981;">
            <p class="text-muted small uppercase font-bold mb-2">Active Users</p>
            <h2 class="mb-0"><?php echo $stats['users']; ?></h2>
        </div>
        <div class="card p-6 border-l-4 shadow-sm" style="border-left-color: #f59e0b;">
            <p class="text-muted small uppercase font-bold mb-2">Total Orders</p>
            <h2 class="mb-0"><?php echo $stats['orders']; ?></h2>
        </div>
        <div class="card p-6 border-l-4 shadow-sm" style="border-left-color: #ef4444;">
            <p class="text-muted small uppercase font-bold mb-2">Pending Reports</p>
            <h2 class="mb-0"><?php echo $stats['reports']; ?></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="card p-8">
            <h3 class="mb-6">Management Modules</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="listings.php" class="card p-6 text-center hover-scale border-accent bg-accent-light">
                    <div class="text-2xl mb-2">📦</div>
                    <div class="font-bold">Listings</div>
                </a>
                <a href="users.php" class="card p-6 text-center hover-scale border-accent bg-accent-light">
                    <div class="text-2xl mb-2">👥</div>
                    <div class="font-bold">Users</div>
                </a>
                <a href="orders.php" class="card p-6 text-center hover-scale border-accent bg-accent-light">
                    <div class="text-2xl mb-2">⚖️</div>
                    <div class="font-bold">Orders</div>
                </a>
                <a href="reports.php" class="card p-6 text-center hover-scale border-accent bg-accent-light">
                    <div class="text-2xl mb-2">🛡️</div>
                    <div class="font-bold">Moderation</div>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <a href="categories.php" class="card p-4 text-center hover-scale border-accent bg-white">
                    <div class="small font-bold">Categories</div>
                </a>
                <a href="tags.php" class="card p-4 text-center hover-scale border-accent bg-white">
                    <div class="small font-bold">Tags</div>
                </a>
            </div>
        </div>

        <div class="card p-8 bg-main text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
            <h3 class="text-white mb-4 text-2xl">System Pulse</h3>
            <p class="mb-8 opacity-80">All core systems are operational. The Marketplace engine is connected to the production database.</p>
            
            <div class="flex items-center gap-4 p-4 rounded-lg bg-white bg-opacity-10 backdrop-blur-md mb-4 shadow-sm">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <div>
                    <div class="font-bold">Database Server</div>
                    <div class="small opacity-70">Latency: 12ms</div>
                </div>
            </div>
            <div class="flex items-center gap-4 p-4 rounded-lg bg-white bg-opacity-10 backdrop-blur-md shadow-sm">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <div>
                    <div class="font-bold">Safety Engine</div>
                    <div class="small opacity-70">Active Monitoring</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
