<?php
// admin/categories.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php';

requireAdmin();

$pageTitle = "Manage Categories";

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $slug = sanitize(strtolower(str_replace(' ', '-', $name)));

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        setFlash('success', "Category '$name' added successfully.");
    } else {
        setFlash('error', "Category name cannot be empty.");
    }
    redirect(BASE_URL . '/admin/categories.php');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="admin-breadcrumb"><a href="<?php echo BASE_URL; ?>admin/index.php">Dashboard</a> › Categories</div>
            <h1>Marketplace Categories</h1>
        </div>
        <button onclick="document.getElementById('add-category-card').scrollIntoView({behavior:'smooth'})" class="btn btn-primary">
            + Add Category
        </button>
    </div>

    <div class="admin-two-col">

        <!-- Categories list -->
        <div class="card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Category Name</th>
                            <th>URL Slug</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="3">
                                <div class="admin-empty">
                                    <span class="admin-empty-icon">🗂️</span>
                                    No categories yet. Add the first one →
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="muted">#<?php echo $cat['id']; ?></td>
                            <td style="font-weight: 700;"><?php echo sanitize($cat['name']); ?></td>
                            <td><code style="background: var(--bg-main); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.82rem;"><?php echo sanitize($cat['slug']); ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add form -->
        <div id="add-category-card">
            <div class="card">
                <div class="card-header">
                    <h3 style="margin-bottom: 0.25rem;">Add New Category</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Slugs are auto-generated from the name.</p>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Electronics, Books" required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary" style="width: 100%;">Save Category</button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 1rem; padding: 1rem 1.25rem; background: var(--primary-light); border: 1px solid #c7d2fe; border-radius: var(--radius-md);">
                <p style="font-size: 0.85rem; color: var(--primary-hover); margin: 0;">
                    💡 <strong>Tip:</strong> Categories help students browse and filter listings by topic.
                </p>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>