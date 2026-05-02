<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$page_title = "Community Rules";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="card p-5" style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
        <h1 class="mb-4" style="color: var(--text-main); font-weight: 800; font-size: 2.2rem; text-align: center;">Community Rules</h1>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">To keep CampusMarket safe and enjoyable for everyone.</p>

        <div style="line-height: 1.8; color: var(--text-main);">
            <h3 style="margin-top: 1.5rem; color: var(--primary);">1. Be Respectful</h3>
            <p>Treat all members of our community with respect. Hate speech, bullying, or harassment will not be tolerated.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">2. Accurate Listings</h3>
            <p>Provide honest and accurate descriptions of your items. Include clear photos and disclose any flaws or damage.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">3. No Prohibited Items</h3>
            <p>Do not list weapons, illegal substances, recalled items, or anything that violates university policy.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">4. Honor Your Agreements</h3>
            <p>If you agree to buy or sell an item, follow through. Repeated no-shows may result in account suspension.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
