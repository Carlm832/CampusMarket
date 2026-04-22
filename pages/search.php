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
        WHERE (p.title LIKE :q1 OR p.description LIKE :q2 OR c.name LIKE :q3)
        AND p.status = 'active'
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([
        ':q1' => "%$query%",
        ':q2' => "%$query%",
        ':q3' => "%$query%"
    ]);
    $results = $stmt->fetchAll();
}
?>

<div class="container" style="margin-top: 4rem; margin-bottom: 8rem; max-width: 1400px; padding: 0 2rem;">
    <div style="display: flex; align-items: flex-start; gap: 80px;">
        
        <!-- Sidebar Filters -->
        <aside style="width: 300px; flex-shrink: 0;">
            <div class="card" style="padding: 2.5rem; position: sticky; top: 6rem; border-radius: 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.05); background: white;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
                    <h3 style="margin-bottom: 0; font-size: 1.4rem; font-weight: 800; color: #1e293b;">Filters</h3>
                    <a href="search.php?q=<?php echo urlencode($query); ?>" style="font-weight: 600; text-decoration: none; color: #3b82f6; font-size: 0.875rem;">Clear all</a>
                </div>
                
                <form method="GET" action="search.php" style="display: flex; flex-direction: column; gap: 2.5rem;">
                    <input type="hidden" name="q" value="<?php echo sanitize($query); ?>">
                    <!-- Category -->
                    <div>
                        <label style="font-weight: 700; font-size: 1rem; color: #1e293b; margin-bottom: 0.75rem; display: block;">Category</label>
                        <select name="category" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; width: 100%; height: auto;" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php 
                            $categories = getTopCategories($pdo);
                            foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo sanitize($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label style="font-weight: 700; font-size: 1rem; color: #1e293b; margin-bottom: 0.75rem; display: block;">Price Range</label>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <input type="number" name="min_price" placeholder="Min" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; flex: 1; height: auto;">
                            <span style="color: #cbd5e1;">—</span>
                            <input type="number" name="max_price" placeholder="Max" class="form-control" style="border-radius: 0.8rem; border: 1px solid #e2e8f0; padding: 0.8rem; flex: 1; height: auto;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="border-radius: 0.8rem; background: #2563eb; border: none; font-weight: 700; font-size: 1rem; padding: 1.15rem; color: white; cursor: pointer;">Apply Filters</button>
                </form>
            </div>
        </aside>

        <!-- Results Content -->
        <main style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5rem; gap: 2rem; width: 100%;">
                <div>
                    <h1 style="margin-bottom: 0.75rem; font-size: 2.25rem; font-weight: 800; color: #1e293b; white-space: nowrap;">Search Results</h1>
                    <p style="color: #64748b; margin-bottom: 0; font-size: 1.2rem; white-space: nowrap;">Showing results for "<strong><?php echo sanitize($query); ?></strong>" — <span style="color: #2563eb; font-weight: 700;"><?php echo count($results); ?></span> items found.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-shrink: 0;">
                    <label style="font-weight: 600; font-size: 1.1rem; color: #64748b; white-space: nowrap;">Sort by:</label>
                    <select class="form-control" style="padding: 0.75rem 2.5rem 0.75rem 1.25rem; width: 220px; font-size: 1.1rem; border-radius: 0.8rem; border: 1px solid #e2e8f0; background-color: white; color: #1e293b; font-weight: 500; cursor: pointer;">
                        <option>Latest First</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                    </select>
                </div>
            </div>

            <?php if (empty($results)): ?>
                <div class="card" style="padding: 4rem; text-align: center; border-radius: 1.5rem; background: white; border: 1px solid #f1f5f9; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.05);">
                    <div style="margin-bottom: 1rem; font-size: 3.5rem;">🔦</div>
                    <h3 style="font-weight: 700; font-size: 1.5rem;">No items matched your search</h3>
                    <p style="color: #64748b; font-size: 1.1rem;">Try using different keywords or broader terms.</p>
                    <a href="browse.php" class="btn btn-primary" style="border-radius: 0.8rem; padding: 0.8rem 2rem; font-weight: 600; background: #2563eb; color: white; border: none; margin-top: 2rem; display: inline-block;">Browse All Items</a>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 3.5rem;">
                    <?php foreach ($results as $prod): ?>
                        <?php include __DIR__ . '/../includes/product_card_template.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
