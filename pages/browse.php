<?php
require_once '../includes/bootstrap.php';

$search = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$condition = $_GET['condition'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
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
if ($minPrice) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
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

<div class="min-h-screen pt-8 pb-16 relative">
    <!-- Background Accents -->
    <div style="position: absolute; top: -5%; left: -5%; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>
    <div style="position: absolute; top: 15%; right: -5%; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(236,72,153,0.06) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>

    <div class="px-8 lg:px-12 w-full">
        <!-- Browse Header -->
        <div class="mb-10 text-center lg:text-left flex flex-col lg:flex-row justify-between items-center gap-6">
            <div>
                <h1 class="font-bold text-4xl mb-2 gradient-text" style="background: linear-gradient(135deg, var(--text-main), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Discover Great Finds</h1>
                <p class="text-muted text-lg">Browse items from students around your campus</p>
            </div>
            
            <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-auto">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                    <input type="text" name="q" value="<?php echo sanitize($search); ?>" placeholder="Search items..." class="premium-input w-full md:w-64" style="padding: 0.6rem 1rem 0.6rem 2.5rem; border-radius: var(--radius-full);">
                </div>
                <button type="submit" class="btn btn-primary hover-scale shadow-md w-full sm:w-auto" style="border-radius: var(--radius-full); padding: 0.6rem 1.5rem;">Search</button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            
            <!-- Sidebar Filters -->
            <aside class="lg:col-span-1">
                <div class="glass-panel p-6 sticky top-24" style="border-radius: var(--radius-xl); box-shadow: var(--shadow-md);">
                    <div class="flex justify-between items-center mb-6 border-b pb-4 shrink-0">
                        <h3 class="mb-0 font-bold flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg> 
                            Filters
                        </h3>
                        <a href="browse.php" class="text-muted small hover:text-primary transition-colors font-medium">Clear all</a>
                    </div>

                    <form method="GET" action="browse.php">
                        <?php if($search): ?>
                            <input type="hidden" name="q" value="<?php echo sanitize($search); ?>">
                        <?php endif; ?>
                        
                        <fieldset class="mb-6">
                            <legend class="block mb-3 font-bold text-main" style="font-size: 0.95rem;">Category</legend>
                            <div class="relative">
                                <select name="category" class="w-full premium-input bg-white" style="appearance: none; padding: 0.6rem 2.5rem 0.6rem 1rem;" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo sanitize($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; display: flex; align-items: center;">
                                    <svg style="width: 16px; height: 16px; color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Price -->
                        <fieldset class="mb-6">
                            <legend class="block mb-3 font-bold text-main" style="font-size: 0.95rem;">Price Range</legend>
                            <div class="flex gap-3 items-center">
                                <div class="relative w-full">
                                    <span class="absolute left-3 text-muted" style="top: 50%; transform: translateY(-50%); text-align: center;">₺</span>
                                    <input type="number" name="min_price" placeholder="Min" value="<?php echo sanitize($minPrice); ?>" class="w-full premium-input text-center" style="padding: 0.5rem 0.5rem 0.5rem 1.2rem;">
                                </div>
                                <span class="text-muted font-bold">-</span>
                                <div class="relative w-full">
                                    <span class="absolute left-3 text-muted" style="top: 50%; transform: translateY(-50%); text-align: center;">₺</span>
                                    <input type="number" name="max_price" placeholder="Max" value="<?php echo sanitize($maxPrice); ?>" class="w-full premium-input text-center" style="padding: 0.5rem 0.5rem 0.5rem 1.2rem;">
                                </div>
                            </div>
                        </fieldset>

                        <!-- Condition -->
                        <fieldset class="mb-8">
                            <legend class="block mb-3 font-bold text-main" style="font-size: 0.95rem;">Condition</legend>
                            <div class="flex flex-col gap-2">
                                <?php 
                                $conditions = ['' => 'Any Condition', 'new' => 'New', 'like_new' => 'Like New', 'used' => 'Used', 'poor' => 'Poor'];
                                foreach ($conditions as $val => $label): 
                                ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="condition" value="<?php echo $val; ?>" <?php echo $condition == $val ? 'checked' : ''; ?> class="accent-primary" style="width: 1rem; height: 1rem;" onchange="this.form.submit()">
                                    <span class="text-main group-hover:text-primary transition-colors"><?php echo $label; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn btn-primary w-full hover-scale shadow-sm" style="border-radius: var(--radius-md);">Apply Filters</button>
                    </form>
                </div>
            </aside>

            <!-- Results -->
            <main class="lg:col-span-4">
                <div class="glass-panel px-6 py-4 mb-8 flex flex-col sm:flex-row justify-between items-center gap-4" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); background: var(--bg-surface);">
                    <div class="font-medium text-main"><strong class="text-primary"><?php echo count($products); ?></strong> items matching your criteria</div>
                    <form method="GET" class="flex items-center gap-3">
                        <?php if($category) echo '<input type="hidden" name="category" value="'.$category.'">'; ?>
                        <?php if($minPrice) echo '<input type="hidden" name="min_price" value="'.$minPrice.'">'; ?>
                        <?php if($maxPrice) echo '<input type="hidden" name="max_price" value="'.$maxPrice.'">'; ?>
                        <?php if($condition) echo '<input type="hidden" name="condition" value="'.$condition.'">'; ?>
                        <?php if($search) echo '<input type="hidden" name="q" value="'.sanitize($search).'">'; ?>
                        <span class="text-muted font-bold small uppercase tracking-wider" style="font-size: 0.75rem;">Sort by:</span>
                        <div class="relative">
                            <select name="sort" class="premium-input bg-white font-medium" style="appearance: none; padding: 0.4rem 2.5rem 0.4rem 1rem; border-radius: var(--radius-md); font-size: 0.9rem;" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                            <div style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; display: flex; align-items: center;">
                                <svg style="width: 14px; height: 14px; color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if (empty($products)): ?>
                    <div class="glass-panel p-16 text-center shadow-sm" style="border-radius: var(--radius-xl); border: 2px dashed rgba(0,0,0,0.05);">
                        <div class="mb-4 text-6xl opacity-30">🔍</div>
                        <h3 class="font-bold text-main text-2xl mb-2">No items found</h3>
                        <p class="text-muted text-lg max-w-md mx-auto">We couldn't find any items matching your current filters. Try adjusting your search criteria or clearing filters.</p>
                        <a href="browse.php" class="btn btn-primary mt-6 hover-scale shadow-sm" style="border-radius: var(--radius-full);">Clear All Filters</a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($products as $prod): ?>
                            <?php include '../includes/product_card_template.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
