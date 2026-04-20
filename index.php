<?php
// index.php
require_once 'config/constants.php';
require_once 'includes/header.php';

$pageTitle = "Home";
?>

<div style="text-align: center; padding: 4rem 0;">
    <h1>Welcome to CampusMarket</h1>
    <p style="font-size: 1.25rem; color: #64748b;">The central hub for students to buy, sell, and trade.</p>
    <div style="margin-top: 2rem;">
        <a href="pages/browse.php" class="btn">Start Browsing</a>
        <a href="pages/categories.php" class="btn" style="background: #fff; color: var(--primary); border: 1px solid var(--primary); margin-left: 1rem;">View Categories</a>
    </div>
</div>

<hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 3rem 0;">

<h2>Project Core Status (Member 1 Output)</h2>
<p>As the Database Architect & Backend Core, the foundational infrastructure is now live:</p>

    <ul style="line-height: 2;">
        <li>✅ <strong>Database:</strong> schema.sql + seed.sql (13 tables ready)</li>
        <li>✅ <strong>Utilities:</strong> functions.php (Sanitization, Auth, Uploads ready)</li>
        <li>✅ <strong>Admin:</strong> <a href="admin/categories.php">Manage Categories</a> and <a href="admin/tags.php">Manage Tags</a> are functional.</li>
        <li>✅ <strong>Messaging & Orders:</strong> <a href="pages/inbox.php">Inbox</a>, <a href="pages/my_orders.php">My Orders</a>, and Notifications are live (Member 4 output).</li>
        <li>✅ <strong>Authentication & Profiles:</strong> <a href="pages/login.php">Login</a>, <a href="pages/register.php">Register</a>, and <a href="pages/profile.php">User Profiles</a> are functional (Member 2 output).</li>
        <li>✅ <strong>Infrastructure:</strong> Header, Footer, and Bootstrap are connected.</li>
    </ul>

    <div style="background: #eff6ff; padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #bfdbfe; margin-top: 2rem;">
        <h3 style="margin-top: 0;">Next Steps for the Team:</h3>
        <ul>
            <li><strong>Member 3:</strong> Build the Product Listing engine.</li>
            <li><strong>Member 5:</strong> Apply the full Design System to the UI.</li>
        </ul>
    </div>

<?php require_once 'includes/footer.php'; ?>
