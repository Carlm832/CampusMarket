<?php
require_once '../includes/bootstrap.php';

$search = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$condition = $_GET['condition'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Filters and sorting logic
$params = [];
$sql = "SELECT p.*, c.name as category_name, u.username as seller_name, i.image_path
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1
        WHERE p.status = 'active'";

if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}
if ($condition) {
    $sql .= " AND p.condition = ?";
    $params[] = $condition;
}

switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    default: $sql .= " ORDER BY p.created_at DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = "Browse Marketplace";
include '../includes/header.php';
?>

<div class="mt-8 mb-20">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="mb-1">Marketplace</h1>
            <p class="text-muted">Found <?php echo count($products); ?> items for you.</p>
        </div>
        <?php if ($search): ?>
            <div class="badge badge-like-new p-2 px-4">Search: "<?php echo sanitize($search); ?>"</div>
        <?php endif; ?>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside style="width: 100%; max-width: 280px; flex-shrink: 0;">
            <div class="card p-6" style="border-radius: var(--radius-lg); border: 1px solid var(--border-light); background: var(--bg-surface); position: sticky; top: 100px;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem;">Filter Listings</h3>
                
                <form action="browse.php" method="GET" class="flex flex-col gap-5">
                    <?php if ($search): ?>
                        <input type="hidden" name="q" value="<?php echo sanitize($search); ?>">
                    <?php endif; ?>

                    <div class="form-group mb-0">
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Category</label>
                        <select name="category" class="form-control" onchange="this.form.submit()" style="font-size: 0.95rem;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Condition</label>
                        <select name="condition" class="form-control" onchange="this.form.submit()" style="font-size: 0.95rem;">
                            <option value="">Any Condition</option>
                            <option value="new" <?php echo $condition == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="like_new" <?php echo $condition == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                            <option value="used" <?php echo $condition == 'used' ? 'selected' : ''; ?>>Used</option>
                            <option value="poor" <?php echo $condition == 'poor' ? 'selected' : ''; ?>>Poor</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Sort By</label>
                        <select name="sort" class="form-control" onchange="this.form.submit()" style="font-size: 0.95rem;">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>

                    <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                        <a href="browse.php" class="btn btn-secondary btn-sm w-full">Clear Filters</a>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Product Grid -->
        <main class="flex-grow">
            <?php if (empty($products)): ?>
                <div class="card p-12 text-center" style="background: var(--bg-main); border: 2px dashed var(--border-light);">
                    <p class="text-muted">No products found matching your filters.</p>
                    <a href="browse.php" class="btn btn-primary btn-sm mt-4">Reset Browse</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    foreach ($products as $prod) {
                        include '../includes/product_card_template.php';
                    } 
                    ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
