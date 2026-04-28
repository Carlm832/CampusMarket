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
    <div class="flex items-center gap-4 mb-8">
        <div class="text-4xl">👑</div>
        <div>
            <h1 class="mb-0 gradient-text">Platform Overview</h1>
            <p class="mb-0 text-muted">Manage CampusMarket activity and operations.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="glass-panel p-6 border-l-4 rounded-lg hover-scale" style="border-left-color: var(--primary);">
            <div class="flex justify-between items-center mb-2">
                <p class="text-muted small uppercase font-bold mb-0">Total Listings</p>
                <span class="text-2xl opacity-50">📦</span>
            </div>
            <h2 class="mb-0" style="font-size: 2.5rem; color: var(--primary);"><?php echo $stats['listings']; ?></h2>
        </div>
        <div class="glass-panel p-6 border-l-4 rounded-lg hover-scale" style="border-left-color: #10b981;">
            <div class="flex justify-between items-center mb-2">
                <p class="text-muted small uppercase font-bold mb-0">Active Users</p>
                <span class="text-2xl opacity-50">👥</span>
            </div>
            <h2 class="mb-0" style="font-size: 2.5rem; color: #10b981;"><?php echo $stats['users']; ?></h2>
        </div>
        <div class="glass-panel p-6 border-l-4 rounded-lg hover-scale" style="border-left-color: #f59e0b;">
             <div class="flex justify-between items-center mb-2">
                <p class="text-muted small uppercase font-bold mb-0">Total Orders</p>
                <span class="text-2xl opacity-50">🛒</span>
            </div>
            <h2 class="mb-0" style="font-size: 2.5rem; color: #f59e0b;"><?php echo $stats['orders']; ?></h2>
        </div>
        <div class="glass-panel p-6 border-l-4 rounded-lg hover-scale" style="border-left-color: #ef4444;">
             <div class="flex justify-between items-center mb-2">
                <p class="text-muted small uppercase font-bold mb-0">Pending Reports</p>
                <span class="text-2xl opacity-50">🛡️</span>
            </div>
            <h2 class="mb-0" style="font-size: 2.5rem; color: #ef4444;"><?php echo $stats['reports']; ?></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="glass-panel p-8 rounded-lg">
            <h3 class="mb-6 gradient-text">Management Modules</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="listings.php" class="card p-6 text-center hover-scale border-accent bg-accent-light" style="border: none; background: linear-gradient(135deg, rgba(99,102,241,0.05), rgba(99,102,241,0.15)); border-radius: var(--radius-lg);">
                    <div class="text-3xl mb-3">📋</div>
                    <div class="font-bold text-main" style="color: var(--primary-hover);">Listings</div>
                </a>
                <a href="users.php" class="card p-6 text-center hover-scale" style="border: none; background: linear-gradient(135deg, rgba(16,185,129,0.05), rgba(16,185,129,0.15)); border-radius: var(--radius-lg);">
                    <div class="text-3xl mb-3">👩‍🎓</div>
                    <div class="font-bold" style="color: var(--secondary-hover);">Users</div>
                </a>
                <a href="orders.php" class="card p-6 text-center hover-scale" style="border: none; background: linear-gradient(135deg, rgba(245,158,11,0.05), rgba(245,158,11,0.15)); border-radius: var(--radius-lg);">
                    <div class="text-3xl mb-3">🧾</div>
                    <div class="font-bold" style="color: #d97706;">Orders</div>
                </a>
                <a href="reports.php" class="card p-6 text-center hover-scale" style="border: none; background: linear-gradient(135deg, rgba(239,68,68,0.05), rgba(239,68,68,0.15)); border-radius: var(--radius-lg);">
                    <div class="text-3xl mb-3">🚨</div>
                    <div class="font-bold" style="color: #b91c1c;">Moderation</div>
                </a>
            </div>
            
            <div class="flex justify-between items-center mt-6 p-4 rounded-lg" style="background: var(--bg-main);">
                <div class="flex gap-4">
                    <a href="categories.php" class="btn btn-secondary btn-sm hover-scale shadow-sm">Categories</a>
                    <a href="tags.php" class="btn btn-secondary btn-sm hover-scale shadow-sm">Tags</a>
                </div>
                <div class="text-muted small">Metadata config</div>
            </div>
        </div>

        <div class="glass-panel-dark p-8 rounded-lg relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 250px; height: 250px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); opacity: 0.15; filter: blur(20px);"></div>
            
            <h3 class="mb-4 text-2xl text-white relative z-10">System Pulse</h3>
            <p class="mb-8 text-muted relative z-10" style="color: rgba(255,255,255,0.7);">All core systems are operational. The Marketplace engine is connected to the production database.</p>
            
            <div class="flex items-center gap-4 p-5 rounded-lg mb-4 glass-panel relative z-10" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                <div class="w-3 h-3 rounded-full animate-pulse" style="background-color: #34d399; box-shadow: 0 0 12px #34d399; width: 12px; height: 12px;"></div>
                <div>
                    <div class="font-bold text-white">Database Server</div>
                    <div class="small" style="color: #34d399;">Latency: 12ms (Optimal)</div>
                </div>
            </div>
            <div class="flex items-center gap-4 p-5 rounded-lg glass-panel relative z-10" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                <div class="w-3 h-3 rounded-full animate-pulse" style="background-color: #34d399; box-shadow: 0 0 12px #34d399; width: 12px; height: 12px;"></div>
                <div>
                    <div class="font-bold text-white">Safety Engine</div>
                    <div class="small" style="color: rgba(255,255,255,0.5);">Active Monitoring & Anti-Spam Online</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
