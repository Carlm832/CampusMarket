<?php
// admin/users.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/header.php';

// Auth Check
if (!isAdmin()) {
    setFlash('error', 'Unauthorized access.');
    redirect('../index.php');
}

$pageTitle = "Manage Users";

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'make_admin') {
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'User promoted to Admin.');
    } elseif ($_GET['action'] === 'remove_admin') {
        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Admin privileges removed.');
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'User account deleted.');
    }
    redirect('users.php');
}

// Fetch Users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="admin-breadcrumb"><a href="index.php">Dashboard</a> › Users</div>
            <h1>User Management</h1>
        </div>
        <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 1rem;"><?php echo count($users); ?> Registered Users</span>
    </div>

    <div class="card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="admin-row-meta">
                                <div class="admin-avatar"><?php echo strtoupper(substr($u['username'], 0, 1)); ?></div>
                                <div>
                                    <div style="font-weight: 700;">@<?php echo sanitize($u['username']); ?></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">UID #<?php echo $u['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo sanitize($u['email']); ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge badge-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Member</span>
                            <?php endif; ?>
                        </td>
                        <td class="muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <div class="admin-actions">
                                <?php if ($u['role'] === 'admin'): ?>
                                    <a href="?action=remove_admin&id=<?php echo $u['id']; ?>" class="btn btn-secondary btn-sm">Demote</a>
                                <?php else: ?>
                                    <a href="?action=make_admin&id=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm">Make Admin</a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user account? This cannot be undone.')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
