<?php
$pageTitle = "Place Order";
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$currentUserId = currentUserId();
$productId = $_GET['id'] ?? null;

if (!$productId) {
    setFlash('error', 'No product specified.');
    redirect(BASE_URL . '/pages/browse.php');
}

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND status = 'active'");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product is no longer available.');
    redirect(BASE_URL . '/pages/browse.php');
}

// Ensure the user isn't trying to buy their own product
if ($product['user_id'] == $currentUserId) {
    setFlash('error', 'You cannot place an order for your own product.');
    redirect(BASE_URL . '/pages/product.php?id=' . $productId);
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meetingPoint = sanitize($_POST['meeting_point'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($meetingPoint)) {
        $error = "Meeting point is required.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Insert into orders table
            $stmt = $pdo->prepare("
                INSERT INTO orders (buyer_id, product_id, amount, status, meeting_point, notes)
                VALUES (:buyer_id, :product_id, :amount, 'pending', :meeting_point, :notes)
            ");
            $stmt->execute([
                ':buyer_id' => $currentUserId,
                ':product_id' => $productId,
                ':amount' => $product['price'],
                ':meeting_point' => $meetingPoint,
                ':notes' => $notes
            ]);
            $orderId = $pdo->lastInsertId();
            
            // Notify the seller
            $notificationTitle = "New Order Placed!";
            $notificationBody = "Someone placed a new order for '{$product['title']}'.";
            createNotification($pdo, $product['user_id'], 'order', $notificationTitle, $notificationBody, $orderId);
            
            $pdo->commit();
            
            setFlash('success', 'Order placed successfully! Waiting for seller confirmation.');
            redirect(BASE_URL . '/pages/my_orders.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "An error occurred while placing your order. Please try again.";
            // Optionally log $e->getMessage()
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container main-content">
    <div class="form-wrapper" style="max-width: 600px; margin: 2rem auto; padding: 2rem; background: var(--bg-card); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2>Place Order</h2>
        <p>You are requesting to buy <strong><?= htmlspecialchars($product['title']) ?></strong> for <strong><?= formatPrice($product['price']) ?></strong>.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="meeting_point">Proposed Meeting Point *</label>
                <input type="text" id="meeting_point" name="meeting_point" class="form-control" placeholder="e.g. Student Union, Library" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="notes">Additional Notes (Optional)</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Any specific time or detail?"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Place Order</button>
            <a href="<?= BASE_URL ?>/pages/product.php?id=<?= $productId ?>" class="btn btn-secondary" style="display: block; text-align: center; margin-top: 10px;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
