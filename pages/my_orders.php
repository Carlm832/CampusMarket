<?php
$pageTitle = "My Orders";
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUserId = currentUserId();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $action = sanitize($_POST['action']);
    $orderId = (int) $_POST['order_id'];
    
    // Fetch the order to verify logic
    $stmt = $pdo->prepare("SELECT o.*, p.user_id as seller_id, p.title as product_title FROM orders o JOIN products p ON o.product_id = p.id WHERE o.id = :id");
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();
    
    if ($order) {
        $isSeller = ($order['seller_id'] == $currentUserId);
        $isBuyer = ($order['buyer_id'] == $currentUserId);
        
        try {
            $pdo->beginTransaction();
            
            if ($action === 'confirm' && $isSeller && $order['status'] === 'pending') {
                // Confirming the order
                $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = :id");
                $stmt->execute([':id' => $orderId]);
                
                // Mark product as sold
                $stmtProduct = $pdo->prepare("UPDATE products SET status = 'sold' WHERE id = :id");
                $stmtProduct->execute([':id' => $order['product_id']]);
                
                // Also cancel any other pending orders for this item
                $stmtCancelOthers = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE product_id = :pid AND id != :oid AND status = 'pending'");
                $stmtCancelOthers->execute([':pid' => $order['product_id'], ':oid' => $orderId]);
                
                // Notify the buyer
                createNotification($pdo, $order['buyer_id'], 'order', "Order Confirmed!", "Your order for '{$order['product_title']}' was confirmed.", $orderId);
                
                setFlash('success', 'Order confirmed and product marked as sold.');
            } elseif ($action === 'cancel' && ($isSeller || $isBuyer) && $order['status'] === 'pending') {
                // Canceling the order
                $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id");
                $stmt->execute([':id' => $orderId]);
                
                // Notify the other party
                $notifyId = $isSeller ? $order['buyer_id'] : $order['seller_id'];
                $cancelerRole = $isSeller ? "Seller" : "Buyer";
                createNotification($pdo, $notifyId, 'system', "Order Cancelled", "The $cancelerRole cancelled the order for '{$order['product_title']}'.", $orderId);
                
                setFlash('error', 'Order cancelled.');
            } else {
                setFlash('error', 'Invalid action or permission denied.');
            }
            
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('error', 'An error occurred updating the order.');
        }
    }
    redirect(BASE_URL . '/pages/my_orders.php');
}


// Fetch buying orders
$stmtBuying = $pdo->prepare("
    SELECT o.*, p.title as product_title, p.price, u.username as seller_name
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u ON p.user_id = u.id
    WHERE o.buyer_id = :uid 
    ORDER BY o.created_at DESC
");
$stmtBuying->execute([':uid' => $currentUserId]);
$buyingOrders = $stmtBuying->fetchAll();

// Fetch selling orders
$stmtSelling = $pdo->prepare("
    SELECT o.*, p.title as product_title, p.price, u.username as buyer_name
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u ON o.buyer_id = u.id
    WHERE p.user_id = :uid 
    ORDER BY o.created_at DESC
");
$stmtSelling->execute([':uid' => $currentUserId]);
$sellingOrders = $stmtSelling->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container main-content">
    <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-top: 2rem;">
        
        <!-- Buying Section -->
        <div class="orders-section" style="flex: 1; min-width: 300px;">
            <h2>My Purchases</h2>
            <?php if (empty($buyingOrders)): ?>
                <p>You haven't placed any orders yet.</p>
            <?php else: ?>
                <?php foreach ($buyingOrders as $order): ?>
                    <div class="card order-card" style="margin-bottom: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px;">
                        <h4><?= htmlspecialchars($order['product_title']) ?></h4>
                        <p><strong>Seller:</strong> <?= htmlspecialchars($order['seller_name']) ?></p>
                        <p><strong>Price:</strong> <?= formatPrice($order['price']) ?></p>
                        <p><strong>Meeting Point:</strong> <?= htmlspecialchars($order['meeting_point']) ?></p>
                        <p><strong>Status:</strong> <span class="badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                        <form method="post" action="" style="margin-top: 10px;">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">Cancel Order</button>
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Selling Section -->
        <div class="orders-section" style="flex: 1; min-width: 300px;">
            <h2>My Sales</h2>
            <?php if (empty($sellingOrders)): ?>
                <p>No one has placed orders on your products yet.</p>
            <?php else: ?>
                <?php foreach ($sellingOrders as $order): ?>
                     <div class="card order-card" style="margin-bottom: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px;">
                        <h4><?= htmlspecialchars($order['product_title']) ?></h4>
                        <p><strong>Buyer:</strong> <?= htmlspecialchars($order['buyer_name']) ?></p>
                        <p><strong>Price:</strong> <?= formatPrice($order['amount']) ?></p>
                        <p><strong>Meeting Point:</strong> <?= htmlspecialchars($order['meeting_point']) ?></p>
                        <p><strong>Buyer's Notes:</strong> <?= htmlspecialchars($order['notes'] ?: 'None') ?></p>
                        <p><strong>Status:</strong> <span class="badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
                        
                        <?php if ($order['status'] === 'pending'): ?>
                        <form method="post" action="" style="display: flex; gap: 10px; margin-top: 10px;">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" name="action" value="confirm" class="btn btn-primary btn-sm">Accept & Mark Sold</button>
                            <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
