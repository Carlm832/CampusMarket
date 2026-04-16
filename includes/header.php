<?php
// includes/header.php
require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - CampusMarket' : 'CampusMarket'; ?></title>
    <!-- We'll link to CSS here. Member 5 will style this. -->
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --text: #1e293b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; line-height: 1.5; }
        nav { background: #fff; padding: 1rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; color: var(--primary); text-decoration: none; }
        .nav-links { display: flex; gap: 1.5rem; }
        .nav-links a { text-decoration: none; color: inherit; font-weight: 500; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .flash { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .flash-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .flash-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        footer { margin-top: 4rem; padding: 2rem; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: var(--primary); color: #fff; border: none; border-radius: 0.375rem; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

<nav>
    <a href="<?php echo BASE_URL; ?>/index.php" class="logo">CampusMarket</a>
    <div class="nav-links">
        <a href="<?php echo BASE_URL; ?>/pages/browse.php">Browse</a>
        <?php if (isLoggedIn()): ?>
            <?php 
                $unreadCount = countUnreadNotifications($pdo, currentUserId());
            ?>
            <a href="<?php echo BASE_URL; ?>/pages/inbox.php">Messages</a>
            <a href="<?php echo BASE_URL; ?>/pages/my_orders.php">Orders</a>
            <a href="<?php echo BASE_URL; ?>/pages/notifications.php">
                Notifications
                <?php if ($unreadCount > 0): ?>
                    <span style="background: #ef4444; color: #fff; padding: 2px 6px; border-radius: 999px; font-size: 0.75rem; vertical-align: top; margin-left: 2px;">
                        <?php echo $unreadCount; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/profile.php">Profile</a>
            <a href="<?php echo BASE_URL; ?>/pages/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/pages/login.php">Login</a>
            <a href="<?php echo BASE_URL; ?>/pages/register.php">Register</a>
        <?php endif; ?>
        <?php if (isAdmin()): ?>
            <a href="<?php echo BASE_URL; ?>/admin/index.php" style="color: #dc2626;">Admin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <?php if ($flash = getFlash()): ?>
        <div class="flash flash-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>
