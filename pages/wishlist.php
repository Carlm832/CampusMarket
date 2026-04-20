<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Wishlist - CampusMarket</title>
<style>
  .wishlist-page { max-width: 960px; margin: 0 auto; padding: 1rem 1.25rem 2rem; }
  .wishlist-empty-msg { color: #555; margin: 1rem 0; }
  .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
  .wishlist-item {
    display: flex; gap: 0.75rem; align-items: flex-start;
    border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.75rem;
    background: #fff; cursor: pointer;
  }
  .wishlist-item:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); }
  .wishlist-item-img { width: 88px; height: 88px; object-fit: cover; border-radius: 8px; flex-shrink: 0; background: #f3f4f6; }
  .wishlist-item-title { font-weight: 600; margin-bottom: 0.25rem; }
  .wishlist-item-meta { font-size: 0.9rem; color: #64748b; margin-bottom: 0.5rem; }
  .wishlist-unlike-btn {
    font: inherit; cursor: pointer; border: 1px solid #fecaca; background: #fff1f2; color: #b91c1c;
    border-radius: 8px; padding: 0.35rem 0.65rem; font-size: 0.9rem;
  }
  .wishlist-unlike-btn:hover { background: #fee2e2; }
</style>
</head>
<body>

<nav aria-label="Catalog" style="padding:.5rem 1rem;font-size:.95rem;">
  <a href="index.php">Home</a> ·
  <a href="browse.php">Browse</a> ·
  <a href="search.php">Search</a> ·
  <a href="create_listing.php">Create listing</a>
</nav>

<div class="wishlist-page">
  <h1>My Wishlist</h1>
  <p class="subtitle">Items you've saved for later. Use <strong>Remove</strong> to undo a like.</p>

  <div id="wishlist-container"></div>
</div>

<script src="../public/js/wishlist.js"></script>
<script>updateWishlistUI();</script>
</body>
</html>
