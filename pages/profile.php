<?php
// pages/profile.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Decide which profile to show
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : (int)(currentUserId() ?? 0);

if ($viewId <= 0) {
    requireLogin();
    $viewId = (int)currentUserId();
}

// Fetch User
$stmt = $pdo->prepare("SELECT id, username, email, role, phone, avatar, created_at FROM users WHERE id = :id");
$stmt->execute([':id' => $viewId]);
$user = $stmt->fetch();

if (!$user) {
    echo '<div class="container mt-12 text-center text-muted"><div class="text-6xl mb-4">👻</div><h2>User not found</h2><p>This user does not exist or has been deleted.</p><a href="index.php" class="btn btn-primary mt-4 hover-scale shadow-sm" style="border-radius: var(--radius-full);">Back Home</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$isSelf = isLoggedIn() && (int)currentUserId() === (int)$user['id'];
$rating = getSellerRating($pdo, (int)$user['id']);
$pageTitle = sanitize($user['username']) . "'s Profile";
?>

<div class="container mt-12 mb-20 relative">
    <!-- Decorative Background Blob -->
    <div style="position: absolute; top: -50px; left: -50px; width: 300px; height: 300px; border-radius: 50%; background: linear-gradient(135deg, var(--primaryLight), var(--secondaryLight)); opacity: 0.15; filter: blur(40px); z-index: -1 pointer-events: none;"></div>

    <!-- Profile Header -->
    <div class="glass-panel p-8 mb-8" style="border-radius: var(--radius-lg); position: relative; overflow: hidden;">
        <div style="position: absolute; top:0; left:0; width:100%; height:80px; background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(16,185,129,0.1) 100%);"></div>
        
        <div class="flex items-center gap-8 flex-wrap position-relative" style="z-index: 2; margin-top: 20px;">
            <div class="relative">
                <img src="<?php echo avatarUrl($user['avatar']); ?>" alt="Avatar" class="shadow-lg hover-scale" style="width: 140px; height: 140px; border-radius: var(--radius-full); object-fit: cover; border: 4px solid white;">
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="absolute bottom-0 right-0 badge shadow-md border" style="background: linear-gradient(135deg, var(--primary), #818cf8); color: white; border-color: white; border-width: 2px;">ADMIN</span>
                <?php endif; ?>
            </div>
            
            <div class="flex-grow">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="mb-1 text-main font-bold" style="font-size: 2.5rem; letter-spacing: -1px;"><?php echo sanitize($user['username']); ?></h1>
                        <p class="text-muted mb-4 font-medium">Member since <span style="color: var(--text-main);"><?php echo formatJoinDate($user['created_at']); ?></span></p>
                        
                        <div class="flex items-center gap-3">
                            <?php if ($rating['count'] > 0): ?>
                                <span style="background: #fef3c7; color: #d97706; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; display: flex; align-items: center; gap: 0.3rem;">
                                    <?php echo renderStars($rating['avg']); ?>
                                </span>
                                <span class="font-bold text-lg"><?php echo $rating['avg']; ?></span>
                                <span class="text-muted small">(<?php echo $rating['count']; ?> verified reviews)</span>
                            <?php else: ?>
                                <span style="background: var(--bg-main); padding: 0.3rem 0.75rem; border-radius: 4px; font-size: 0.85rem; color: var(--text-muted); border: 1px solid var(--border-light);">No trusted reviews yet.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($isSelf): ?>
                        <div class="flex gap-3">
                            <a href="edit_profile.php" class="btn btn-primary btn-sm hover-scale shadow-sm" style="border-radius: var(--radius-full);">Edit Profile</a>
                            <a href="logout.php" class="btn btn-secondary btn-sm hover-scale shadow-sm" style="border-radius: var(--radius-full);">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="messages.php?to=<?php echo $user['id']; ?>" class="btn btn-primary px-6 hover-scale shadow-sm" style="border-radius: var(--radius-full);">Direct Message</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="lg:col-span-1">
            <div class="glass-panel p-6 h-full" style="border-radius: var(--radius-lg);">
                <h3 class="mb-6 gradient-text">Trust & Details</h3>
                <div class="grid gap-6">
                    <div style="background: rgba(255,255,255,0.5); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(0,0,0,0.02);">
                        <p class="text-muted small uppercase font-bold mb-1">Email</p>
                        <p class="font-medium text-main m-0"><?php echo $isSelf || isAdmin() ? sanitize($user['email']) : '••••••••@••••.com'; ?></p>
                    </div>
                    <?php if ($user['phone']): ?>
                    <div style="background: rgba(255,255,255,0.5); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(0,0,0,0.02);">
                        <p class="text-muted small uppercase font-bold mb-1">Phone</p>
                        <p class="font-medium text-main m-0"><?php echo sanitize($user['phone']); ?></p>
                    </div>
                    <?php endif; ?>
                    <div style="background: rgba(16,185,129,0.05); padding: 1rem; border-radius: var(--radius-md); border: 1px solid rgba(16,185,129,0.1);">
                        <p class="text-muted small uppercase font-bold mb-1">Status</p>
                        <p class="text-success flex items-center gap-1 font-medium m-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Verified Campus Email
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Listings -->
        <div class="lg:col-span-2">
            <div class="glass-panel p-6 h-full" style="border-radius: var(--radius-lg);">
                <h3 class="mb-6 gradient-text">Active Market Listings</h3>
                <?php
                $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, i.image_path FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1 WHERE p.user_id = :uid AND p.status = 'active' ORDER BY p.created_at DESC");
                $stmt->execute([':uid' => $viewId]);
                $userProducts = $stmt->fetchAll();
                ?>

                <?php if (empty($userProducts)): ?>
                    <div class="text-center py-16" style="background: rgba(255,255,255,0.5); border-radius: var(--radius-md); border: 2px dashed var(--border-light);">
                        <div class="text-5xl mb-4 opacity-50">🛒</div>
                        <p class="font-medium text-muted">No active listings available right now.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($userProducts as $prod): ?>
                            <a href="product.php?id=<?php echo $prod['id']; ?>" class="flex gap-4 p-4 rounded-lg bg-white border border-transparent shadow-sm hover-scale" style="text-decoration: none; border-color: rgba(0,0,0,0.05);">
                                <div style="width: 90px; height: 90px; background: var(--bg-main); border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0;">
                                    <img src="<?php echo $prod['image_path'] ? BASE_URL.'/public/'.$prod['image_path'] : '../public/images/placeholder.png'; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div class="flex-grow flex flex-col justify-center">
                                    <h4 class="mb-1 text-main font-bold" style="font-size: 1rem; line-height: 1.3; margin: 0 0 0.25rem 0;"><?php echo sanitize($prod['title']); ?></h4>
                                    <p class="text-primary font-bold mb-2" style="font-size: 1.15rem; margin: 0;"><?php echo formatPrice($prod['price']); ?></p>
                                    <div>
                                        <span class="badge" style="background: var(--bg-main); color: var(--text-muted); border: 1px solid var(--border-light); font-size: 0.75rem; padding: 0.1rem 0.4rem;"><?php echo sanitize($prod['category_name']); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
