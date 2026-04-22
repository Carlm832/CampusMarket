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

<!-- Breadcrumbs -->
<div class="container mt-8">
    <nav class="flex items-center gap-2 text-muted small">
        <a href="../index.php" class="hover:text-primary">Home</a>
        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <a href="browse.php" class="hover:text-primary">Marketplace</a>
        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <a href="browse.php?category=<?php echo $product['category_id']; ?>" class="hover:text-primary"><?php echo sanitize($product['category_name']); ?></a>
    </nav>
</div>

<div class="container mt-8 mb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Gallery (Left Column - 7/12) -->
        <div class="lg:col-span-7">
            <div class="card overflow-hidden" style="background: white; border-radius: 2rem; border: 1px solid var(--border-light); position: relative;">
                <div style="height: 500px; display: flex; align-items: center; justify-content: center; background: #fdfdfd;">
                    <?php if (!empty($images)): ?>
                        <img id="main-image" src="<?php echo BASE_URL; ?>public/<?php echo $images[0]['image_path']; ?>" alt="<?php echo sanitize($product['title']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 1rem;">
                    <?php else: ?>
                        <div class="text-muted text-center">
                            <div style="font-size: 5rem; margin-bottom: 1rem;">📦</div>
                            <p>No images available for this item</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 10;">
                    <?php $badge = conditionBadge($product['condition']); ?>
                    <span class="badge <?php echo $badge['class']; ?> shadow-lg px-6 py-3" style="font-size: 1rem; border-radius: var(--radius-full);"><?php echo $badge['label']; ?></span>
                </div>
            </div>
            
            <?php if (count($images) > 1): ?>
                <div class="flex gap-4 mt-6 overflow-x-auto pb-2">
                    <?php foreach ($images as $index => $img): ?>
                        <div class="card p-1 cursor-pointer transition-all hover:border-primary <?php echo $index === 0 ? 'border-primary' : ''; ?>" 
                             style="width: 100px; height: 100px; flex-shrink: 0; border-radius: 1rem;" 
                             onclick="document.getElementById('main-image').src='<?php echo BASE_URL; ?>public/<?php echo $img['image_path']; ?>'; document.querySelectorAll('.gallery-thumb').forEach(el => el.classList.remove('border-primary')); this.classList.add('border-primary');">
                            <img src="<?php echo BASE_URL; ?>public/<?php echo $img['image_path']; ?>" alt="Thumb" class="gallery-thumb" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.75rem;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="mt-12">
                <h3 class="mb-6" style="font-size: 1.75rem;">Product Description</h3>
                <div class="card p-8" style="border-radius: 1.5rem; background: white; line-height: 1.8; color: var(--text-main); font-size: 1.1rem; border: 1px solid var(--border-light);">
                    <?php echo nl2br(sanitize($product['description'] ?: 'No description provided by the seller.')); ?>
                </div>
            </div>
        </div>

        <!-- Purchase Info (Right Column - 5/12) -->
        <div class="lg:col-span-5">
            <div class="sticky top-24">
                <div class="mb-8">
                    <h1 class="mb-4" style="font-size: 3rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em;"><?php echo sanitize($product['title']); ?></h1>
                    <div class="flex items-center gap-4">
                        <span style="font-size: 2.75rem; font-weight: 900; color: var(--primary);"><?php echo formatPrice($product['price']); ?></span>
                    </div>
                </div>

                <!-- Seller Trust Card -->
                <div class="card p-8 mb-8" style="border-radius: 2rem; border: 1px solid var(--border-light); background: linear-gradient(to bottom right, #ffffff, #f9fafb);">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div style="width: 64px; height: 64px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.75rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
                                <?php echo strtoupper(substr($product['seller_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 class="mb-1" style="font-size: 1.25rem;">@<?php echo sanitize($product['seller_name']); ?></h4>
                                <div class="flex items-center gap-2 text-sm">
                                    <span style="color: #f59e0b; font-weight: 700;">★ <?php echo $rating['avg']; ?></span>
                                    <span class="text-muted">(<?php echo $rating['count']; ?> reviews)</span>
                                </div>
                            </div>
                        </div>
                        <a href="profile.php?id=<?php echo $product['seller_id']; ?>" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-full);">Visit Store</a>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-2xl flex items-start gap-3" style="background: var(--primary-light); color: var(--primary-hover); font-size: 0.9rem;">
                        <svg style="width: 20px; height: 20px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="mb-0">Member since <?php echo formatJoinDate($product['seller_since']); ?>. Verified campus student.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <a href="messages.php?other_user_id=<?php echo $product['seller_id']; ?>&product_id=<?php echo $product['id']; ?>" class="btn btn-primary w-full py-5 text-xl" style="border-radius: var(--radius-full); box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);">
                        Message Seller
                    </a>
                    
                    <div class="flex gap-4">
                        <form action="<?php echo BASE_URL; ?>actions/toggle_wishlist.php" method="POST" class="flex-grow">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <?php 
                                $isSaved = false;
                                if (isLoggedIn()) {
                                    $stmt = $pdo->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ?");
                                    $stmt->execute([currentUserId(), $product['id']]);
                                    $isSaved = (bool) $stmt->fetch();
                                }
                            ?>
                            <button type="submit" 
                                    class="btn <?php echo $isSaved ? 'btn-danger' : 'btn-secondary'; ?> w-full flex items-center justify-center gap-2 py-4 heart-btn" id="heart-<?php echo $product['id']; ?>" style="border-radius: var(--radius-full);">
                                <svg style="width: 24px; height: 24px;" fill="<?php echo $isSaved ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span><?php echo $isSaved ? 'Saved in Wishlist' : 'Save for later'; ?></span>
                            </button>
                        </form>
                        
                        <button class="btn btn-secondary py-4 px-6" style="border-radius: var(--radius-full);" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');">
                            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 100-2.684 3 3 0 000 2.684zm0 12.684a3 3 0 100-2.684 3 3 0 000 2.684z"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="mt-12 grid grid-cols-2 gap-6">
                    <div class="p-4 rounded-2xl bg-white border border-light flex flex-col items-center text-center">
                        <div class="text-primary mb-2">
                            <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622l-.382-3.040z"></path></svg>
                        </div>
                        <strong class="text-sm">Safe Transaction</strong>
                        <span class="text-muted small">In-person exchange</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-light flex flex-col items-center text-center">
                        <div class="text-primary mb-2">
                            <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <strong class="text-sm">Fast Response</strong>
                        <span class="text-muted small">Active seller</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.active-heart svg {
    color: var(--accent);
    fill: var(--accent);
}
.active-heart {
    background: #fff1f2 !important;
    border-color: #fecdd3 !important;
}
.hover-scale:hover {
    transform: scale(1.05);
}
</style>

<script src="../public/js/wishlist.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof updateWishlistUI === 'function') {
        updateWishlistUI();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
