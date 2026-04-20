<?php
// pages/wishlist.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

$pageTitle = "My Wishlist";
?>

<div class="container mt-12 mb-20">
    <div class="mb-8">
        <h1 class="mb-2">My Wishlist</h1>
        <p class="text-muted">Items you've saved to look at later.</p>
    </div>

    <!-- This container is populated by public/js/wishlist.js -->
    <div id="wishlist-container">
        <div class="text-center py-20">
            <div class="text-4xl mb-4">⌛</div>
            <p class="text-muted">Loading your saved items...</p>
        </div>
    </div>
</div>

<!-- Custom styles for the wishlist items dynamic injection -->
<style>
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.wishlist-card {
    background: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    padding: 1rem;
    display: flex;
    gap: 1rem;
    transition: all 0.3s ease;
}
.wishlist-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
.wishlist-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: var(--radius-md);
    background: var(--bg-main);
}
</style>

<script src="../public/js/wishlist.js"></script>
<script>
// Overriding the default updateWishlistUI to use our new Design System classes
function updateWishlistUI() {
    const container = document.getElementById('wishlist-container');
    const items = getWishlist();
    
    if (items.length === 0) {
        container.innerHTML = `
            <div class="card p-16 text-center">
                <div class="text-4xl mb-4">❤️</div>
                <h3>Your wishlist is empty</h3>
                <p class="text-muted">Start browsing and click the heart icon to save items.</p>
                <a href="browse.php" class="btn btn-primary mt-6">Start Browsing</a>
            </div>
        `;
        return;
    }

    let html = '<div class="wishlist-grid">';
    items.forEach(item => {
        html += `
            <div class="wishlist-card">
                <img src="${item.img}" alt="${item.title}">
                <div class="flex-grow flex flex-col justify-between py-1">
                    <div>
                        <h4 class="mb-1">${item.title}</h4>
                        <p class="text-muted small mb-2">${item.category}</p>
                        <p class="font-bold text-primary">${item.price}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="product.php?id=${item.id}" class="btn btn-secondary btn-sm">View</a>
                        <button onclick="toggleWishlist(${item.id}, {}); updateWishlistUI();" class="btn btn-sm" style="color: #ef4444; background: #fee2e2; border: none;">Remove</button>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', updateWishlistUI);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
