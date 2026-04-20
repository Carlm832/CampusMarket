<?php
// pages/my_orders.php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUserId = currentUserId();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $action = sanitize($_POST['action']);
    $orderId = (int) $_POST['order_id'];
    
    $stmt = $pdo->prepare("SELECT o.*, p.user_id as seller_id, p.title as product_title FROM orders o JOIN products p ON o.product_id = p.id WHERE o.id = :id");
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();
    
    if ($order) {
        $isSeller = ($order['seller_id'] == $currentUserId);
        $isBuyer = ($order['buyer_id'] == $currentUserId);
        
        try {
            $pdo->beginTransaction();
            if ($action === 'confirm' && $isSeller && $order['status'] === 'pending') {
                $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?")->execute([$orderId]);
                $pdo->prepare("UPDATE products SET status = 'sold' WHERE id = ?")->execute([$order['product_id']]);
                $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE product_id = ? AND id != ? AND status = 'pending'")->execute([$order['product_id'], $orderId]);
                createNotification($pdo, $order['buyer_id'], 'order', "Order Confirmed!", "Your order for '{$order['product_title']}' was confirmed.", $orderId);
                setFlash('success', 'Order confirmed and product marked as sold.');
            } elseif ($action === 'cancel' && ($isSeller || $isBuyer) && $order['status'] === 'pending') {
                $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$orderId]);
                $notifyId = $isSeller ? $order['buyer_id'] : $order['seller_id'];
                $cancelerRole = $isSeller ? "Seller" : "Buyer";
                createNotification($pdo, $notifyId, 'system', "Order Cancelled", "The $cancelerRole cancelled the order for '{$order['product_title']}'.", $orderId);
                setFlash('info', 'Order cancelled.');
            }
            $pdo->commit();
        } catch (PDOException $e) { $pdo->rollBack(); setFlash('error', 'Update failed.'); }
    }
    redirect(BASE_URL . '/pages/my_orders.php');
}

// Fetch buying orders
$stmtBuying = $pdo->prepare("SELECT o.*, p.title as product_title, p.price, u.username as seller_name, i.image_path FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON p.user_id = u.id LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1 WHERE o.buyer_id = :uid ORDER BY o.created_at DESC");
$stmtBuying->execute([':uid' => $currentUserId]);
$buyingOrders = $stmtBuying->fetchAll();

// Fetch selling orders
$stmtSelling = $pdo->prepare("SELECT o.*, p.title as product_title, p.price, u.username as buyer_name, i.image_path FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON o.buyer_id = u.id LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1 WHERE p.user_id = :uid ORDER BY o.created_at DESC");
$stmtSelling->execute([':uid' => $currentUserId]);
$sellingOrders = $stmtSelling->fetchAll();

$pageTitle = "My Transactions";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-12 mb-20">
    <h1 class="mb-8 text-center">Your Marketplace Activity</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        
        <!-- My Purchases -->
        <div>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-secondary-light rounded-xl">🛒</div>
                <h2 class="mb-0">My Purchases</h2>
            </div>
            
            <div class="flex flex-col gap-4">
                <?php if (empty($buyingOrders)): ?>
                    <div class="card p-12 text-center bg-gray-50 border-dashed">
                        <p class="text-muted">You haven't bought anything yet.</p>
                        <a href="browse.php" class="btn btn-secondary btn-sm mt-2">Browse Items</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($buyingOrders as $order): ?>
                        <div class="card p-5 flex gap-4 items-center hover-scale">
                            <img src="<?php echo $order['image_path'] ? BASE_URL.'/public/'.$order['image_path'] : '../public/images/placeholder.png'; ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-md);">
                            <div class="flex-grow">
                                <div class="flex justify-between items-start">
                                    <h4 class="mb-1"><?php echo sanitize($order['product_title']); ?></h4>
                                    <span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                </div>
                                <p class="text-muted small mb-0">From @<?php echo sanitize($order['seller_name']); ?> • <?php echo formatPrice($order['price']); ?></p>
                            </div>
                            <?php if ($order['status'] === 'pending'): ?>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this order?')">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Sales -->
        <div>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-primary-light rounded-xl">💰</div>
                <h2 class="mb-0">My Sales</h2>
            </div>

            <div class="flex flex-col gap-4">
                <?php if (empty($sellingOrders)): ?>
                    <div class="card p-12 text-center bg-gray-50 border-dashed">
                        <p class="text-muted">No sales orders yet.</p>
                        <a href="create_listing.php" class="btn btn-primary btn-sm mt-2">Sell Something</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($sellingOrders as $order): ?>
                        <div class="card p-5 hover-scale">
                            <div class="flex gap-4 items-start mb-4">
                                <img src="<?php echo $order['image_path'] ? BASE_URL.'/public/'.$order['image_path'] : '../public/images/placeholder.png'; ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: var(--radius-md);">
                                <div class="flex-grow">
                                    <div class="flex justify-between items-start">
                                        <h4 class="mb-1"><?php echo sanitize($order['product_title']); ?></h4>
                                        <span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                    </div>
                                    <p class="text-muted small">To @<?php echo sanitize($order['buyer_name']); ?> • Proposed: <strong><?php echo sanitize($order['meeting_point']); ?></strong></p>
                                </div>
                            </div>
                            
                            <?php if ($order['status'] === 'pending'): ?>
                                <div class="flex gap-2">
                                    <form method="post" class="flex-grow">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="action" value="confirm" class="btn btn-primary w-full btn-sm">Accept & Close</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
