<?php
// pages/create_listing.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Auth Check
if (!isLoggedIn()) {
    setFlash('error', 'Please login to list an item.');
    redirect('login.php');
}

$success = false;
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = sanitize($_POST['title']);
    $categoryId  = (int)$_POST['category_id'];
    $price       = (float)$_POST['price'];
    $condition   = sanitize($_POST['condition']);
    $description = sanitize($_POST['description']);
    $userId      = currentUserId();

    try {
        $pdo->beginTransaction();

        // 1. Insert Product
        $stmt = $pdo->prepare("INSERT INTO products (user_id, category_id, title, description, price, `condition`, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$userId, $categoryId, $title, $description, $price, $condition]);
        $productId = $pdo->lastInsertId();

        // 2. Handle Image Uploads
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($i >= MAX_IMAGES) break; // Limit per listing

                $fileData = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i]
                ];

                $upload = handleUpload($fileData, 'products/');
                if ($upload['success']) {
                    $isPrimary = ($i === 0) ? 1 : 0;
                    $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                    $stmtImg->execute([$productId, $upload['path'], $isPrimary]);
                }
            }
        }

        $pdo->commit();
        $success = true;
        setFlash('success', 'Your listing is live!');
        redirect('browse.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to create listing: " . $e->getMessage();
    }
}

// Fetch Categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="container mt-12 mb-20">
    <div class="max-w-2xl mx-auto">
        <h1 class="mb-2">List an Item</h1>
        <p class="text-muted mb-8">Reach thousands of students instantly.</p>

        <?php if ($error): ?>
            <div class="flash flash-error mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card p-8">
            <form action="create_listing.php" method="POST" enctype="multipart/form-data" class="grid gap-6">
                
                <div class="form-group">
                    <label class="font-bold mb-2 block">What are you selling? *</label>
                    <input type="text" name="title" placeholder="e.g. Macbeth Textbook, 10th Edition" class="w-full" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="font-bold mb-2 block">Category *</label>
                        <select name="category_id" class="w-full" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-bold mb-2 block">Price (<?php echo APP_CURRENCY; ?>) *</label>
                        <input type="number" name="price" step="0.01" placeholder="0.00" class="w-full" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-bold mb-2 block">Condition *</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="new" required> <span>New</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="like_new"> <span>Like New</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="condition" value="used" checked> <span>Used</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-bold mb-2 block">Description *</label>
                    <textarea name="description" rows="5" placeholder="Mention age, defects, or why you're selling..." class="w-full" required></textarea>
                </div>

                <div class="form-group">
                    <label class="font-bold mb-2 block">Photos (Max 5)</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center bg-gray-50 hover-scale cursor-pointer" onclick="document.getElementById('imgInput').click()">
                        <div class="text-3xl mb-2">📸</div>
                        <p class="text-muted small">Click to upload images</p>
                        <input type="file" id="imgInput" name="images[]" multiple accept="image/*" class="hidden">
                    </div>
                    <div id="preview" class="flex gap-2 mt-4 overflow-x-auto"></div>
                </div>

                <hr class="my-4">

                <div class="flex justify-between items-center">
                    <a href="browse.php" class="text-muted">Cancel</a>
                    <button type="submit" class="btn btn-primary px-8 py-3">Publish Listing</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('imgInput').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    [...e.target.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = (re) => {
            const div = document.createElement('div');
            div.style = "width:60px; height:60px; border-radius:8px; overflow:hidden;";
            div.innerHTML = `<img src="${re.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
            preview.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
