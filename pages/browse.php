<?php
session_start();
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/data.php';

$selected_category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? trim((string)$_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? trim((string)$_GET['max_price']) : '';
$condition = isset($_GET['condition']) ? trim((string)$_GET['condition']) : '';
$sort = $_GET['sort'] ?? 'latest';

/**
 * Build browse URL keeping current filters; $overrides replace those keys (empty string drops the param).
 */
function browse_url(array $overrides = []): string {
    $params = array_merge([
        'category' => $_GET['category'] ?? '',
        'min_price' => $_GET['min_price'] ?? '',
        'max_price' => $_GET['max_price'] ?? '',
        'condition' => $_GET['condition'] ?? '',
        'sort' => $_GET['sort'] ?? '',
    ], $overrides);
    $out = [];
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        $out[$k] = $v;
    }
    return $out === [] ? 'browse.php' : 'browse.php?' . http_build_query($out);
}

$filtered_products = $products;

if ($selected_category !== '') {
    $filtered_products = array_filter($filtered_products, fn($p) => $p['category'] === $selected_category);
}
if ($condition !== '') {
    $filtered_products = array_filter($filtered_products, function ($p) use ($condition) {
        return strcasecmp(trim((string)($p['condition'] ?? '')), $condition) === 0;
    });
}
if ($min_price !== '') {
    $filtered_products = array_filter($filtered_products, fn($p) => $p['price'] >= (float)$min_price);
}
if ($max_price !== '') {
    $filtered_products = array_filter($filtered_products, fn($p) => $p['price'] <= (float)$max_price);
}

$filtered_products = array_values($filtered_products);

if ($sort === 'price_asc') {
    usort($filtered_products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sort === 'price_desc') {
    usort($filtered_products, fn($a, $b) => $b['price'] <=> $a['price']);
} else {
    // latest (default, reverse id)
    usort($filtered_products, fn($a, $b) => $b['id'] <=> $a['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Browse - CampusMarket</title>
</head>
<body>

<nav aria-label="Catalog" style="padding:.5rem 1rem;font-size:.95rem;">
  <a href="index.php">Home</a> ·
  <a href="search.php">Search</a> ·
  <a href="create_listing.php">Create listing</a> ·
  <a href="wishlist.php">Wishlist</a>
</nav>

<div class="breadcrumb">
  <a href="index.php">Home</a> <span>›</span> Browse
</div>

<div class="browse-layout">
  <!-- SIDEBAR -->
  <aside class="filter-sidebar">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
      <h3 style="margin:0;">Filters</h3>
      <a href="browse.php" class="filter-clear">Clear all</a>
    </div>
    <form method="GET" action="browse.php" id="browseFiltersForm">
      <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
      <div class="filter-section">
        <h4>Category</h4>
        <div style="max-height:200px;overflow-y:auto;">
          <label class="filter-option">
            <input type="radio" name="category" value="" <?= $selected_category===''?'checked':'' ?> onclick="window.location.href='<?= htmlspecialchars(browse_url(['category' => '']), ENT_QUOTES, 'UTF-8') ?>'"> All Categories
          </label>
          <?php foreach($categories as $cat): ?>
          <label class="filter-option">
            <input type="radio" name="category" value="<?= htmlspecialchars($cat['name']) ?>" <?= $selected_category===$cat['name']?'checked':'' ?> onclick="window.location.href='<?= htmlspecialchars(browse_url(['category' => $cat['name']]), ENT_QUOTES, 'UTF-8') ?>'"> <?= htmlspecialchars($cat['name']) ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="filter-section">
        <h4>Price Range</h4>
        <div class="price-inputs">
          <input type="number" name="min_price" placeholder="0 TL" value="<?= htmlspecialchars($min_price) ?>">
          <span>-</span>
          <input type="number" name="max_price" placeholder="1000 TL+" value="<?= htmlspecialchars($max_price) ?>">
        </div>
      </div>

      <div class="filter-section">
        <h4>Condition</h4>
        <label class="filter-option">
          <input type="radio" name="condition" value="" <?= $condition===''?'checked':'' ?> onchange="this.form.submit()"> All Conditions
        </label>
        <label class="filter-option">
          <input type="radio" name="condition" value="New" <?= $condition==='New'?'checked':'' ?> onchange="this.form.submit()"> New
        </label>
        <label class="filter-option">
          <input type="radio" name="condition" value="Like New" <?= $condition==='Like New'?'checked':'' ?> onchange="this.form.submit()"> Like New
        </label>
        <label class="filter-option">
          <input type="radio" name="condition" value="Used" <?= $condition==='Used'?'checked':'' ?> onchange="this.form.submit()"> Used
        </label>
      </div>

      <button type="submit" class="apply-btn">Apply Filters</button>
    </form>
  </aside>

  <!-- RESULTS -->
  <main class="browse-results">
    <div class="results-bar">
      <div class="results-count"><?= count($filtered_products) ?> items found</div>
      <form method="GET" id="sortForm">
        <?php foreach($_GET as $k=>$v): if($k!=='sort'): ?>
        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
        <?php endif; endforeach; ?>
        <select name="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
          <option value="latest" <?= $sort==='latest'?'selected':'' ?>>Sort by: Latest</option>
          <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Sort by: Price (Low to High)</option>
          <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Sort by: Price (High to Low)</option>
        </select>
      </form>
    </div>

    <div class="products-grid">
      <?php foreach($filtered_products as $p): ?>
      <div class="product-card" onclick="window.location.href='product.php?id=<?= $p['id'] ?>'">
        <img class="product-img" src="<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['title']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="product-img-placeholder" style="display:none">📦</div>
        <button class="heart-btn" type="button" id="heart-<?= $p['id'] ?>" onclick="event.stopPropagation();toggleWishlist(<?= $p['id'] ?>,<?= htmlspecialchars(json_encode($p),ENT_QUOTES) ?>)" aria-label="Add to wishlist">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        </button>
        <div class="product-body">
          <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
          <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
          <div class="product-price"><?= formatPrice($p['price']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($filtered_products)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:3rem;background:#fff;border-radius:8px;">
        <h3>No items found matching your filters.</h3>
        <a href="browse.php" class="view-all" style="margin-top:1rem;display:inline-block;">Clear Filters</a>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<style>
  .product-card { position: relative; }
  .heart-btn { position: absolute; top: 8px; right: 8px; z-index: 2; width: 36px; height: 36px; border: none; border-radius: 50%; background: rgba(255,255,255,.95); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 4px rgba(0,0,0,.12); color: #64748b; }
  .heart-btn.active { color: #dc2626; }
</style>
<script src="../public/js/wishlist.js"></script>
<script>updateWishlistUI();</script>
</body>
</html>
