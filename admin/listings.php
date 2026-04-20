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

<div class="container mt-8">
    <div class="flex justify-between items-center mb-8">
        <h1>Listing Management</h1>
        <div class="badge badge-info"><?php echo count($listings); ?> Total Listings</div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4">Item</th>
                    <th class="p-4">Seller</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $item): ?>
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="font-bold"><?php echo sanitize($item['title']); ?></div>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge badge-warning text-xs">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted small">ID: #<?php echo $item['id']; ?></div>
                        </td>
                        <td class="p-4">@<?php echo sanitize($item['seller_name']); ?></td>
                        <td class="p-4"><span class="badge badge-secondary"><?php echo sanitize($item['category_name']); ?></span></td>
                        <td class="p-4 font-bold text-primary"><?php echo formatPrice($item['price']); ?></td>
                        <td class="p-4">
                            <?php $badge = conditionBadge($item['condition']); ?>
                            <span class="badge <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <?php if ($item['is_featured']): ?>
                                    <a href="?action=unfeature&id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" title="Unfeature">⭐</a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
