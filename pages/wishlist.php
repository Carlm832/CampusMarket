<?php
// pages/wishlist.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

$pageTitle = "My Wishlist";
?>

<div class="container min-h-screen pt-12 pb-20 relative">
    <!-- Background Accents -->
    <div style="position: absolute; top: -5%; left: 5%; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(236,72,153,0.06) 0%, rgba(255,255,255,0) 70%); z-index: -1;"></div>

    <div class="mb-10 text-center lg:text-left flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
            <h1 class="font-bold text-4xl mb-2 gradient-text" style="background: linear-gradient(135deg, var(--text-main), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">My Wishlist</h1>
            <p class="text-muted text-lg font-medium">Items you've saved to look at later.</p>
        </div>
    </div>

    <!-- This container is populated by public/js/wishlist.js -->
    <div id="wishlist-container">
        <div class="glass-panel text-center py-20 px-4 shadow-sm relative overflow-hidden" style="border-radius: var(--radius-xl); border: 2px dashed rgba(0,0,0,0.05);">
            <div class="text-4xl mb-6 opacity-30 animate-pulse">⏳</div>
            <p class="text-muted font-bold text-lg">Loading your collection...</p>
        </div>
    </div>
</div>

<!-- Custom styles for the wishlist items dynamic injection -->
<style>
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}
.wishlist-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.5);
    padding: 1.25rem;
    display: flex;
    gap: 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.wishlist-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 4px; height: 100%;
    background: linear-gradient(to bottom, var(--primary), var(--secondary));
    opacity: 0;
    transition: opacity 0.3s ease;
}
.wishlist-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    background: white;
}
.wishlist-card:hover::before {
    opacity: 1;
}
.wishlist-card img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
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
            <div class="glass-panel p-20 text-center shadow-sm relative overflow-hidden" style="border-radius: var(--radius-xl); border: 2px dashed rgba(0,0,0,0.05);">
                <div class="text-8xl mb-6 opacity-20" style="transform: rotate(10deg);">💖</div>
                <h3 class="font-bold text-main text-3xl mb-3">Your wishlist is empty</h3>
                <p class="text-muted text-lg max-w-lg mx-auto mb-8">Start browsing and click the heart icon to save items you love.</p>
                <a href="browse.php" class="btn btn-primary shadow-lg hover-scale" style="border-radius: var(--radius-full); padding: 0.8rem 2.5rem; font-weight: bold; font-size: 1.1rem;">Discover Items</a>
            </div>
        `;
        return;
    }

    let html = '<div class="wishlist-grid">';
    items.forEach(item => {
        html += `
            <div class="wishlist-card group">
                <div style="position: relative;">
                    <img src="${item.img}" alt="${item.title}">
                </div>
                <div class="flex-grow flex flex-col justify-between py-1">
                    <div>
                        <p class="text-primary font-bold tracking-wider uppercase mb-1" style="font-size: 0.65rem;">${item.category}</p>
                        <h4 class="mb-1 text-main font-bold truncate leading-tight" style="font-size: 1.15rem; max-width: 150px;">${item.title}</h4>
                        <p class="font-bold font-inter tracking-tight" style="font-size: 1.25rem; color: var(--text-main);">${item.price}</p>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <a href="product.php?id=${item.id}" class="btn btn-primary btn-sm flex-1 text-center" style="border-radius: var(--radius-full); font-weight: bold; font-size: 0.85rem;">View</a>
                        <button onclick="toggleWishlist(${item.id}, {}); updateWishlistUI();" class="btn btn-sm hover-scale" style="color: #ef4444; background: #fee2e2; border: none; border-radius: var(--radius-full); font-weight: bold; width: 40px; padding: 0; display:flex; align-items:center; justify-content:center;" title="Remove">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        </button>
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
