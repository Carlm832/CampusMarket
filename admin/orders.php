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

<div class="container mt-8">
    <div class="flex justify-between items-center mb-8">
        <h1>Marketplace Transactions</h1>
        <div class="badge badge-info"><?php echo count($orders); ?> Total Orders</div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4">ID</th>
                    <th class="p-4">Item</th>
                    <th class="p-4">Buyer</th>
                    <th class="p-4">Seller</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-muted small">#<?php echo $order['id']; ?></td>
                        <td class="p-4">
                            <div class="font-bold"><?php echo sanitize($order['product_title']); ?></div>
                            <a href="../pages/product.php?id=<?php echo $order['product_id']; ?>" class="small text-primary" target="_blank">View Item</a>
                        </td>
                        <td class="p-4">@<?php echo sanitize($order['buyer_name']); ?></td>
                        <td class="p-4">@<?php echo sanitize($order['seller_name']); ?></td>
                        <td class="p-4 font-bold"><?php echo formatPrice($order['amount']); ?></td>
                        <td class="p-4">
                            <span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                        </td>
                        <td class="p-4 text-right text-muted small">
                            <?php echo date('M d, H:i', strtotime($order['created_at'])); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
