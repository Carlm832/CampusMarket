<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$page_title = "Terms of Service";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="card p-5" style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
        <h1 class="mb-4" style="color: var(--text-main); font-weight: 800; font-size: 2.2rem; text-align: center;">Terms of Service</h1>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Last updated: <?= date('F j, Y') ?></p>

        <div style="line-height: 1.8; color: var(--text-main);">
            <p>Welcome to CampusMarket. By accessing or using our platform, you agree to be bound by these Terms of Service.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">1. Eligibility</h3>
            <p>You must be a current student or staff member with a valid .edu email address to register an account.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">2. User Conduct</h3>
            <p>Users agree not to post illegal, prohibited, or inappropriate items. Harassment of any kind will result in an immediate ban.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">3. Liability</h3>
            <p>CampusMarket is a platform for connecting buyers and sellers. We do not guarantee the quality, safety, or legality of items listed.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">4. Modifications</h3>
            <p>We reserve the right to modify these terms at any time. Continued use of the platform constitutes acceptance of the new terms.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
