<?php
// admin/tags.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php';

$pageTitle = "Manage Tags";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_tag'])) {
        $name = sanitize($_POST['name']);
        $slug = sanitize(strtolower(str_replace(' ', '-', $name)));
        
        $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        setFlash('success', 'Tag added successfully.');
    }
}

$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        body { font-family: sans-serif; padding: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; }
    </style>
</head>
<body>
    <a href="../index.php">← Back to Site</a>
    <h1><?= $pageTitle ?></h1>

    <div style="margin-bottom: 30px; padding: 20px; border: 1px solid #eee;">
        <h3>Add New Tag (Member 1 Tool)</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Tag Name (e.g. Vintage)" required>
            <button type="submit" name="add_tag">Add Tag</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= $tag['id'] ?></td>
                <td><?= sanitize($tag['name']) ?></td>
                <td><?= sanitize($tag['slug']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
