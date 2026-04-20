<?php
// pages/search.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

$query = sanitize($_GET['q'] ?? '');
$pageTitle = "Search Results: " . $query;

$results = [];
if ($query) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, i.image_path, u.username as seller_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN users u ON p.user_id = u.id
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1
        WHERE (p.title LIKE :q OR p.description LIKE :q OR c.name LIKE :q)
        AND p.status = 'active'
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([':q' => "%$query%"]);
    $results = $stmt->fetchAll();
}
?>

<div class="container mt-12 mb-20">
    <div class="mb-8">
        <h1 class="mb-2">Search Results</h1>
        <p class="text-muted">Showing results for "<strong><?php echo $query; ?></strong>" — <?php echo count($results); ?> items found.</p>
    </div>

    <?php if (empty($results)): ?>
        <div class="card p-16 text-center">
            <div class="text-4xl mb-4">🔦</div>
            <h3>No items matched your search</h3>
            <p class="text-muted">Try using different keywords or broader terms.</p>
            <a href="browse.php" class="btn btn-primary mt-6">Browse All Items</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($results as $prod): ?>
                <a href="product.php?id=<?php echo $prod['id']; ?>" class="card card-hover">
                    <div style="height: 200px; background: var(--bg-main); overflow: hidden; position: relative;">
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
                            <span class="text-muted small">@<?php echo sanitize($prod['seller_name']); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
