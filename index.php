<?php
// index.php
require_once 'config/constants.php';
require_once 'includes/header.php';

$pageTitle = "Home";

// Data for homepage
$recentProducts = getRecentProducts($pdo, 8);
$topCategories = getTopCategories($pdo);
?>

<!-- Hero Section with Background Carousel -->
<section class="hero">
    <div class="hero-carousel">
        <div class="hero-slide active" style="background-image: url('public/images/hero/hero1.png');"></div>
        <div class="hero-slide" style="background-image: url('public/images/hero/hero2.png');"></div>
        <div class="hero-slide" style="background-image: url('public/images/hero/hero3.png');"></div>
        <div class="hero-slide" style="background-image: url('public/images/hero/hero4.png');"></div>
    </div>
    <div class="hero-overlay"></div>
    
    <div class="container">
        <h1 style="font-size: 4rem; font-weight: 800; margin-bottom: 1.5rem; text-shadow: 0 4px 12px rgba(0,0,0,0.3);">The Campus Marketplace</h1>
        <p style="font-size: 1.5rem; max-width: 700px; margin: 0 auto 3rem; font-weight: 500; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">
            The safest way to buy and sell within your university community.
        </p>
        <div class="flex justify-center gap-6">
            <a href="pages/browse.php" class="btn" style="background: white; color: var(--primary); padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 700; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);">Start Browsing</a>
            <?php if (isLoggedIn()): ?>
                <a href="pages/create_listing.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.4); color: white; padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 700; border-radius: 1rem; backdrop-filter: blur(8px);">Sell an Item</a>
            <?php else: ?>
                <a href="pages/register.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.4); color: white; padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 700; border-radius: 1rem; backdrop-filter: blur(8px);">Join to Sell</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Hero Carousel Logic
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    let currentSlide = 0;
    
    function nextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }
    
    // Change slide every 5 seconds
    setInterval(nextSlide, 5000);
});
</script>

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
