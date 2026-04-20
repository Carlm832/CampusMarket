<?php
session_start();
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/data.php';

$success = false;
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $condition = trim($_POST['condition'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $category === '' || $condition === '' || $price <= 0 || $description === '') {
        $error_message = 'Please fill in all required fields.';
    } elseif (!isset($_FILES['images']) || !isset($_FILES['images']['tmp_name'][0]) || $_FILES['images']['tmp_name'][0] === '') {
        $error_message = 'Please select at least one image.';
    } else {
        $upload_dir_fs = __DIR__ . '/../public/images/';
        if (!is_dir($upload_dir_fs)) {
            mkdir($upload_dir_fs, 0777, true);
        }

        $original_name = basename($_FILES['images']['name'][0]);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowed, true)) {
            $error_message = 'Only JPG, JPEG, PNG, WEBP, and GIF images are allowed.';
        } else {
            $safe_base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
            $new_file_name = time() . '_' . $safe_base . '.' . $extension;
            $target_fs = $upload_dir_fs . $new_file_name;

            if (!move_uploaded_file($_FILES['images']['tmp_name'][0], $target_fs)) {
                $error_message = 'Image upload failed. Please try again.';
            } else {
                $max_id = 0;
                foreach ($products as $product) {
                    $max_id = max($max_id, (int)$product['id']);
                }

                $new_product = [
                    'id' => $max_id + 1,
                    'title' => $title,
                    'price' => $price,
                    'category' => $category,
                    'condition' => $condition,
                    'img' => '../public/images/' . rawurlencode($new_file_name),
                    'desc' => $description,
                ];

                if (!isset($_SESSION['custom_products']) || !is_array($_SESSION['custom_products'])) {
                    $_SESSION['custom_products'] = [];
                }
                array_unshift($_SESSION['custom_products'], $new_product);
                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create a Listing - CampusMarket</title>

</head>
<body>

<nav aria-label="Catalog" style="padding:.5rem 1rem;font-size:.95rem;">
  <a href="index.php">Home</a> ·
  <a href="browse.php">Browse</a> ·
  <a href="search.php">Search</a> ·
  <a href="wishlist.php">Wishlist</a>
</nav>

<div class="create-layout">
  <form class="create-listing-form" method="POST" enctype="multipart/form-data">
  <div class="create-form-col">
    <h1>Create a New Listing</h1>
    <p class="subtitle">Fill in the details below to list your item.</p>

    <?php if($success): ?>
    <div class="alert-success">✅ Listing created successfully! Your item is now live.</div>
    <?php endif; ?>
    <?php if($error_message !== ''): ?>
    <div class="alert-success" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">⚠️ <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="form-card">
      <h2>Item Details</h2>
      <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" placeholder="e.g., iPad Air 4th Gen" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Category *</label>
          <select name="category" required>
            <option value="">Select a category</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Condition *</label>
          <div style="display: flex; gap: 8px; align-items: center;">
            <select name="condition" id="condition-select" required>
              <option value="">Select condition</option>
              <option value="New">New</option>
              <option value="Like New">Like New</option>
              <option value="Used">Used</option>
            </select>
            <input type="text" id="new-condition-input" placeholder="Add new..." style="display:none; width: 120px;" />
            <button type="button" id="add-condition-btn">Add</button>
          </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          const addBtn = document.getElementById('add-condition-btn');
          const input = document.getElementById('new-condition-input');
          const select = document.getElementById('condition-select');
          let adding = false;
          addBtn.addEventListener('click', function() {
            if (!adding) {
              input.style.display = 'inline-block';
              input.focus();
              addBtn.textContent = 'Save';
              adding = true;
            } else {
              const val = input.value.trim();
              if (val) {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                select.appendChild(opt);
                select.value = val;
                input.value = '';
              }
              input.style.display = 'none';
              addBtn.textContent = 'Add';
              adding = false;
            }
          });
        });
        </script>
      </div>

      <div class="form-group">
        <label>Price *</label>
        <input type="number" name="price" placeholder="TL" step="0.01" required>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" placeholder="Describe your item in detail..." required></textarea>
      </div>
    </div>
  </div>

  <div class="create-photos-col" style="margin-top:4rem;">
    <div class="form-card">
      <h2>Photos</h2>
      <div class="upload-area" onclick="document.getElementById('images').click()">
        <p>Drag &amp; drop photos here or click to upload</p>
        <small>You can upload up to 5 images.</small>
        <input type="file" id="images" name="images[]" multiple accept="image/*">
      </div>
      <div class="preview-grid" id="preview"></div>

      <h2 style="margin-top:1.5rem;">Location & Contact</h2>
      <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" placeholder="e.g., Main Library, Building A">
      </div>
      <div class="form-group">
        <label>Contact Preference</label>
        <select name="contact_pref">
          <option>In-app messaging</option>
          <option>Phone call</option>
          <option>Email</option>
        </select>
      </div>

      <div class="form-actions">
        <a href="index.php" class="btn-cancel" style="text-decoration:none;text-align:center;">Cancel</a>
        <button type="submit" class="btn-publish">Publish Listing</button>
      </div>
    </div>
  </div>
  </form>
</div>



<script>
document.getElementById('images').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    Array.from(e.target.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `<img src="${event.target.result}"><button class="preview-remove" onclick="event.preventDefault();">✕</button>`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
</body>
</html>
