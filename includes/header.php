<?php
// includes/header.php
require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - CampusMarket' : 'CampusMarket'; ?></title>
    
    <!-- Member 5: Design System -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    
</head>
<body>

<nav class="navbar">
    <div class="container flex justify-between items-center">
        <!-- Logo -->
        <a href="<?php echo BASE_URL; ?>/index.php" class="logo">CampusMarket</a>
        
        <!-- Shared Search Bar -->
        <form action="<?php echo BASE_URL; ?>/pages/search.php" method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Search for items, books, tech..." class="search-input" required>
            <button type="submit" class="search-btn">Search</button>
        </form>

        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>/pages/browse.php">Browse</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>/pages/inbox.php">Inbox</a>
                <a href="<?php echo BASE_URL; ?>/pages/wishlist.php">Wishlist</a>
                <a href="<?php echo BASE_URL; ?>/pages/profile.php">Profile</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/index.php" style="color: var(--accent); font-weight: bold;">Admin</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-secondary btn-sm" style="margin-left: 0.5rem;">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/pages/login.php">Login</a>
                <a href="<?php echo BASE_URL; ?>/pages/register.php" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <?php if ($flash = getFlash()): ?>
        <div class="mt-4 flex items-center flash flash-<?php echo sanitize($flash['type']); ?>">
            <?php echo sanitize($flash['message']); ?>
        </div>
    <?php endif; ?>
