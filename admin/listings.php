<?php
// admin/listings.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Auth Check (Admin Only)
if (!isAdmin()) {
    setFlash('error', 'Unauthorized access.');
    redirect('../index.php');
}

$pageTitle = "Manage Listings";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Listing deleted.');
    } elseif ($_GET['action'] === 'feature') {
        $stmt = $pdo->prepare("UPDATE products SET is_featured = 1 WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Listing featured.');
    } elseif ($_GET['action'] === 'unfeature') {
        $stmt = $pdo->prepare("UPDATE products SET is_featured = 0 WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Listing unfeatured.');
    }
    redirect('listings.php');
}

// Fetch Listings
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, u.username as seller_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
$listings = $stmt->fetchAll();
?>

<div class="container mt-8 mb-16">
    <div class="flex justify-between items-end mb-8">
        <div>
            <div class="admin-breadcrumb mb-2"><a href="index.php">Dashboard</a> › Listings</div>
            <h1 class="mb-0 gradient-text">Listing Management</h1>
        </div>
        <div class="badge" style="background: var(--primary-light); color: var(--primary-hover); font-size: 0.9rem; padding: 0.5rem 1rem;"><?php echo count($listings); ?> Total Listings</div>
    </div>

    <div class="glass-panel table-responsive" style="border-radius: var(--radius-lg); overflow: hidden; border: 1px solid rgba(0,0,0,0.05); box-shadow: var(--shadow-md);">
        <table class="table w-full text-left" style="border-collapse: collapse; margin: 0;">
            <thead>
                <tr style="background: rgba(248, 250, 252, 0.8);">
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);">Item Name</th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);">Seller</th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);">Category</th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);">Price</th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);">Condition</th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider text-right" style="border-bottom: 2px solid var(--border-light);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $item): ?>
                    <tr style="transition: background 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.02)'" onmouseout="this.style.background='transparent'">
                        <td class="p-4" style="border-bottom: 1px solid var(--border-light);">
                            <div class="flex items-center gap-3">
                                <div class="font-bold flex items-center gap-2">
                                    <?php echo sanitize($item['title']); ?>
                                </div>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 0.7rem; padding: 0.2rem 0.5rem;"><span class="animate-pulse inline-block mr-1">⭐</span>Featured</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">ID #<?php echo $item['id']; ?></div>
                        </td>
                        <td class="p-4 font-medium" style="border-bottom: 1px solid var(--border-light); color: var(--primary);">@<?php echo sanitize($item['seller_name']); ?></td>
                        <td class="p-4" style="border-bottom: 1px solid var(--border-light);"><span class="badge" style="background: var(--bg-main); color: var(--text-muted); border: 1px solid var(--border-light);"><?php echo sanitize($item['category_name']); ?></span></td>
                        <td class="p-4 font-bold text-main" style="border-bottom: 1px solid var(--border-light); font-size: 1.1rem;"><?php echo formatPrice($item['price']); ?></td>
                        <td class="p-4" style="border-bottom: 1px solid var(--border-light);">
                            <?php $badge = conditionBadge($item['condition']); ?>
                            <span class="badge <?php echo $badge['class']; ?> shadow-sm"><?php echo $badge['label']; ?></span>
                        </td>
                        <td class="p-4 text-right" style="border-bottom: 1px solid var(--border-light);">
                            <div class="flex justify-end gap-2">
                                <?php if ($item['is_featured']): ?>
                                    <a href="?action=unfeature&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm hover-scale shadow-sm" style="border-radius: var(--radius-full);" title="Unfeature">⭐ UNFEAT</a>
                                <?php else: ?>
                                    <a href="?action=feature&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm hover-scale shadow-sm" style="border-radius: var(--radius-full);" title="Feature">☆ FEAT</a>
                                <?php endif; ?>
                                <a href="../pages/product.php?id=<?php echo $item['id']; ?>" target="_blank" class="btn btn-primary btn-sm hover-scale shadow-sm" style="border-radius: var(--radius-full);">View</a>
                                <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm hover-scale shadow-sm" style="border-radius: var(--radius-full);" onclick="return confirm('Delete this listing permanently?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($listings)): ?>
            <div class="text-center p-8 text-muted">
                <span class="text-4xl mb-4 block">📭</span>
                No listings available on the platform.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
