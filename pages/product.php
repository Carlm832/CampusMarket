<?php
// pages/product.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

$productId = $_GET['id'] ?? 0;

// Fetch Product Details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.username as seller_name, u.id as seller_id, u.created_at as seller_since
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = :id AND p.status = 'active'
");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="container mt-12 text-center"><h2>Product not found</h2><a href="browse.php" class="btn btn-primary mt-4">Back to Browse</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Fetch Images
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY is_primary DESC");
$stmt->execute([':id' => $productId]);
$images = $stmt->fetchAll();

// Seller Rating
$rating = getSellerRating($pdo, $product['seller_id']);
?>

<div class="container mt-12 mb-20">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        
        <!-- Gallery -->
        <div class="gallery-container">
            <div class="card overflow-hidden" style="background: var(--bg-main); min-height: 400px; display: flex; align-items: center; justify-content: center; position: relative;">
                <?php if (!empty($images)): ?>
                    <img src="<?php echo BASE_URL; ?>/public/<?php echo $images[0]['image_path']; ?>" alt="<?php echo sanitize($product['title']); ?>" style="max-width: 100%; max-height: 500px; object-fit: contain;">
                <?php else: ?>
                    <div class="text-muted" style="font-size: 4rem;">📦</div>
                <?php endif; ?>
                
                <div style="position: absolute; top: 1.5rem; right: 1.5rem;">
                    <?php $badge = conditionBadge($product['condition']); ?>
                    <span class="badge <?php echo $badge['class']; ?> shadow-lg px-4 py-2" style="font-size: 1rem;"><?php echo $badge['label']; ?></span>
                </div>
            </div>
            
            <?php if (count($images) > 1): ?>
                <div class="flex gap-4 mt-4">
                    <?php foreach ($images as $img): ?>
                        <div class="card p-1 cursor-pointer hover-scale" style="width: 80px; height: 80px; overflow: hidden;">
                            <img src="<?php echo BASE_URL; ?>/public/<?php echo $img['image_path']; ?>" alt="Thumb" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div>
            <div class="flex items-center gap-2 text-muted small mb-4">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="browse.php?category=<?php echo $product['category_id']; ?>"><?php echo sanitize($product['category_name']); ?></a>
            </div>

            <h1 class="mb-4" style="font-size: 2.75rem; color: var(--text-main);"><?php echo sanitize($product['title']); ?></h1>
            <div class="flex items-center gap-4 mb-8">
                <span style="font-size: 2.25rem; font-weight: 800; color: var(--primary);"><?php echo formatPrice($product['price']); ?></span>
            </div>

            <div class="card p-6 mb-8" style="background: rgba(255,255,255,0.5); backdrop-filter: blur(10px);">
                <h3 class="mb-4">Description</h3>
                <p style="line-height: 1.8; color: var(--text-muted); font-size: 1.1rem;">
                    <?php echo nl2br(sanitize($product['description'])); ?>
                </p>
            </div>

            <!-- Seller Card -->
            <div class="card p-6 border-accent bg-accent-light flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div style="width: 56px; height: 56px; background: var(--primary); color: white; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5rem;">
                        <?php echo strtoupper(substr($product['seller_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4 class="mb-0">@<?php echo sanitize($product['seller_name']); ?></h4>
                        <div class="flex items-center gap-2 text-sm">
                            <span style="color: #f59e0b;">★ <?php echo $rating['avg']; ?></span>
                            <span class="text-muted">(<?php echo $rating['count']; ?> reviews)</span>
                        </div>
                    </div>
                </div>
                <a href="profile.php?id=<?php echo $product['seller_id']; ?>" class="btn btn-secondary btn-sm">Visit Profile</a>
            </div>

            <div class="flex gap-4">
                <a href="messages.php?to=<?php echo $product['seller_id']; ?>&product=<?php echo $product['id']; ?>" class="btn btn-primary flex-grow justify-center py-4 text-lg">
                    Message Seller
                </a>
                <button class="btn btn-secondary flex items-center justify-center p-4">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </button>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
