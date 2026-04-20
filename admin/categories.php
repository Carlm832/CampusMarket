<?php
// admin/categories.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php'; // Ensures only admin can access

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

<div class="mt-8 mb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-muted" style="font-size: 0.9rem;">Admin Dashboard</a>
                <span class="text-light">/</span>
                <span class="text-main" style="font-size: 0.9rem; font-weight: 500;">Categories</span>
            </div>
            <h1 class="mb-0">Marketplace Categories</h1>
        </div>
        <button onclick="document.getElementById('add-category-card').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">
            + Add New Category
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Categories List -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Category Name</th>
                                <th>URL Slug</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 3rem;">No categories found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td class="text-muted">#<?php echo $cat['id']; ?></td>
                                        <td><strong><?php echo sanitize($cat['name']); ?></strong></td>
                                        <td><code style="background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: var(--radius-sm);"><?php echo sanitize($cat['slug']); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Category Form -->
        <div id="add-category-card">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="mb-0">Add New</h3>
                    <p class="text-muted small">Create a new marketplace category.</p>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Electronics, Books" required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary w-full">Save Category</button>
                    </form>
                </div>
            </div>

            <div class="card mt-6" style="background: var(--primary-light); border-color: var(--primary);">
                <div class="card-body">
                    <h4 style="color: var(--primary-hover);">Admin Tip</h4>
                    <p style="color: var(--primary-hover); font-size: 0.9rem; margin-bottom: 0;">
                        Slugs are generated automatically from the name to create clean URLs.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>