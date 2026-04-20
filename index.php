<?php
// index.php
require_once 'config/constants.php';
require_once 'includes/header.php';

$pageTitle = "Home";

// Data for homepage
$recentProducts = getRecentProducts($pdo, 8);
$topCategories = getTopCategories($pdo);
?>

<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 6rem 0; margin-top: -2rem; color: white; text-align: center; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
    <div class="container">
        <h1 style="color: white; font-size: 3.5rem; margin-bottom: 1.5rem;">The Campus Marketplace</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.25rem; max-width: 600px; margin: 0 auto 2.5rem;">
            Buy and sell textbooks, electronics, furniture, and more within your university community.
        </p>
        <div class="flex justify-center gap-4">
            <a href="pages/browse.php" class="btn" style="background: white; color: var(--primary);">Start Browsing</a>
            <a href="pages/create_listing.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: white;">Sell an Item</a>
        </div>
    </div>
</section>

<!-- Category Quick Access -->
<section class="mt-12">
    <div class="container">
        <div class="flex justify-between items-end mb-6">
            <h2 class="mb-0">Shop by Category</h2>
            <a href="pages/categories.php" class="text-muted" style="font-weight: 500;">View all</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach (array_slice($topCategories, 0, 6) as $cat): ?>
                <a href="pages/browse.php?category=<?php echo $cat['id']; ?>" class="card card-hover p-4 flex flex-col items-center justify-center text-center">
                    <div style="background: var(--bg-main); width: 48px; height: 48px; border-radius: var(--radius-full); display: flex; align-items: center; justify-center; margin-bottom: 0.75rem;">
                        <svg style="width: 24px; height: 24px; color: var(--primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <strong><?php echo sanitize($cat['name']); ?></strong>
                    <span class="text-muted small"><?php echo $cat['product_count']; ?> items</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Recent Products -->
<section class="mt-16 mb-16">
    <div class="container">
        <div class="flex justify-between items-end mb-8">
            <h2 class="mb-0">Recent Listings</h2>
            <a href="pages/browse.php" class="btn btn-secondary btn-sm">See everything</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (empty($recentProducts)): ?>
                <div class="col-span-full text-center py-12 bg-white rounded-lg border">
                    <p class="text-muted">No products listed yet. Be the first to sell something!</p>
                    <a href="pages/create_listing.php" class="btn btn-primary">Create Listing</a>
                </div>
            <?php else: ?>
                <?php foreach ($recentProducts as $prod): ?>
                    <a href="pages/product.php?id=<?php echo $prod['id']; ?>" class="card card-hover">
                        <div style="height: 200px; background: #eee; overflow: hidden; position: relative;">
                            <?php if ($prod['image_path']): ?>
                                <img src="<?php echo BASE_URL; ?>/public/<?php echo $prod['image_path']; ?>" alt="<?php echo sanitize($prod['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-center; color: #999;">No Image</div>
                            <?php endif; ?>
                            <div style="position: absolute; top: 0.75rem; right: 0.75rem;">
                                <?php $badge = conditionBadge($prod['condition']); ?>
                                <span class="badge <?php echo $badge['class']; ?> shadow-sm"><?php echo $badge['label']; ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-1"><?php echo sanitize($prod['category_name']); ?></p>
                            <h4 class="mb-2" style="font-size: 1.1rem; line-height: 1.4;"><?php echo sanitize($prod['title']); ?></h4>
                            <div class="flex justify-between items-center mt-4">
                                <span style="font-weight: 800; color: var(--text-main); font-size: 1.25rem;"><?php echo formatPrice($prod['price']); ?></span>
                                <span class="text-muted small">By <?php echo sanitize($prod['seller_name']); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
