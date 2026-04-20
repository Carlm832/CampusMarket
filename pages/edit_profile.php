<?php
// pages/edit_profile.php — Member 2
// Update your own username, phone, and avatar.
// Email + password are NOT editable here (per project spec).

require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/functions_member2.php';

requireLogin();

$uid = (int) currentUserId();

// Load current user.
$stmt = $pdo->prepare('SELECT id, username, email, phone, avatar FROM users WHERE id = :id');
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch();

if (!$user) {
    // Session points at a user that no longer exists — nuke it.
    $_SESSION = [];
    session_destroy();
    redirect(BASE_URL . '/pages/login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    // Username
    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors['username'] = '3–50 characters. Letters, numbers, underscores only.';
    } elseif (strcasecmp($username, $user['username']) !== 0) {
        // Only hit DB when the value actually changed.
        $chk = $pdo->prepare('SELECT id FROM users WHERE username = :u AND id != :id LIMIT 1');
        $chk->execute([':u' => $username, ':id' => $uid]);
        if ($chk->fetch()) {
            $errors['username'] = 'That username is already taken.';
        }
    }

    // Phone (optional)
    if ($phone !== '' && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone)) {
        $errors['phone'] = 'Phone number looks invalid.';
    }

    // Avatar upload — use Member 1's uploadImage() helper.
    $newAvatar = null;
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploaded = uploadImage($_FILES['avatar'], 'avatars');
        if ($uploaded === false) {
            $errors['avatar'] = 'Avatar upload failed. Use JPEG/PNG/WebP/GIF under 5 MB.';
        } else {
            $newAvatar = $uploaded;
        }
    }

    // Persist
    if (!$errors) {
        if ($newAvatar !== null) {
            // Best-effort cleanup of the old avatar file.
            if (!empty($user['avatar'])) {
                $oldAbs = ROOT_PATH . 'public/' . $user['avatar'];
                if (is_file($oldAbs)) @unlink($oldAbs);
            }
            $upd = $pdo->prepare('
                UPDATE users
                SET username = :u, phone = :p, avatar = :a
                WHERE id = :id
            ');
            $upd->execute([
                ':u'  => $username,
                ':p'  => $phone !== '' ? $phone : null,
                ':a'  => $newAvatar,
                ':id' => $uid,
            ]);
        } else {
            $upd = $pdo->prepare('
                UPDATE users
                SET username = :u, phone = :p
                WHERE id = :id
            ');
            $upd->execute([
                ':u'  => $username,
                ':p'  => $phone !== '' ? $phone : null,
                ':id' => $uid,
            ]);
        }

        // Keep session in sync with the new username.
        $_SESSION['username'] = $username;

        setFlash('success', 'Profile updated.');
        redirect(BASE_URL . '/pages/profile.php');
    }

    // On error, keep typed values visible.
    $user['username'] = $username;
    $user['phone']    = $phone;
}

$pageTitle = 'Edit profile';
require_once '../includes/header.php';
?>

<style>
  .edit-card   { max-width:640px; margin:1rem auto; background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:2rem; }
  .edit-head   { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
  .avatar-row  { display:flex; gap:1rem; align-items:center; margin-bottom:1.5rem; }
  .avatar-lg   { width:96px; height:96px; object-fit:cover; border-radius:50%; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,.06); background:#f1f5f9; }
  .form-row    { margin-bottom:1rem; }
  .form-row label { display:block; font-weight:500; margin-bottom:0.35rem; }
  .form-row input { width:100%; padding:0.55rem 0.75rem; border:1px solid #cbd5e1; border-radius:0.375rem; font-size:1rem; box-sizing:border-box; }
  .form-row input:focus { outline:2px solid var(--primary); border-color:transparent; }
  .form-row input:disabled { background:#f1f5f9; color:#64748b; }
  .form-row input.is-invalid { border-color:#dc2626; }
  .form-row .hint  { color:#64748b; font-size:0.85rem; margin-top:0.25rem; }
  .form-row .error { color:#b91c1c; font-size:0.85rem; margin-top:0.25rem; }
  .btn-link    { color:#64748b; text-decoration:none; }
</style>

<div class="edit-card">
  <div class="edit-head">
    <h1 style="margin:0;">Edit profile</h1>
    <a class="btn-link" href="<?php echo BASE_URL; ?>/pages/profile.php">Cancel</a>
  </div>

  <form method="post" enctype="multipart/form-data" novalidate>
    <div class="avatar-row">
      <img class="avatar-lg"
           src="<?php echo sanitize(avatarUrl($user['avatar'])); ?>"
           alt="Current avatar">
      <div style="flex:1;">
        <label for="avatar">Change avatar</label>
        <input type="file" id="avatar" name="avatar"
               accept="image/jpeg,image/png,image/webp,image/gif"
               class="<?php echo isset($errors['avatar']) ? 'is-invalid' : ''; ?>">
        <?php if (isset($errors['avatar'])): ?>
          <div class="error"><?php echo sanitize($errors['avatar']); ?></div>
        <?php else: ?>
          <div class="hint">JPEG, PNG, WebP, or GIF · max 5 MB.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-row">
      <label for="username">Username</label>
      <input type="text" id="username" name="username"
             value="<?php echo sanitize($user['username']); ?>"
             maxlength="50" required
             class="<?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>">
      <?php if (isset($errors['username'])): ?>
        <div class="error"><?php echo sanitize($errors['username']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label for="phone">Phone</label>
      <input type="tel" id="phone" name="phone"
             value="<?php echo sanitize($user['phone'] ?? ''); ?>"
             maxlength="20"
             class="<?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>">
      <?php if (isset($errors['phone'])): ?>
        <div class="error"><?php echo sanitize($errors['phone']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label>Email</label>
      <input type="email" value="<?php echo sanitize($user['email']); ?>" disabled>
      <div class="hint">Email changes not supported yet.</div>
    </div>

    <button type="submit" class="btn">Save changes</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
