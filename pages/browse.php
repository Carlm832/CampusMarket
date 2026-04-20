<?php
// pages/browse.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

$pageTitle = "Browse Marketplace";

// Get Filters
$catFilter   = $_GET['category'] ?? '';
$minPrice    = $_GET['min_price'] ?? '';
$maxPrice    = $_GET['max_price'] ?? '';
$condFilter  = $_GET['condition'] ?? '';
$sort        = $_GET['sort'] ?? 'latest';

// Fetch Categories for Sidebar
$categories = getTopCategories($pdo);

// Build SQL Query
$sql = "SELECT p.*, c.name as category_name, i.image_path, u.username as seller_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN users u ON p.user_id = u.id
        LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1
        WHERE p.status = 'active'";

$params = [];

if ($catFilter) {
    if (is_numeric($catFilter)) {
        $sql .= " AND p.category_id = :cat";
        $params[':cat'] = $catFilter;
    } else {
        $sql .= " AND c.name = :cat";
        $params[':cat'] = $catFilter;
    }
}

if ($minPrice) {
    $sql .= " AND p.price >= :min";
    $params[':min'] = $minPrice;
}

if ($maxPrice) {
    $sql .= " AND p.price <= :max";
    $params[':max'] = $maxPrice;
}

if ($condFilter) {
    $sql .= " AND p.condition = :cond";
    $params[':cond'] = $condFilter;
}

// Sorting
switch ($sort) {
    case 'price_asc':  $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    default:           $sql .= " ORDER BY p.created_at DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="container mt-8 mb-16">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Sidebar Filters -->
        <aside class="lg:col-span-1">
            <div class="card p-6 sticky top-24">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="mb-0">Filters</h3>
                    <a href="browse.php" class="text-muted small">Clear all</a>
                </div>

                <form method="GET" action="browse.php">
                    <!-- Category -->
                    <div class="mb-6">
                        <label class="block mb-3 font-bold">Category</label>
                        <select name="category" class="w-full" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $catFilter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($cat['name']); ?> (<?php echo $cat['product_count']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="mb-6">
                        <label class="block mb-3 font-bold">Price Range</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo sanitize($minPrice); ?>" class="w-full">
                            <span class="text-muted">-</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo sanitize($maxPrice); ?>" class="w-full">
                        </div>
                    </div>

                    <!-- Condition -->
                    <div class="mb-8">
                        <label class="block mb-3 font-bold">Condition</label>
                        <select name="condition" class="w-full" onchange="this.form.submit()">
                            <option value="">Any Condition</option>
                            <option value="new" <?php echo $condFilter == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="like_new" <?php echo $condFilter == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                            <option value="used" <?php echo $condFilter == 'used' ? 'selected' : ''; ?>>Used</option>
                            <option value="fair" <?php echo $condFilter == 'fair' ? 'selected' : ''; ?>>Fair</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">Apply Filters</button>
                </form>
            </div>
        </aside>

        <!-- Results -->
        <main class="lg:col-span-3">
            <div class="flex justify-between items-center mb-8">
                <h2 class="mb-0"><?php echo count($products); ?> Items Found</h2>
                <form method="GET" class="flex items-center gap-2">
                    <?php if($catFilter) echo '<input type="hidden" name="category" value="'.$catFilter.'">'; ?>
                    <?php if($condFilter) echo '<input type="hidden" name="condition" value="'.$condFilter.'">'; ?>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Latest First</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
                <div class="card p-12 text-center">
                    <div class="mb-4" style="font-size: 3rem;">🔍</div>
                    <h3>No items found</h3>
                    <p class="text-muted">Try adjusting your filters or search keywords.</p>
                    <a href="browse.php" class="btn btn-secondary mt-4">Reset Browse</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $prod): ?>
                        <a href="product.php?id=<?php echo $prod['id']; ?>" class="card card-hover">
                            <div style="height: 180px; background: var(--bg-main); overflow: hidden; position: relative;">
                                <?php if ($prod['image_path']): ?>
                                    <img src="<?php echo BASE_URL; ?>/public/<?php echo $prod['image_path']; ?>" alt="<?php echo sanitize($prod['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-center; color: #999;">No Image</div>
                                <?php endif; ?>
                                <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                                    <?php $badge = conditionBadge($prod['condition']); ?>
                                    <span class="badge <?php echo $badge['class']; ?> shadow-sm"><?php echo $badge['label']; ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-1"><?php echo sanitize($prod['category_name']); ?></p>
                                <h4 class="mb-2" style="font-size: 1rem; line-height: 1.4;"><?php echo sanitize($prod['title']); ?></h4>
                                <div class="flex justify-between items-end mt-4">
                                    <span style="font-weight: 800; color: var(--text-main); font-size: 1.15rem;"><?php echo formatPrice($prod['price']); ?></span>
                                    <span class="text-muted small">@<?php echo sanitize($prod['seller_name']); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
