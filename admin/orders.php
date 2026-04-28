<?php
// admin/orders.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Auth Check (Admin Only)
if (!isAdmin()) {
    setFlash('error', 'Unauthorized access.');
    redirect('../index.php');
}

$pageTitle = "Order Oversight";

// Fetch All Orders
$stmt = $pdo->query("
    SELECT o.*,
           b.username as buyer_name,
           p.title as product_title,
           s.username as seller_name,
           p.id as product_id
    FROM orders o
    JOIN users b ON o.buyer_id = b.id
    JOIN products p ON o.product_id = p.id
    JOIN users s ON p.user_id = s.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="admin-breadcrumb"><a href="index.php">Dashboard</a> › Orders</div>
            <h1>Marketplace Transactions</h1>
        </div>
        <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 1rem;"><?php echo count($orders); ?> Total Orders</span>
    </div>

    <div class="card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Buyer</th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="text-align: right;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="admin-empty">
                                <span class="admin-empty-icon">🧾</span>
                                No orders have been placed yet.
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="muted">#<?php echo $order['id']; ?></td>
                        <td>
                            <div style="font-weight: 700;"><?php echo sanitize($order['product_title']); ?></div>
                            <a href="../pages/product.php?id=<?php echo $order['product_id']; ?>" style="font-size: 0.78rem; color: var(--primary);" target="_blank">View Item ↗</a>
                        </td>
                        <td>@<?php echo sanitize($order['buyer_name']); ?></td>
                        <td>@<?php echo sanitize($order['seller_name']); ?></td>
                        <td style="font-weight: 700; color: var(--primary);"><?php echo formatPrice($order['amount']); ?></td>
                        <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td class="muted" style="text-align: right;"><?php echo date('M d, H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
