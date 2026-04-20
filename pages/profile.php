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
    echo '<div class="container mt-12 text-center"><h2>User not found</h2><a href="index.php" class="btn btn-primary mt-4">Back Home</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$isSelf = isLoggedIn() && (int)currentUserId() === (int)$user['id'];
$rating = getSellerRating($pdo, (int)$user['id']);
$pageTitle = sanitize($user['username']) . "'s Profile";
?>

<div class="container mt-12 mb-20">
    <!-- Profile Header -->
    <div class="card p-8 mb-8" style="background: linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.9)); backdrop-filter: blur(20px);">
        <div class="flex items-center gap-8 flex-wrap">
            <div class="relative">
                <img src="<?php echo avatarUrl($user['avatar']); ?>" alt="Avatar" class="shadow-xl" style="width: 140px; height: 140px; border-radius: var(--radius-full); object-fit: cover; border: 4px solid white;">
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="absolute bottom-0 right-0 badge badge-primary py-1 px-3 shadow-md">ADMIN</span>
                <?php endif; ?>
            </div>
            
            <div class="flex-grow">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="mb-1" style="font-size: 2.5rem;"><?php echo sanitize($user['username']); ?></h1>
                        <p class="text-muted mb-4">Member since <?php echo formatJoinDate($user['created_at']); ?></p>
                        
                        <div class="flex items-center gap-2">
                            <?php if ($rating['count'] > 0): ?>
                                <span style="color: #f59e0b; font-size: 1.25rem;">
                                    <?php echo renderStars($rating['avg']); ?>
                                </span>
                                <span class="font-bold"><?php echo $rating['avg']; ?></span>
                                <span class="text-muted small">(<?php echo $rating['count']; ?> reviews)</span>
                            <?php else: ?>
                                <span class="text-muted small">No reviews yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($isSelf): ?>
                        <div class="flex gap-2">
                            <a href="edit_profile.php" class="btn btn-secondary btn-sm">Edit Profile</a>
                            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="messages.php?to=<?php echo $user['id']; ?>" class="btn btn-primary px-6">Message</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="lg:col-span-1">
            <div class="card p-6 h-full">
                <h3 class="mb-6">About Seller</h3>
                <div class="grid gap-4">
                    <div>
                        <p class="text-muted small uppercase font-bold mb-1">Email</p>
                        <p><?php echo $isSelf || isAdmin() ? sanitize($user['email']) : '••••••••@••••.com'; ?></p>
                    </div>
                    <?php if ($user['phone']): ?>
                    <div>
                        <p class="text-muted small uppercase font-bold mb-1">Phone</p>
                        <p><?php echo sanitize($user['phone']); ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <p class="text-muted small uppercase font-bold mb-1">Verified</p>
                        <p class="text-success flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            University Email Student
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Listings -->
        <div class="lg:col-span-2">
            <div class="card p-6 h-full">
                <h3 class="mb-6">Active Listings</h3>
                <?php
                $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, i.image_path FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN product_images i ON p.id = i.product_id AND i.is_primary = 1 WHERE p.user_id = :uid AND p.status = 'active' ORDER BY p.created_at DESC");
                $stmt->execute([':uid' => $viewId]);
                $userProducts = $stmt->fetchAll();
                ?>

                <?php if (empty($userProducts)): ?>
                    <div class="text-center py-12">
                        <p class="text-muted">No active listings at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($userProducts as $prod): ?>
                            <a href="product.php?id=<?php echo $prod['id']; ?>" class="flex gap-4 p-3 border rounded-lg hover:bg-gray-50 transition-colors">
                                <img src="<?php echo $prod['image_path'] ? BASE_URL.'/public/'.$prod['image_path'] : '../public/images/placeholder.png'; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-sm);">
                                <div class="flex-grow">
                                    <h4 class="mb-1" style="font-size: 0.95rem;"><?php echo sanitize($prod['title']); ?></h4>
                                    <p class="text-primary font-bold mb-1"><?php echo formatPrice($prod['price']); ?></p>
                                    <span class="badge badge-secondary py-0 px-2" style="font-size: 0.75rem;"><?php echo sanitize($prod['category_name']); ?></span>
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
