<?php
// pages/create_listing.php
require_once '../includes/bootstrap.php';
requireLogin();

// Admins are moderators only — they cannot create listings
if (isAdmin()) {
    setFlash('error', 'Administrators cannot create listings. Use the Admin Panel to manage the marketplace.');
    redirect(BASE_URL . 'admin/index.php');
}

$pageTitle = "Create New Listing";
include '../includes/header.php';

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

<div class="container relative mt-16 mb-20 flex justify-center">
    <!-- Decorative elements -->
    <div style="position: absolute; top: -50px; left: 10%; width: 300px; height: 300px; border-radius: 50%; background: linear-gradient(135deg, var(--primaryLight), var(--secondaryLight)); opacity: 0.15; filter: blur(40px); z-index: -1;"></div>
    <div style="position: absolute; bottom: -50px; right: 10%; width: 250px; height: 250px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #3b82f6); opacity: 0.1; filter: blur(40px); z-index: -1;"></div>

    <div class="w-full max-w-3xl">
        <div class="text-center mb-8">
            <h1 class="gradient-text mb-2" style="font-size: 2.75rem;">List an Item</h1>
            <p class="text-muted text-lg">Reach thousands of students instantly</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-weight: 500;">
                <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>

        <div class="glass-panel" style="padding: 2.5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); z-index: 10;">
            <form action="create_listing.php" method="POST" enctype="multipart/form-data" class="grid gap-6">
                
                <div class="form-group">
                    <label class="font-bold mb-2 block" style="color: var(--text-main);">What are you selling? *</label>
                    <input type="text" name="title" placeholder="e.g. Macbeth Textbook, 10th Edition" class="w-full premium-input" style="padding: 0.8rem 1rem;" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="font-bold mb-2 block" style="color: var(--text-main);">Category *</label>
                        <select name="category_id" class="w-full premium-input" style="padding: 0.8rem 1rem;" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-bold mb-2 block" style="color: var(--text-main);">Price (<?php echo APP_CURRENCY; ?>) *</label>
                        <div class="relative">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: bold; color: var(--text-muted);">$</span>
                            <input type="number" name="price" step="0.01" placeholder="0.00" class="w-full premium-input" style="padding: 0.8rem 1rem 0.8rem 2rem;" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-bold mb-2 block" style="color: var(--text-main);">Condition *</label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer glass-panel py-2 px-4 hover-scale" style="border-radius: var(--radius-full); border: 2px solid transparent; transition: all 0.2s;" onclick="this.parentElement.querySelectorAll('label').forEach(l => l.style.borderColor='transparent'); this.style.borderColor='var(--primary)';">
                            <input type="radio" name="condition" value="new" required class="m-0 accent-primary" style="accent-color: var(--primary);"> <span class="font-medium">New</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer glass-panel py-2 px-4 hover-scale" style="border-radius: var(--radius-full); border: 2px solid transparent; transition: all 0.2s;" onclick="this.parentElement.querySelectorAll('label').forEach(l => l.style.borderColor='transparent'); this.style.borderColor='var(--primary)';">
                            <input type="radio" name="condition" value="like_new" class="m-0" style="accent-color: var(--primary);"> <span class="font-medium">Like New</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer glass-panel py-2 px-4 hover-scale" style="border-radius: var(--radius-full); border: 2px solid var(--primary); transition: all 0.2s;" onclick="this.parentElement.querySelectorAll('label').forEach(l => l.style.borderColor='transparent'); this.style.borderColor='var(--primary)';">
                            <input type="radio" name="condition" value="used" checked class="m-0" style="accent-color: var(--primary);"> <span class="font-medium">Used</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-bold mb-2 block" style="color: var(--text-main);">Description *</label>
                    <textarea name="description" rows="5" placeholder="Mention age, defects, or why you're selling..." class="w-full premium-input" style="padding: 1rem; border-radius: var(--radius-lg);" required></textarea>
                </div>

                <div class="form-group">
                    <label class="font-bold mb-2 block" style="color: var(--text-main);">Photos (Max 5)</label>
                    <div class="border-2 border-dashed rounded-lg p-8 text-center hover-scale cursor-pointer transition-colors" style="border-color: rgba(99,102,241,0.3); background: rgba(99,102,241,0.03);" onclick="document.getElementById('imgInput').click()" onmouseover="this.style.background='rgba(99,102,241,0.06)'" onmouseout="this.style.background='rgba(99,102,241,0.03)'">
                        <div class="text-4xl mb-3">📸</div>
                        <p class="font-bold mb-1" style="color: var(--primary);">Upload Images</p>
                        <p class="text-muted small">PNG, JPG up to 5MB</p>
                        <input type="file" id="imgInput" name="images[]" multiple accept="image/*" class="hidden">
                    </div>
                    <div id="preview" class="flex gap-3 mt-4 overflow-x-auto pb-2"></div>
                </div>

                <hr style="border-color: rgba(0,0,0,0.05); margin: 1rem 0;">

                <div class="flex justify-between items-center">
                    <a href="browse.php" class="text-muted font-medium hover:text-main transition-colors">Cancel</a>
                    <button type="submit" class="btn btn-primary px-8 py-3 hover-scale shadow-lg" style="border-radius: var(--radius-full); font-weight: bold; font-size: 1.1rem;">Publish Listing ✨</button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    input[type="radio"] { width: 1.25rem; height: 1.25rem; }
    textarea { resize: vertical; }
</style>

<script>
document.getElementById('imgInput').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    [...e.target.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = (re) => {
            const div = document.createElement('div');
            div.style = "width:80px; height:80px; border-radius: var(--radius-md); overflow:hidden; border: 2px solid var(--primaryLight); box-shadow: var(--shadow-sm); flex-shrink: 0;";
            div.innerHTML = `<img src="${re.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
            preview.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
