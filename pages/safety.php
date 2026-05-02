<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$page_title = "Safety Guidelines";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="card p-5" style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
        <h1 class="mb-4" style="color: var(--text-main); font-weight: 800; font-size: 2.2rem; text-align: center;">Safety Guidelines</h1>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Your safety is our top priority. Please review these guidelines before using CampusMarket.</p>

        <div style="line-height: 1.8; color: var(--text-main);">
            <h3 style="margin-top: 1.5rem; color: var(--primary);">1. Meeting in Person</h3>
            <p>Always meet in public, well-lit areas. Avoid going to someone's private residence or inviting strangers to yours.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">2. Verify Items</h3>
            <p>Inspect the item thoroughly before finalizing the purchase. Make sure it matches the description provided in the listing.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">3. Secure Payments</h3>
            <p>Use cash or secure digital payment methods (like Venmo or Zelle) at the time of the exchange. Never send money in advance.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">4. Trust Your Instincts</h3>
            <p>If a deal seems too good to be true, it probably is. If you feel uncomfortable at any point, walk away.</p>

            <hr style="border-color: var(--border-light); margin: 2rem 0;">

            <h2 id="meeting-points" style="color: var(--text-main); font-weight: 800; font-size: 1.8rem;">Recommended Meeting Points</h2>
            <ul style="margin-top: 1rem; list-style-type: disc; padding-left: 20px;">
                <li>The Student Union Building</li>
                <li>Campus Library (Main Lobby)</li>
                <li>Campus Security Office (Many campuses encourage this for online trades)</li>
                <li>On-campus coffee shops</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
