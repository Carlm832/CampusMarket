<?php
// admin/categories.php
require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/auth_check.php'; // Ensures only admin can access

$pageTitle = "Manage Categories";

// Handle Add/Delete (Member 1 logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = sanitize($_POST['name']);
        $slug = sanitize(strtolower(str_replace(' ', '-', $name)));

        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        setFlash('success', 'Category added successfully.');
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// lets keep the logic simple for other team members to follow
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../public/css/style.css"> <!-- Assuming Member 5 finishes this -->
    <style>
        body {
            font-family: sans-serif;
            padding: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }

        .form-add {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #eee;
        }
    </style>
</head>

<body>
    <a href="../index.php">← Back to Site</a>
    <h1><?= $pageTitle ?></h1>

    <div class="form-add">
        <h3>Add New Category (Member 1 Tool)</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Category Name" required>
            <button type="submit" name="add_category">Add Category</button>
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
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= sanitize($cat['name']) ?></td>
                    <td><?= sanitize($cat['slug']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>