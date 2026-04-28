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

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="admin-breadcrumb"><a href="index.php">Dashboard</a> › Listings</div>
            <h1>Listing Management</h1>
        </div>
        <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 1rem;"><?php echo count($listings); ?> Total Listings</span>
    </div>

    <div class="card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Condition</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $item): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                                <?php echo sanitize($item['title']); ?>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge badge-warning" style="font-size: 0.7rem;">⭐ Featured</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">ID #<?php echo $item['id']; ?></div>
                        </td>
                        <td>@<?php echo sanitize($item['seller_name']); ?></td>
                        <td><span class="badge badge-secondary"><?php echo sanitize($item['category_name']); ?></span></td>
                        <td style="font-weight: 700; color: var(--primary);"><?php echo formatPrice($item['price']); ?></td>
                        <td>
                            <?php $badge = conditionBadge($item['condition']); ?>
                            <span class="badge <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <?php if ($item['is_featured']): ?>
                                    <a href="?action=unfeature&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" title="Unfeature">★</a>
                                <?php else: ?>
                                    <a href="?action=feature&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" title="Feature">☆</a>
                                <?php endif; ?>
                                <a href="../pages/product.php?id=<?php echo $item['id']; ?>" target="_blank" class="btn btn-secondary btn-sm">View</a>
                                <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this listing permanently?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
