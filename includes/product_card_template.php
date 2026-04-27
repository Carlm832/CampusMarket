<div class="card card-hover flex flex-col h-full" style="position: relative; border-radius: 1.25rem; border: 1px solid #f1f5f9; background: white; overflow: hidden; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s;">
    <!-- Main Product Link (Wraps content but NOT the form) -->
    <a href="<?php echo BASE_URL; ?>pages/product.php?id=<?php echo $prod['id']; ?>" style="text-decoration: none; display: flex; flex-direction: column; height: 100%;">
        <!-- Product Image Container -->
        <div style="height: 280px; width: 100%; overflow: hidden; background-color: #ffffff; display: flex; align-items: center; justify-content: center; position: relative; border-radius: 1rem; margin-bottom: 1.5rem;">
            <img src="<?php echo !empty($prod['image_path']) ? sanitize(BASE_URL . 'public/' . $prod['image_path']) : sanitize(BASE_URL . 'public/images/default-product.png'); ?>" 
                 alt="<?php echo sanitize($prod['title']); ?>" 
                 style="max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease;">
            
            <!-- Condition Badge -->
            <div style="position: absolute; top: 1rem; left: 1rem; z-index: 5;">
                <?php 
                $cond = $prod['condition'] ?? 'used';
                $badge = conditionBadge($cond); 
                ?>
                <span style="display: inline-block; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); padding: 0.4rem 0.85rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 800; color: #1e293b; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; text-transform: uppercase;">
                    <?php echo $badge['label']; ?>
                </span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="flex flex-col flex-grow px-1">
            <p class="mb-2" style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-bottom: 0.25rem;">(<?php echo sanitize($prod['category_name'] ?? ($prod['category'] ?? 'General')); ?>)</p>
            <h4 class="mb-3" style="font-size: 1.2rem; font-weight: 700; color: #1e293b; line-height: 1.2; margin-bottom: 1rem;"><?php echo sanitize($prod['title']); ?></h4>
            
            <div class="mt-auto flex items-center gap-4">
                <span style="font-weight: 800; color: #1e293b; font-size: 1.5rem; white-space: nowrap;">₺ <?php echo number_format($prod['price']); ?></span>
                <div style="height: 5px; width: 32px; background: #3b82f6; border-radius: 3px;"></div>
            </div>
        </div>
    </a>

    <!-- Save for Later Button (Moved OUTSIDE the link to fix interaction) -->
    <div style="position: absolute; bottom: 1.5rem; right: 1.5rem; z-index: 20;">
        <form action="<?php echo BASE_URL; ?>actions/toggle_wishlist.php" method="POST" style="margin: 0;">
            <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
            <?php 
                $isSaved = false;
                if (isLoggedIn()) {
                    global $userWishlistIds;
                    if (!isset($userWishlistIds)) {
                        $stmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE user_id = ?");
                        $stmt->execute([currentUserId()]);
                        $userWishlistIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    }
                    $isSaved = in_array($prod['id'], $userWishlistIds);
                }
            ?>
            <button type="submit" style="background: <?php echo $isSaved ? '#fee2e2' : '#f8fafc'; ?>; border: 1px solid <?php echo $isSaved ? '#fecaca' : '#e2e8f0'; ?>; color: <?php echo $isSaved ? '#ef4444' : '#64748b'; ?>; padding: 0.7rem; border-radius: 1rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" title="<?php echo $isSaved ? 'Remove from Wishlist' : 'Save for Later'; ?>" onmouseover="this.style.backgroundColor='#fee2e2'; this.style.color='#ef4444'; this.style.borderColor='#fecaca'; this.style.transform='scale(1.1)';" onmouseout="this.style.backgroundColor='<?php echo $isSaved ? '#fee2e2' : '#f8fafc'; ?>'; this.style.color='<?php echo $isSaved ? '#ef4444' : '#64748b'; ?>'; this.style.borderColor='<?php echo $isSaved ? '#fecaca' : '#e2e8f0'; ?>'; this.style.transform='scale(1)';">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="<?php echo $isSaved ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>
        </form>
    </div>
</div>
