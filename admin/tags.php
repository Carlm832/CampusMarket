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

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="admin-breadcrumb"><a href="<?php echo BASE_URL; ?>admin/index.php">Dashboard</a> › Tags</div>
            <h1>Manage Interest Tags</h1>
        </div>
        <button onclick="document.getElementById('add-tag-card').scrollIntoView({behavior:'smooth'})" class="btn btn-primary">
            + Create Tag
        </button>
    </div>

    <div class="admin-two-col">

        <!-- Tags list -->
        <div class="card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tag</th>
                            <th>System Slug</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tags)): ?>
                        <tr>
                            <td colspan="3">
                                <div class="admin-empty">
                                    <span class="admin-empty-icon">🏷️</span>
                                    No tags yet. Create the first one →
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td class="muted">#<?php echo $tag['id']; ?></td>
                            <td>
                                <span class="badge badge-secondary" style="font-size: 0.85rem;">#<?php echo sanitize($tag['name']); ?></span>
                            </td>
                            <td><code style="background: var(--bg-main); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.82rem;"><?php echo sanitize($tag['slug']); ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add form -->
        <div id="add-tag-card">
            <div class="card">
                <div class="card-header">
                    <h3 style="margin-bottom: 0.25rem;">Quick Add Tag</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Searchable keywords for products.</p>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Tag Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Vintage, Study, Tech" required>
                        </div>
                        <button type="submit" name="add_tag" class="btn btn-primary" style="width: 100%;">Save Tag</button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 1rem; padding: 1rem 1.25rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid var(--secondary); border-radius: var(--radius-md);">
                <h4 style="font-size: 0.9rem; margin-bottom: 0.35rem; color: #166534;">Why use tags?</h4>
                <p style="font-size: 0.82rem; color: #15803d; margin: 0;">
                    Tags help students filter beyond categories — e.g. a "Desk" in Furniture tagged <code>#study</code> shows up in study-related searches.
                </p>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
