<?php
// pages/categories.php
require_once '../config/constants.php';
require_once '../includes/header.php';

$pageTitle = "Browse Categories";

// Fetch all categories from the database
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<h1><?php echo $pageTitle; ?></h1>
<p>Select a category to see currently listed items.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1; gap: 1.5rem; margin-top: 2rem;">
    <?php if (count($categories) > 0): ?>
        <?php foreach ($categories as $cat): ?>
            <a href="browse.php?category=<?php echo $cat['id']; ?>" style="text-decoration: none; color: inherit;">
                <div style="background: #fff; border: 1px solid #e2e8f0; padding: 2rem; border-radius: 0.5rem; text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="margin: 0; color: var(--primary);"><?php echo sanitize($cat['name']); ?></h3>
                    <!-- In the future, Member 1 or 3 can add product counts here -->
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No categories found. Please check back later.</p>
    <?php endif; ?>
</div>

<div style="margin-top: 3rem; text-align: center;">
    <a href="../index.php" class="btn" style="background: #64748b;">← Back to Home</a>
</div>

<?php require_once '../includes/footer.php'; ?>
