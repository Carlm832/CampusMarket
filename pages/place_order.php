<?php
// pages/place_order.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$currentUserId = currentUserId();
$productId = $_GET['id'] ?? null;

if (!$productId) {
    setFlash('error', 'No product specified.');
    redirect(BASE_URL . '/pages/browse.php');
}

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, u.username as seller_name, c.name as category_name, i.image_path 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1
    WHERE p.id = :id AND p.status = 'active'
");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product is no longer available.');
    redirect(BASE_URL . '/pages/browse.php');
}

if ($product['user_id'] == $currentUserId) {
    setFlash('error', 'You cannot place an order for your own product.');
    redirect(BASE_URL . '/pages/product.php?id=' . $productId);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meetingPoint = sanitize($_POST['meeting_point'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($meetingPoint)) {
        $error = "Meeting point is required.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, product_id, amount, status, meeting_point, notes) VALUES (:buyer_id, :product_id, :amount, 'pending', :meeting_point, :notes)");
            $stmt->execute([':buyer_id' => $currentUserId, ':product_id' => $productId, ':amount' => $product['price'], ':meeting_point' => $meetingPoint, ':notes' => $notes]);
            $orderId = $pdo->lastInsertId();
            createNotification($pdo, $product['user_id'], 'order', "New Order Placed!", "Someone wants to buy '{$product['title']}'. Check your sales!", $orderId);
            $pdo->commit();
            setFlash('success', 'Order placed! The seller has been notified.');
            redirect('my_orders.php');
        } catch (PDOException $e) { $pdo->rollBack(); $error = "Something went wrong. Please try again."; }
    }
}
$pageTitle = "Complete Your Order";
?>

<div class="container mt-12 mb-20">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-5xl mx-auto">
        <!-- Product Summary -->
        <div>
            <h1 class="mb-8">Review Order</h1>
            <div class="card p-6 flex gap-6 items-start">
                <img src="<?php echo $product['image_path'] ? BASE_URL.'/public/'.$product['image_path'] : '../public/images/placeholder.png'; ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: var(--radius-md);">
                <div>
                    <span class="badge badge-secondary mb-2"><?php echo $product['category_name']; ?></span>
                    <h3 class="mb-1"><?php echo sanitize($product['title']); ?></h3>
                    <p class="text-muted small mb-4">Sold by @<?php echo sanitize($product['seller_name']); ?></p>
                    <div class="text-2xl font-bold text-primary"><?php echo formatPrice($product['price']); ?></div>
                </div>
            </div>
            
            <div class="mt-8 p-6 bg-accent-light rounded-2xl border-accent">
                <h4 class="mb-2">Safe Trading Tips 🛡️</h4>
                <ul class="text-muted small flex flex-col gap-2">
                    <li>• Meet in public, well-lit campus locations.</li>
                    <li>• Inspect the item thoroughly before paying.</li>
                    <li>• Use official campus payment or cash on delivery.</li>
                </ul>
            </div>
        </div>

        <!-- Order Form -->
        <div>
            <div class="card p-8">
                <h2 class="mb-6">Transaction Details</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger mb-6"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group mb-6">
                        <label class="form-label" for="meeting_point">Proposed Meeting Point *</label>
                        <input type="text" name="meeting_point" id="meeting_point" class="form-control" placeholder="e.g. Student Union, Library" required>
                        <p class="text-muted small mt-1">Suggest a neutral spot on campus.</p>
                    </div>

                    <div class="form-group mb-8">
                        <label class="form-label" for="notes">Additional Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Mention your availability or contact preference..."></textarea>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn btn-primary w-full py-4 text-lg">Send Buy Request</button>
                        <a href="product.php?id=<?php echo $productId; ?>" class="btn btn-secondary w-full text-center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
