<?php
session_start();
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/data.php';

$search_query = $_GET['q'] ?? '';

$results = [];
if ($search_query) {
    $query_lower = strtolower($search_query);
    $results = array_filter($products, function($p) use ($query_lower) {
        return strpos(strtolower($p['title']), $query_lower) !== false ||
               strpos(strtolower($p['category']), $query_lower) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Search Results - CampusMarket</title>

</head>
<body>

<nav aria-label="Catalog" style="padding:.5rem 1rem;font-size:.95rem;">
  <a href="index.php">Home</a> ·
  <a href="browse.php">Browse</a> ·
  <a href="create_listing.php">Create listing</a> ·
  <a href="wishlist.php">Wishlist</a>
</nav>

<div class="search-page">
  <div class="search-results-header">
    <h1>Search results for "<?= htmlspecialchars($search_query) ?>"</h1>
    <p><?= count($results) ?> items found</p>
  </div>

  <?php if(count($results)>0): ?>
  <div class="products-grid">
    <?php foreach($results as $p): ?>
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
  </div>
  <?php else: ?>
  <div class="empty-wishlist">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
    <h3>No items found</h3>
    <p>Try searching with different keywords or check out all listings.</p>
    <a href="browse.php" class="btn-publish" style="display:inline-block;text-decoration:none;">Browse All Items</a>
  </div>
  <?php endif; ?>
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
