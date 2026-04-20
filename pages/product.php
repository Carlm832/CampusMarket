<?php
session_start();
include __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/data.php';

$product_id = $_GET['id'] ?? 1;
$product = null;
foreach($products as $p) {
    if($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}
if(!$product) $product = $products[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($product['title']) ?> - CampusMarket</title>

</head>
<body>

<nav aria-label="Catalog" style="padding:.5rem 1rem;font-size:.95rem;">
  <a href="index.php">Home</a> ·
  <a href="browse.php">Browse</a> ·
  <a href="search.php">Search</a> ·
  <a href="create_listing.php">Create listing</a> ·
  <a href="wishlist.php">Wishlist</a>
</nav>

<div class="detail-layout">
  <div class="detail-gallery">
    <div class="gallery-main">
      <img src="<?= $product['img'] ?>" alt="<?= htmlspecialchars($product['title']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
      <div class="gallery-main-placeholder" style="display:none">📦</div>
    </div>
    <div class="gallery-thumbs">
      <div class="gallery-thumb active"><img src="<?= $product['img'] ?>" alt=""></div>
    </div>
  </div>

  <div class="detail-info">
    <h1 class="detail-title"><?= htmlspecialchars($product['title']) ?></h1>
    <div class="detail-price"><?= formatPrice($product['price']) ?></div>

    <div class="detail-badges">
      <span class="badge badge-category"><?= htmlspecialchars($product['category']) ?></span>
      <span class="badge badge-condition <?= strtolower(str_replace(' ','-',$product['condition'])) ?>"><?= htmlspecialchars($product['condition']) ?></span>
    </div>

    <p class="detail-desc">
      <?= htmlspecialchars($product['desc']) ?>
    </p>

    <div class="detail-meta-row">
      <span>📍 Main Library</span>
      <span>📅 Posted recently</span>
    </div>

    <div class="seller-box">
      <div class="seller-left">
        <div class="seller-avatar">JD</div>
        <div>
          <div class="seller-name">john_doe</div>
          <div class="seller-rating">⭐ 4.8 (24 reviews)</div>
          <div class="seller-join">Member since Mar 2024</div>
        </div>
      </div>
      <button class="btn-view-profile">View Profile</button>
    </div>

    <div class="action-row">
      <button type="button" class="btn-contact">Message Seller</button>
      <button type="button" class="btn-wishlist-detail" id="heart-btn-detail" onclick="toggleWishlistDetail(<?= $product['id'] ?>,<?= htmlspecialchars(json_encode($product),ENT_QUOTES) ?>)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        Add to Wishlist
      </button>
    </div>
  </div>
</div>

<style>
  .btn-wishlist-detail { font: inherit; cursor: pointer; display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; }
  .btn-wishlist-detail.active { border-color: #fecaca; background: #fff1f2; color: #b91c1c; }
</style>
<script src="../public/js/wishlist.js"></script>
<script>
function toggleWishlistDetail(id, item) {
  toggleWishlist(id, item);
  updateDetailBtn(id);
}
function updateDetailBtn(id) {
  var w = getWishlist();
  var btn = document.getElementById('heart-btn-detail');
  var n = Number(id);
  var on = w.some(function (x) { return Number(x.id) === n; });
  if (on) {
    btn.classList.add('active');
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg> Remove from Wishlist';
  } else {
    btn.classList.remove('active');
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg> Add to Wishlist';
  }
}
updateWishlistUI();
updateDetailBtn(<?= (int)$product['id'] ?>);
</script>
</body>
</html>
