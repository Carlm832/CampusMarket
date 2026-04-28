<?php
// pages/create_listing.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

// Admins are moderators only — they cannot create listings
if (isAdmin()) {
    setFlash('error', 'Administrators cannot create listings. Use the Admin Panel to manage the marketplace.');
    redirect(BASE_URL . 'admin/index.php');
}

$pageTitle = "Create New Listing";

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 4rem; margin-bottom: 8rem; max-width: 1000px; padding: 0 2rem;">
    <!-- Horizontal Stepper -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 2rem; margin-bottom: 4rem;">
        <!-- Step 1: Active -->
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 40px; height: 40px; background: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);">1</div>
            <span style="font-weight: 700; color: #1e293b; font-size: 1.1rem;">Details</span>
        </div>
        
        <!-- Connector -->
        <div style="width: 60px; height: 2px; background: #e2e8f0;"></div>
        
        <!-- Step 2: Inactive -->
        <div style="display: flex; align-items: center; gap: 1rem; opacity: 0.5;">
            <div style="width: 40px; height: 40px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;">2</div>
            <span style="font-weight: 600; color: #64748b; font-size: 1.1rem;">Photos</span>
        </div>
        
        <!-- Connector -->
        <div style="width: 60px; height: 2px; background: #e2e8f0;"></div>
        
        <!-- Step 3: Inactive -->
        <div style="display: flex; align-items: center; gap: 1rem; opacity: 0.5;">
            <div style="width: 40px; height: 40px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;">3</div>
            <span style="font-weight: 600; color: #64748b; font-size: 1.1rem;">Preview</span>
        </div>
    </div>

    <!-- Form Content -->
    <main style="width: 100%;">
        <div style="background: white; border-radius: 1.5rem; border: 1px solid #f1f5f9; padding: 4rem; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.08);">
                <h1 style="font-size: 2.25rem; font-weight: 800; color: #1e293b; margin-bottom: 3.5rem;">Create a New Listing</h1>
                
                <form action="process_listing.php" method="POST" style="display: flex; flex-direction: column; gap: 2.5rem;">
                    
                    <!-- Title -->
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 1.1rem; color: #1e293b; margin-bottom: 0.75rem;">Title *</label>
                        <input type="text" name="title" placeholder="e.g., iPhone 12 Pro 128GB" required 
                               style="width: 100%; padding: 1.15rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; font-size: 1.1rem; color: #1e293b; transition: border-color 0.2s; outline: none;"
                               onfocus="this.style.borderColor='#2563eb'">
                    </div>

                    <!-- Category -->
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 1.1rem; color: #1e293b; margin-bottom: 0.75rem;">Category *</label>
                        <select name="category_id" required 
                                style="width: 100%; padding: 1.15rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; font-size: 1.1rem; color: #1e293b; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpath%20d%3D%22m6%209%206%206%206-6%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1.5rem center; background-size: 1.5rem; outline: none;"
                                onfocus="this.style.borderColor='#2563eb'">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Condition -->
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 1.1rem; color: #1e293b; margin-bottom: 0.75rem;">Condition *</label>
                        <select name="condition" required 
                                style="width: 100%; padding: 1.15rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; font-size: 1.1rem; color: #1e293b; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpath%20d%3D%22m6%209%206%206%206-6%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1.5rem center; background-size: 1.5rem; outline: none;"
                                onfocus="this.style.borderColor='#2563eb'">
                            <option value="">Select condition</option>
                            <option value="new">New</option>
                            <option value="like_new">Like New</option>
                            <option value="used">Used</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 1.1rem; color: #1e293b; margin-bottom: 0.75rem;">Price (₺) *</label>
                        <div style="position: relative;">
                            <input type="number" step="0.01" name="price" placeholder="0.00" required 
                                   style="width: 100%; padding: 1.15rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; font-size: 1.1rem; color: #1e293b; outline: none;"
                                   onfocus="this.style.borderColor='#2563eb'">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 1.1rem; color: #1e293b; margin-bottom: 0.75rem;">Description *</label>
                        <textarea name="description" placeholder="Describe your item in detail..." required rows="6"
                                  style="width: 100%; padding: 1.15rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; font-size: 1.1rem; color: #1e293b; resize: vertical; outline: none; transition: border-color 0.2s;"
                                  onfocus="this.style.borderColor='#2563eb'"></textarea>
                        <div style="text-align: right; margin-top: 0.5rem; color: #94a3b8; font-size: 0.875rem;">0 / 1000</div>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 1.1rem; color: #1e293b; margin-bottom: 0.75rem;">Tags</label>
                        <input type="text" name="tags" placeholder="Add tags (e.g. apple, iphone, gadget)" 
                               style="width: 100%; padding: 1.15rem 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0; font-size: 1.1rem; color: #1e293b; outline: none;"
                               onfocus="this.style.borderColor='#2563eb'">
                        <p style="color: #94a3b8; font-size: 0.875rem; margin-top: 0.5rem; margin-bottom: 0;">Max 5 tags</p>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; justify-content: flex-end; gap: 1.5rem; margin-top: 2rem;">
                        <button type="button" style="background: white; border: 1px solid #e2e8f0; color: #64748b; padding: 1rem 2.5rem; border-radius: 1rem; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='white'">Cancel</button>
                        <button type="submit" style="background: #2563eb; border: none; color: white; padding: 1rem 2.5rem; border-radius: 1rem; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);" onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">Next: Photos</button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
