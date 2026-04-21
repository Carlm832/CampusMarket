<?php
// pages/wishlist.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch wishlist items
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, i.image_path
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlistItems = $stmt->fetchAll();

$pageTitle = "My Wishlist";
?>

<div class="container" style="margin-top: 4rem; margin-bottom: 8rem; max-width: 1400px; padding: 0 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 2rem;">
        <div>
            <h1 style="margin-bottom: 0.5rem; font-size: 2.5rem; font-weight: 800; color: #1e293b;">My Wishlist</h1>
            <p style="color: #64748b; font-size: 1.2rem; margin-bottom: 0;">Items you've saved to look at later.</p>
        </div>
        <div style="background: #eff6ff; color: #2563eb; padding: 0.75rem 1.5rem; border-radius: 1rem; font-weight: 700; font-size: 1.1rem; border: 1px solid #dbeafe;">
            <span id="wishlist-count"><?php echo count($wishlistItems); ?></span> items saved
        </div>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div class="card" style="padding: 6rem; text-align: center; border-radius: 2rem; background: white; border: 2px dashed #e2e8f0; box-shadow: none;">
            <div style="font-size: 5rem; margin-bottom: 2rem;">✨</div>
            <h2 style="font-weight: 800; color: #1e293b; margin-bottom: 1rem;">Your wishlist is empty</h2>
            <p style="color: #64748b; font-size: 1.2rem; margin-bottom: 3rem; max-width: 500px; margin-left: auto; margin-right: auto;">Explore our campus collection and save items you love here to find them easily later.</p>
            <a href="browse.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 1rem; font-weight: 700; background: #2563eb; color: white; border: none; text-decoration: none;">Discover Items</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 4rem;">
            <?php foreach ($wishlistItems as $prod): ?>
                <?php 
                // We use a custom version of the card for wishlist to include the remove button prominently
                ?>
                <div style="position: relative;">
                    <?php include __DIR__ . '/../includes/product_card_template.php'; ?>
                    
                    <!-- Remove Button (Absolute Overlay for Wishlist) -->
                    <form action="<?php echo BASE_URL; ?>actions/toggle_wishlist.php" method="POST" style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 10;">
                        <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                        <button type="submit" style="width: 40px; height: 40px; background: white; border-radius: 50%; border: 1px solid #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: all 0.2s;" onmouseover="this.style.backgroundColor='#ef4444'; this.style.color='white';" onmouseout="this.style.backgroundColor='white'; this.style.color='#ef4444';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
