<?php
// pages/register.php — Member 2
// Create a new CampusMarket account. Uses Member 1's foundation
// (bootstrap.php, sanitize, setFlash, redirect, $pdo).

require_once '../config/constants.php';
require_once '../includes/bootstrap.php';

// If already logged in, send them home.
if (isLoggedIn()) {
    redirect(BASE_URL . '/pages/profile.php');
}

$errors = [];
$old    = ['username' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Gather + normalize
    $username = trim($_POST['username'] ?? '');
    $email    = trim(strtolower($_POST['email'] ?? ''));
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    $old['username'] = $username;
    $old['email']    = $email;
    $old['phone']    = $phone;

    // Validate username
    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors['username'] = '3–50 characters. Letters, numbers, underscores only.';
    }

    // Validate email
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Validate phone (optional)
    if ($phone !== '' && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone)) {
        $errors['phone'] = 'Phone number looks invalid.';
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must contain at least one letter and one number.';
    }
    if ($password !== $confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    // Uniqueness check (case-insensitive on email)
    if (!$errors) {
        $stmt = $pdo->prepare('
            SELECT username, email FROM users
            WHERE username = :u OR LOWER(email) = LOWER(:e)
            LIMIT 1
        ');
        $stmt->execute([':u' => $username, ':e' => $email]);
        $dup = $stmt->fetch();
        if ($dup) {
            if (strcasecmp($dup['username'], $username) === 0) {
                $errors['username'] = 'That username is already taken.';
            }
            if (strcasecmp($dup['email'], $email) === 0) {
                $errors['email'] = 'An account with that email already exists.';
            }
        }
    }

    // Persist + auto-login
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $ins = $pdo->prepare('
            INSERT INTO users (username, email, password_hash, role, phone)
            VALUES (:u, :e, :h, "user", :p)
        ');
        $ins->execute([
            ':u' => $username,
            ':e' => $email,
            ':h' => $hash,
            ':p' => $phone !== '' ? $phone : null,
        ]);

        $newId = (int) $pdo->lastInsertId();

        // Session fixation defense: fresh session id after auth.
        session_regenerate_id(true);
        $_SESSION['user_id']  = $newId;
        $_SESSION['role']     = 'user';
        $_SESSION['username'] = $username;

        setFlash('success', 'Welcome to CampusMarket, ' . sanitize($username) . '!');
        redirect(BASE_URL . '/pages/profile.php');
    }
}

$pageTitle = 'Create account';
require_once '../includes/header.php';
?>

<style>
  .auth-card { max-width: 480px; margin: 2rem auto; background: #fff; border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 2rem; }
  .auth-card h1 { margin-top: 0; }
  .form-row { margin-bottom: 1rem; }
  .form-row label { display: block; font-weight: 500; margin-bottom: 0.35rem; }
  .form-row input { width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; font-size: 1rem; box-sizing: border-box; }
  .form-row input:focus { outline: 2px solid var(--primary); border-color: transparent; }
  .form-row .hint { color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; }
  .form-row .error { color: #b91c1c; font-size: 0.85rem; margin-top: 0.25rem; }
  .form-row input.is-invalid { border-color: #dc2626; }
  .btn-full { width: 100%; padding: 0.7rem; font-size: 1rem; }
  .auth-foot { text-align: center; color: #64748b; margin-top: 1.25rem; font-size: 0.9rem; }

  /* Password eye toggle */
  .password-wrap { position: relative; }
  .password-wrap input { padding-right: 2.6rem; }
  .password-toggle {
    position: absolute; top: 50%; right: 0.4rem; transform: translateY(-50%);
    background: none; border: 0; padding: 0.35rem;
    color: #64748b; cursor: pointer; display: flex; align-items: center;
    border-radius: 0.25rem;
  }
  .password-toggle:hover       { color: #0f172a; }
  .password-toggle:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
  .password-toggle svg         { width: 1.15rem; height: 1.15rem; }
  .password-toggle .icon-hide  { display: none; }
  .password-toggle.is-shown .icon-show { display: none; }
  .password-toggle.is-shown .icon-hide { display: block; }
</style>

<div class="auth-card">
  <h1>Create your account</h1>
  <p style="color:#64748b; margin-top:-0.25rem;">Join the campus marketplace in 30 seconds.</p>

  <form method="post" novalidate>
    <div class="form-row">
      <label for="username">Username</label>
      <input type="text" id="username" name="username"
             value="<?php echo sanitize($old['username']); ?>"
             maxlength="50" required autofocus autocomplete="username"
             class="<?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>">
      <?php if (isset($errors['username'])): ?>
        <div class="error"><?php echo sanitize($errors['username']); ?></div>
      <?php else: ?>
        <div class="hint">3–50 characters. Letters, numbers, underscores.</div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label for="email">Email</label>
      <input type="email" id="email" name="email"
             value="<?php echo sanitize($old['email']); ?>"
             maxlength="100" required autocomplete="email"
             class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
      <?php if (isset($errors['email'])): ?>
        <div class="error"><?php echo sanitize($errors['email']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label for="phone">Phone <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
      <input type="tel" id="phone" name="phone"
             value="<?php echo sanitize($old['phone']); ?>"
             maxlength="20" autocomplete="tel"
             class="<?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>">
      <?php if (isset($errors['phone'])): ?>
        <div class="error"><?php echo sanitize($errors['phone']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label for="password">Password</label>
      <div class="password-wrap">
        <input type="password" id="password" name="password"
               minlength="8" required autocomplete="new-password"
               class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
        <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
          <svg class="icon-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
          <svg class="icon-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a19.8 19.8 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c6.5 0 10 7 10 7a19.9 19.9 0 0 1-3.17 4.19"/>
            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
            <path d="M1 1l22 22"/>
          </svg>
        </button>
      </div>
      <?php if (isset($errors['password'])): ?>
        <div class="error"><?php echo sanitize($errors['password']); ?></div>
      <?php else: ?>
        <div class="hint">At least 8 characters, mixing letters and numbers.</div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label for="password_confirm">Confirm password</label>
      <div class="password-wrap">
        <input type="password" id="password_confirm" name="password_confirm"
               minlength="8" required autocomplete="new-password"
               class="<?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>">
        <button type="button" class="password-toggle" data-target="password_confirm" aria-label="Show password">
          <svg class="icon-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
          <svg class="icon-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a19.8 19.8 0 0 1 5.06-5.94"/>
            <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c6.5 0 10 7 10 7a19.9 19.9 0 0 1-3.17 4.19"/>
            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
            <path d="M1 1l22 22"/>
          </svg>
        </button>
      </div>
      <?php if (isset($errors['password_confirm'])): ?>
        <div class="error"><?php echo sanitize($errors['password_confirm']); ?></div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-full">Create account</button>
  </form>

  <p class="auth-foot">
    Already have an account?
    <a href="<?php echo BASE_URL; ?>/pages/login.php">Log in</a>
  </p>
</div>

<script>
  document.querySelectorAll('.password-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.target);
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('is-shown', show);
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
</script>

<?php require_once '../includes/footer.php'; ?>
