<?php
// pages/profile.php — Member 2
// View a user profile: avatar, username, join date, average rating.
// Defaults to the logged-in user. ?id=N shows another user.

require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/functions_member2.php';

// Decide which profile to show.
$viewId = isset($_GET['id']) ? (int) $_GET['id'] : (int) (currentUserId() ?? 0);

// If nobody specified and not logged in, require login.
if ($viewId <= 0) {
    requireLogin();
    $viewId = (int) currentUserId();
}

$stmt = $pdo->prepare('
    SELECT id, username, email, role, phone, avatar, created_at
    FROM users
    WHERE id = :id
    LIMIT 1
');
$stmt->execute([':id' => $viewId]);
$user = $stmt->fetch();

$pageTitle = $user ? $user['username'] . "'s profile" : 'User not found';
require_once '../includes/header.php';
?>

<?php if (!$user): ?>
  <div class="alert-error" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:1rem;border-radius:0.5rem;">
    No user with id <?php echo (int) $viewId; ?>.
  </div>
<?php else:
    $isSelf  = isLoggedIn() && (int) currentUserId() === (int) $user['id'];
    $rating  = sellerRatingSummary($pdo, (int) $user['id']);
?>

<style>
  .profile-card { background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:2rem; margin-bottom:1.5rem; }
  .profile-head { display:flex; gap:1.5rem; align-items:center; flex-wrap:wrap; }
  .avatar-lg    { width:120px; height:120px; object-fit:cover; border-radius:50%; border:3px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,.08); background:#f1f5f9; }
  .admin-badge  { background:#111827; color:#fff; font-size:0.75rem; padding:0.15rem 0.55rem; border-radius:9999px; margin-left:0.5rem; letter-spacing:0.04em; }
  .stars        { color:#f59e0b; font-size:1.1rem; letter-spacing:0.08em; }
  .dim          { color:#64748b; }
  .meta dt      { color:#64748b; font-weight:400; }
  .meta dd      { margin:0 0 0.75rem 0; }
  .meta dl      { display:grid; grid-template-columns: 8rem 1fr; gap:0.5rem 1rem; margin:0; }
  .profile-actions { margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap; }
  .btn-outline  { background:#fff; color:var(--primary); border:1px solid var(--primary); }
</style>

<div class="profile-card">
  <div class="profile-head">
    <img class="avatar-lg"
         src="<?php echo sanitize(avatarUrl($user['avatar'])); ?>"
         alt="<?php echo sanitize($user['username']); ?>'s avatar">

    <div style="flex:1; min-width:260px;">
      <h1 style="margin:0 0 0.25rem 0;">
        <?php echo sanitize($user['username']); ?>
        <?php if ($user['role'] === 'admin'): ?>
          <span class="admin-badge">ADMIN</span>
        <?php endif; ?>
      </h1>

      <p class="dim" style="margin:0 0 0.75rem 0;">
        Joined <?php echo sanitize(formatJoinDate($user['created_at'])); ?>
      </p>

      <div>
        <?php if ($rating['count'] > 0): ?>
          <span class="stars" aria-label="<?php echo number_format($rating['avg'], 1); ?> out of 5">
            <?php echo renderStars($rating['avg']); ?>
          </span>
          <strong style="margin-left:0.4rem;"><?php echo number_format($rating['avg'], 1); ?></strong>
          <span class="dim" style="font-size:0.9rem;">
            (<?php echo (int) $rating['count']; ?>
            rating<?php echo $rating['count'] === 1 ? '' : 's'; ?>)
          </span>
        <?php else: ?>
          <span class="dim">No ratings yet</span>
        <?php endif; ?>
      </div>

      <?php if ($isSelf): ?>
        <div class="profile-actions">
          <a href="<?php echo BASE_URL; ?>/pages/edit_profile.php" class="btn btn-outline">Edit profile</a>
          <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-outline">Log out</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($isSelf): ?>
  <!-- Visible only to the account owner. -->
  <div class="profile-card">
    <h2 style="font-size:1rem; text-transform:uppercase; letter-spacing:0.06em; color:#64748b; margin-top:0;">
      Account details
    </h2>
    <div class="meta">
      <dl>
        <dt>Email</dt>
        <dd><?php echo sanitize($user['email']); ?></dd>

        <dt>Phone</dt>
        <dd>
          <?php echo $user['phone']
              ? sanitize($user['phone'])
              : '<span class="dim">—</span>'; ?>
        </dd>

        <dt>Member since</dt>
        <dd><?php echo sanitize(date('F j, Y', strtotime($user['created_at']))); ?></dd>
      </dl>
    </div>
  </div>
<?php endif; ?>

<?php endif; // $user exists ?>

<?php require_once '../includes/footer.php'; ?>
