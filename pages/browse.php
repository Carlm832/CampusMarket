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

<div class="min-h-screen pt-8 pb-16 relative">
    <!-- Background Accents -->
    <div style="position: absolute; top: -5%; left: -5%; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>
    <div style="position: absolute; top: 15%; right: -5%; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(236,72,153,0.06) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>

    <div class="container">
        <!-- Browse Header -->
        <div class="mb-10 text-center lg:text-left flex flex-col lg:flex-row justify-between items-center gap-6">
            <div>
                <h1 class="font-bold text-4xl mb-2 gradient-text" style="background: linear-gradient(135deg, var(--text-main), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Discover Great Finds</h1>
                <p class="text-muted text-lg">Browse items from students around your campus</p>
            </div>
            
            <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-auto">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                    <input type="text" placeholder="Search items..." class="premium-input w-full md:w-64" style="padding: 0.6rem 1rem 0.6rem 2.5rem; border-radius: var(--radius-full);">
                </div>
                <button type="submit" class="btn btn-primary hover-scale shadow-md w-full sm:w-auto" style="border-radius: var(--radius-full); padding: 0.6rem 1.5rem;">Search</button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
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
                        <!-- Category -->
                        <fieldset class="mb-6">
                            <legend class="block mb-3 font-bold text-main" style="font-size: 0.95rem;">Category</legend>
                            <div class="relative">
                                <select name="category" class="w-full premium-input bg-white" style="appearance: none; padding: 0.6rem 2rem 0.6rem 1rem;" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $catFilter == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo sanitize($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="w-4 h-4 absolute right-3 pointer-events-none text-muted" style="top: 50%; transform: translateY(-50%);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </fieldset>

                        <!-- Price -->
                        <fieldset class="mb-6">
                            <legend class="block mb-3 font-bold text-main" style="font-size: 0.95rem;">Price Range</legend>
                            <div class="flex gap-3 items-center">
                                <div class="relative w-full">
                                    <span class="absolute left-3 text-muted" style="top: 50%; transform: translateY(-50%); text-align: center;">$</span>
                                    <input type="number" name="min_price" placeholder="Min" value="<?php echo sanitize($minPrice); ?>" class="w-full premium-input text-center" style="padding: 0.5rem 0.5rem 0.5rem 1.2rem;">
                                </div>
                                <span class="text-muted font-bold">-</span>
                                <div class="relative w-full">
                                    <span class="absolute left-3 text-muted" style="top: 50%; transform: translateY(-50%); text-align: center;">$</span>
                                    <input type="number" name="max_price" placeholder="Max" value="<?php echo sanitize($maxPrice); ?>" class="w-full premium-input text-center" style="padding: 0.5rem 0.5rem 0.5rem 1.2rem;">
                                </div>
                            </div>
                        </fieldset>

                        <!-- Condition -->
                        <fieldset class="mb-8">
                            <legend class="block mb-3 font-bold text-main" style="font-size: 0.95rem;">Condition</legend>
                            <div class="flex flex-col gap-2">
                                <?php 
                                $conditions = ['' => 'Any Condition', 'new' => 'New', 'like_new' => 'Like New', 'used' => 'Used', 'fair' => 'Fair'];
                                foreach ($conditions as $val => $label): 
                                ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="condition" value="<?php echo $val; ?>" <?php echo $condFilter == $val ? 'checked' : ''; ?> class="accent-primary" style="width: 1rem; height: 1rem;" onchange="this.form.submit()">
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
            <main class="lg:col-span-3">
                <div class="glass-panel px-6 py-4 mb-8 flex flex-col sm:flex-row justify-between items-center gap-4" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                    <div class="font-medium text-main"><strong class="text-primary"><?php echo count($products); ?></strong> items matching your criteria</div>
                    <form method="GET" class="flex items-center gap-3">
                        <?php if($catFilter) echo '<input type="hidden" name="category" value="'.$catFilter.'">'; ?>
                        <?php if($minPrice) echo '<input type="hidden" name="min_price" value="'.$minPrice.'">'; ?>
                        <?php if($maxPrice) echo '<input type="hidden" name="max_price" value="'.$maxPrice.'">'; ?>
                        <?php if($condFilter) echo '<input type="hidden" name="condition" value="'.$condFilter.'">'; ?>
                        <span class="text-muted font-bold small uppercase tracking-wider">Sort by:</span>
                        <div class="relative">
                            <select name="sort" class="premium-input bg-white font-medium" style="appearance: none; padding: 0.4rem 2rem 0.4rem 1rem; border-radius: var(--radius-md);" onchange="this.form.submit()">
                                <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Latest First</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                            <svg class="w-4 h-4 absolute right-3 pointer-events-none text-muted" style="top: 50%; transform: translateY(-50%);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($products as $prod): ?>
                            <a href="product.php?id=<?php echo $prod['id']; ?>" class="card card-hover flex flex-col h-full" style="text-decoration: none; border-radius: var(--radius-lg); overflow: hidden; background: var(--bg-surface); border: 1px solid var(--border-light);">
                                <div style="height: 220px; background: #e2e8f0; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                    <?php if ($prod['image_path']): ?>
                                        <img src="<?php echo BASE_URL; ?>/public/<?php echo $prod['image_path']; ?>" alt="<?php echo sanitize($prod['title']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: linear-gradient(135deg, var(--bg-main), var(--border-light)); color: var(--text-muted);">
                                            <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span class="text-xs font-medium">No Image</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="position: absolute; top: 0.75rem; right: 0.75rem;">
                                        <?php $badge = conditionBadge($prod['condition']); ?>
                                        <span class="badge <?php echo $badge['class']; ?> shadow-sm" style="font-size: 0.7rem; padding: 0.25rem 0.6rem; backdrop-filter: blur(4px);"><?php echo $badge['label']; ?></span>
                                    </div>
                                </div>
                                <div class="p-5 flex flex-col flex-grow">
                                    <p class="text-primary font-bold small tracking-wider uppercase mb-1" style="font-size: 0.7rem;"><?php echo sanitize($prod['category_name']); ?></p>
                                    <h4 class="mb-3 text-main font-bold" style="font-size: 1.1rem; line-height: 1.4; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo sanitize($prod['title']); ?></h4>
                                    
                                    <div class="flex justify-between items-center mt-auto pt-4 border-t border-light" style="border-color: var(--border-light);">
                                        <span style="font-weight: 800; color: var(--text-main); font-size: 1.25rem; font-family: 'Inter', sans-serif;">₺ <?php echo number_format($prod['price']); ?></span>
                                        <div class="flex items-center gap-2">
                                            <div style="min-width: 24px; min-height: 24px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: bold; padding:0.2rem;"><?php echo strtoupper(substr($prod['seller_name'],0,2)); ?></div>
                                            <span class="text-muted font-medium text-sm truncate" style="max-width: 80px;">@<?php echo sanitize($prod['seller_name']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
