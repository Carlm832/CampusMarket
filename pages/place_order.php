<?php
// pages/place_order.php
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

// Admins are moderators only — they cannot place orders
if (isAdmin()) {
    setFlash('error', 'Administrators cannot place orders.');
    redirect(BASE_URL . 'admin/index.php');
}

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
            redirect(BASE_URL . '/pages/my_orders.php');
        } catch (PDOException $e) { $pdo->rollBack(); $error = "Something went wrong. Please try again."; }
    }
}
$pageTitle = "Complete Your Order";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container min-h-screen pt-12 pb-20 relative">
    
    <!-- Background Accents -->
    <div style="position: absolute; top: -5%; right: 10%; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(99,102,241,0.06) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-5xl mx-auto">
        <!-- Product Summary -->
        <div>
            <h1 class="mb-8 font-bold text-main" style="font-size: 2.5rem; letter-spacing: -0.5px;">Review Request</h1>
            
            <div class="glass-panel p-6 flex flex-col sm:flex-row gap-6 items-start shadow-md relative overflow-hidden" style="border-radius: var(--radius-xl); background: white;">
                <div style="position: absolute; top: 0; right: 0; bottom: 0; width: 6px; background: linear-gradient(to bottom, var(--primary), var(--secondary));"></div>
                <div style="width: 140px; height: 140px; flex-shrink: 0; border-radius: var(--radius-lg); overflow: hidden; background: #e2e8f0; border: 1px solid rgba(0,0,0,0.05);">
                    <?php if ($product['image_path']): ?>
                        <img src="<?php echo BASE_URL.'/public/'.$product['image_path']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                    <?php endif; ?>
                </div>
                <div class="flex-grow pt-1">
                    <span class="badge uppercase tracking-wider font-bold mb-3" style="font-size: 0.65rem; background: var(--primaryLight); color: var(--primary);"><?php echo $product['category_name']; ?></span>
                    <h3 class="mb-2 font-bold text-main leading-tight" style="font-size: 1.35rem;"><?php echo sanitize($product['title']); ?></h3>
                    <p class="text-muted font-medium mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Sold by <span class="text-main font-bold">@<?php echo sanitize($product['seller_name']); ?></span>
                    </p>
                    <div class="text-3xl font-bold text-primary font-inter tracking-tight"><?php echo formatPrice($product['price']); ?></div>
                </div>
            </div>
            
            <div class="mt-8 glass-panel p-6 shadow-sm border" style="border-radius: var(--radius-xl); background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.1);">
                <h4 class="mb-4 font-bold flex items-center gap-2" style="color: #059669;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Safe Trading Tips
                </h4>
                <div class="flex flex-col gap-3">
                    <div class="flex gap-3">
                        <div style="color: #10b981;">•</div>
                        <p class="mb-0 text-sm font-medium" style="color: #065f46;">Meet in public, well-lit campus locations.</p>
                    </div>
                    <div class="flex gap-3">
                        <div style="color: #10b981;">•</div>
                        <p class="mb-0 text-sm font-medium" style="color: #065f46;">Inspect the item thoroughly before paying.</p>
                    </div>
                    <div class="flex gap-3">
                        <div style="color: #10b981;">•</div>
                        <p class="mb-0 text-sm font-medium" style="color: #065f46;">Agree on payment method (cash/mobile) beforehand.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Form -->
        <div>
            <div class="glass-panel p-8 shadow-xl relative" style="border-radius: var(--radius-xl); background: white;">
                <!-- Card Badge -->
                <div style="position: absolute; top: -15px; left: 2rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: var(--radius-full); color: white; padding: 0.4rem 1.2rem; font-weight: bold; font-size: 0.85rem; box-shadow: var(--shadow-sm); letter-spacing: 0.5px; text-transform: uppercase;">
                    Final Step
                </div>

                <h2 class="mb-6 mt-3 font-bold text-main">Transaction Details</h2>
                
                <?php if ($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-weight: 500;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group mb-6">
                        <label class="font-bold mb-2 block" for="meeting_point" style="color: var(--text-main);">Proposed Meeting Point *</label>
                        <input type="text" name="meeting_point" id="meeting_point" class="w-full premium-input bg-gray-50 text-lg" style="padding: 1rem; border-radius: var(--radius-lg);" placeholder="e.g. Student Union Level 1, Main Library" required>
                        <p class="text-muted small mt-2 italic flex items-center gap-1">
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Suggest a neutral spot on campus.
                        </p>
                    </div>

                    <div class="form-group mb-10">
                        <label class="font-bold mb-2 block" for="notes" style="color: var(--text-main);">Additional Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="w-full premium-input bg-gray-50" rows="4" style="padding: 1rem; border-radius: var(--radius-lg); resize: vertical;" placeholder="Mention your availability or preferred payment method..."></textarea>
                    </div>

                    <div class="flex flex-col gap-4 border-t border-gray-100 pt-6">
                        <button type="submit" class="btn btn-primary w-full shadow-lg hover-scale" style="padding: 1.25rem; border-radius: var(--radius-full); font-size: 1.15rem; font-weight: bold; position: relative; overflow: hidden;">
                            <!-- Button sheen -->
                            <div style="position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transform: skewX(-20deg); animation: shine 3s infinite;"></div>
                            Send Buy Request
                        </button>
                        <a href="product.php?id=<?php echo $productId; ?>" class="text-center font-medium hover:text-primary transition-colors py-2 text-muted">Cancel and go back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes shine {
    0% { left: -100%; }
    20% { left: 200%; }
    100% { left: 200%; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
