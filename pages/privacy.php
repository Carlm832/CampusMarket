<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$page_title = "Privacy Policy";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="card p-5" style="max-width: 800px; margin: 0 auto; background: var(--bg-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
        <h1 class="mb-4" style="color: var(--text-main); font-weight: 800; font-size: 2.2rem; text-align: center;">Privacy Policy</h1>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Last updated: <?= date('F j, Y') ?></p>

        <div style="line-height: 1.8; color: var(--text-main);">
            <p>This Privacy Policy describes how your personal information is collected, used, and shared when you visit or make a purchase/listing on CampusMarket.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">Information We Collect</h3>
            <p>We collect your university email address, name, and any profile information you choose to provide. We also collect data about your listings, messages, and interactions on the platform.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">How We Use Your Information</h3>
            <p>We use your information to provide our services, authenticate users, facilitate communication between buyers and sellers, and improve our platform.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">Data Sharing</h3>
            <p>Your public profile and listings are visible to other verified users on CampusMarket. We do not sell your personal data to third parties.</p>

            <h3 style="margin-top: 1.5rem; color: var(--primary);">Security</h3>
            <p>We implement industry-standard security measures to protect your account, but remember that no method of transmission over the internet is 100% secure.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
