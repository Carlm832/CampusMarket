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
            <?php if (isLoggedIn()): ?>
            <div class="flex gap-2">
                <a href="pages/create_listing.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: white;">Sell an Item</a>
                <a href="pages/wishlist.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: white; display: flex; align-items: center; gap: 0.5rem;">
                    <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    My Wishlist
                </a>
            </div>
            <?php else: ?>
                <a href="pages/register.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: white;">Join to Sell</a>
            <?php endif; ?>
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
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php 
            $catIcons = [
                1 => '💻', 2 => '📚', 3 => '🪑', 4 => '👕', 5 => '🍳', 
                6 => '🧴', 7 => '🍕', 8 => '✏️', 9 => '🏠', 10 => '🚲'
            ];
            foreach ($topCategories as $cat): ?>
                <a href="pages/browse.php?category=<?php echo $cat['id']; ?>" class="card card-hover p-4 flex flex-col items-center justify-center text-center">
                    <div style="background: var(--bg-main); width: 48px; height: 48px; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; font-size: 1.5rem;">
                        <?php echo $catIcons[$cat['id']] ?? '🏷️'; ?>
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
                    <?php if (isLoggedIn()): ?>
                        <a href="pages/create_listing.php" class="btn btn-primary">Create Listing</a>
                    <?php else: ?>
                        <a href="pages/register.php" class="btn btn-primary">Join & Sell</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($recentProducts as $prod): ?>
                    <?php include 'includes/product_card_template.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
