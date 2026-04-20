<?php
// admin/tags.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php';

requireAdmin();

$pageTitle = "Manage Tags";

// Handle Add Tag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
    $name = sanitize($_POST['name']);
    $slug = sanitize(strtolower(str_replace(' ', '-', $name)));
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        setFlash('success', "Tag '#$name' added successfully.");
    } else {
        setFlash('error', "Tag name cannot be empty.");
    }
    redirect(BASE_URL . '/admin/tags.php');
}

$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="mt-8 mb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-muted" style="font-size: 0.9rem;">Admin Dashboard</a>
                <span class="text-light">/</span>
                <span class="text-main" style="font-size: 0.9rem; font-weight: 500;">Product Tags</span>
            </div>
            <h1 class="mb-0">Manage Interest Tags</h1>
        </div>
        <button onclick="document.getElementById('add-tag-card').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">
            + Create Tag
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Tags List -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Tag Name</th>
                                <th>System Slug</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tags)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 3rem;">No tags found. Use tags to help users find specific items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td class="text-muted">#<?php echo $tag['id']; ?></td>
                                        <td>
                                            <span class="badge badge-used" style="font-size: 0.9rem;">
                                                #<?php echo sanitize($tag['name']); ?>
                                            </span>
                                        </td>
                                        <td><code style="background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: var(--radius-sm);"><?php echo sanitize($tag['slug']); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Tag Form -->
        <div id="add-tag-card">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="mb-0">Quick Add</h3>
                    <p class="text-muted small">Create searchable keywords for products.</p>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Tag Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Vintage, Study, Tech" required>
                        </div>
                        <button type="submit" name="add_tag" class="btn btn-primary w-full">Save Tag</button>
                    </form>
                </div>
            </div>

            <div class="mt-6 p-4 bg-white border" style="border-radius: var(--radius-md); border-left: 4px solid var(--secondary);">
                <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Why use tags?</h4>
                <p class="text-muted small mb-0">
                    Tags help users filter products beyond just categories. (e.g., a "Desk" in Furniture can be tagged with "#study").
                </p>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
