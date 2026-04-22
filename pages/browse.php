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

<div class="container" style="margin-top: 3rem; margin-bottom: 8rem; max-width: 1400px; padding: 0 2rem;">
    <div style="display: flex; align-items: flex-start; gap: 80px;">
        
        <!-- Sidebar Filters -->
        <aside style="width: 300px; flex-shrink: 0;">
            <div class="card" style="padding: 2.5rem; position: sticky; top: 6rem; border-radius: 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.05); background: white;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
                    <h3 style="margin-bottom: 0; font-size: 1.4rem; font-weight: 800; color: #1e293b;">Filters</h3>
                    <a href="browse.php" style="font-weight: 600; text-decoration: none; color: #3b82f6; font-size: 0.875rem;">Clear all</a>
                </div>
                
                <form method="GET" action="browse.php" style="display: flex; flex-direction: column; gap: 2.5rem;">
                    <!-- Category -->
                    <div>
                        <label style="font-weight: 700; font-size: 1rem; color: #1e293b; margin-bottom: 0.75rem; display: block;">Category</label>
                        <select name="category" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; width: 100%; height: auto;" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $catFilter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label style="font-weight: 700; font-size: 1rem; color: #1e293b; margin-bottom: 0.75rem; display: block;">Price Range</label>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo sanitize($minPrice); ?>" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; flex: 1; height: auto;">
                            <span style="color: #cbd5e1;">—</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo sanitize($maxPrice); ?>" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; flex: 1; height: auto;">
                        </div>
                    </div>

                    <!-- Condition -->
                    <div>
                        <label style="font-weight: 700; font-size: 1rem; color: #1e293b; margin-bottom: 0.75rem; display: block;">Condition</label>
                        <select name="condition" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; width: 100%; height: auto;" onchange="this.form.submit()">
                            <option value="">Any Condition</option>
                            <option value="new" <?php echo $condFilter == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="like_new" <?php echo $condFilter == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                            <option value="used" <?php echo $condFilter == 'used' ? 'selected' : ''; ?>>Used</option>
                            <option value="poor" <?php echo $condFilter == 'poor' ? 'selected' : ''; ?>>Poor / Fair</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="border-radius: 0.8rem; background: #2563eb; border: none; font-weight: 700; font-size: 1rem; padding: 1.15rem; color: white; cursor: pointer; transition: background 0.2s;">Apply Filters</button>
                </form>
            </div>
        </aside>

        <!-- Results -->
        <main style="flex: 1; min-width: 0;">
            <!-- Results Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; width: 100%; gap: 2rem;">
                <h2 style="margin-bottom: 0; font-size: 1.8rem; font-weight: 800; color: #1e293b; white-space: nowrap;">
                    <span style="color: #2563eb;"><?php echo count($products); ?></span> Items Found
                    <?php if ($catFilter): ?>
                        <span style="color: #64748b; font-size: 1.1rem; font-weight: 500; margin-left: 0.75rem;">in <?php echo is_numeric($catFilter) ? 'Selected Category' : sanitize($catFilter); ?></span>
                    <?php endif; ?>
                </h2>
                <form method="GET" style="display: flex; align-items: center; gap: 1.5rem; flex-shrink: 0;">
                    <?php if($catFilter) echo '<input type="hidden" name="category" value="'.sanitize($catFilter).'">'; ?>
                    <?php if($condFilter) echo '<input type="hidden" name="condition" value="'.sanitize($condFilter).'">'; ?>
                    <?php if($minPrice) echo '<input type="hidden" name="min_price" value="'.sanitize($minPrice).'">'; ?>
                    <?php if($maxPrice) echo '<input type="hidden" name="max_price" value="'.sanitize($maxPrice).'">'; ?>
                    <label style="font-weight: 600; font-size: 1rem; color: #64748b; white-space: nowrap;">Sort by:</label>
                    <select name="sort" class="form-control" style="padding: 0.75rem 2.5rem 0.75rem 1.25rem; width: 220px; font-size: 1rem; border-radius: 0.8rem; border: 1px solid #e2e8f0; background-color: white; color: #1e293b; font-weight: 500; cursor: pointer;" onchange="this.form.submit()">
                        <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Latest First</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
                <div class="card" style="padding: 5rem; text-align: center; border-radius: 1.5rem; background: white; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.05);">
                    <div style="margin-bottom: 1.5rem; font-size: 4rem;">🔍</div>
                    <h3 style="font-weight: 800; font-size: 1.75rem;">No items found</h3>
                    <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 2rem;">Try adjusting your filters or search keywords.</p>
                    <a href="browse.php" class="btn btn-secondary" style="border-radius: 0.8rem; padding: 0.8rem 2rem; font-weight: 600; text-decoration: none;">Reset Browse</a>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 3.5rem;">
                    <?php foreach ($products as $prod): ?>
                        <?php include __DIR__ . '/../includes/product_card_template.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
    </div>
</div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
