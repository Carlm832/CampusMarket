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
}

// Fetch Users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="container mt-8">
    <div class="flex justify-between items-center mb-8">
        <h1>User Management</h1>
        <div class="badge badge-info"><?php echo count($users); ?> Registered Users</div>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4">User</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Joined</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="border-b hover:bg-gray-100 transition-all">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div style="width: 40px; height: 40px; background: var(--bg-main); border-radius: var(--radius-full); display: flex; align-items: center; justify-center; font-weight: bold;">
                                    <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-bold">@<?php echo sanitize($u['username']); ?></div>
                                    <div class="text-muted small">UID: #<?php echo $u['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4"><?php echo sanitize($u['email']); ?></td>
                        <td class="p-4">
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge badge-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Member</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-muted small"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
