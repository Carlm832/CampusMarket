<?php
// pages/register.php — Member 2
// Create a new CampusMarket account. Uses Member 1's foundation
// (bootstrap.php, sanitize, setFlash, redirect, $pdo).

require_once '../config/constants.php';
require_once '../includes/bootstrap.php';

// If already logged in, send them home.
if (isLoggedIn()) {
    redirect(BASE_URL . 'pages/profile.php');
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
        redirect(BASE_URL . 'pages/profile.php');
    }
}

$pageTitle = 'Create account';
require_once '../includes/header.php';
?>

<div class="container relative mt-16 mb-20 flex justify-center">
    <!-- Decorative elements -->
    <div style="position: absolute; top: -50px; left: 15%; width: 250px; height: 250px; border-radius: 50%; background: linear-gradient(135deg, var(--secondaryLight), var(--primaryLight)); opacity: 0.2; filter: blur(40px); z-index: -1;"></div>
    <div style="position: absolute; bottom: -50px; right: 15%; width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #93c5fd); opacity: 0.15; filter: blur(40px); z-index: -1;"></div>

    <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 2.5rem; text-align: left; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); z-index: 10;">
        <div class="text-center mb-6">
            <h1 class="gradient-text mb-2" style="font-size: 2.2rem;">Join the Market</h1>
            <p class="text-muted text-md">Begin trading safely with your campus community</p>
        </div>

        <form method="post" novalidate class="flex flex-col gap-4">
            <div>
                <label for="username" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main);">Username</label>
                <input type="text" id="username" name="username"
                       value="<?php echo sanitize($old['username']); ?>"
                       maxlength="50" required autofocus autocomplete="username"
                       class="form-control premium-input w-full <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
                       placeholder="e.g. campus_trader"
                       style="padding: 0.7rem 1rem; <?php echo isset($errors['username']) ? 'border-color: #ef4444; background-color: #fef2f2;' : ''; ?>">
                <?php if (isset($errors['username'])): ?>
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; font-weight: 500;"><?php echo sanitize($errors['username']); ?></div>
                <?php else: ?>
                    <div class="text-muted" style="font-size: 0.8rem; margin-top: 0.3rem;">3–50 chars (letters, numbers, underscores).</div>
                <?php endif; ?>
            </div>

            <div>
                <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main);">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo sanitize($old['email']); ?>"
                       maxlength="100" required autocomplete="email"
                       class="form-control premium-input w-full <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                       placeholder="e.g. you@university.edu"
                       style="padding: 0.7rem 1rem; <?php echo isset($errors['email']) ? 'border-color: #ef4444; background-color: #fef2f2;' : ''; ?>">
                <?php if (isset($errors['email'])): ?>
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; font-weight: 500;"><?php echo sanitize($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="phone" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main);">Phone <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo sanitize($old['phone']); ?>"
                       maxlength="20" autocomplete="tel"
                       class="form-control premium-input w-full <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                       placeholder="+1 (555) 000-0000"
                       style="padding: 0.7rem 1rem; <?php echo isset($errors['phone']) ? 'border-color: #ef4444; background-color: #fef2f2;' : ''; ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; font-weight: 500;"><?php echo sanitize($errors['phone']); ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="password" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main);">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password"
                           minlength="8" required autocomplete="new-password"
                           class="form-control premium-input w-full <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                           placeholder="••••••••"
                           style="padding: 0.7rem 3rem 0.7rem 1rem; <?php echo isset($errors['password']) ? 'border-color: #ef4444; background-color: #fef2f2;' : ''; ?>">
                    <button type="button" class="password-toggle" data-target="password" aria-label="Show password" 
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0.2rem; display: flex; align-items: center; justify-content: center; transition: color 0.2s;">
                        <svg class="icon-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 1.2rem; height: 1.2rem;"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 1.2rem; height: 1.2rem; display: none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a19.8 19.8 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c6.5 0 10 7 10 7a19.9 19.9 0 0 1-3.17 4.19"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M1 1l22 22"/></svg>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; font-weight: 500;"><?php echo sanitize($errors['password']); ?></div>
                <?php else: ?>
                    <div class="text-muted" style="font-size: 0.8rem; margin-top: 0.3rem;">Min. 8 characters, mixing letters & numbers.</div>
                <?php endif; ?>
            </div>

            <div>
                <label for="password_confirm" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-main);">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" id="password_confirm" name="password_confirm"
                           minlength="8" required autocomplete="new-password"
                           class="form-control premium-input w-full <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>"
                           placeholder="••••••••"
                           style="padding: 0.7rem 3rem 0.7rem 1rem; <?php echo isset($errors['password_confirm']) ? 'border-color: #ef4444; background-color: #fef2f2;' : ''; ?>">
                    <button type="button" class="password-toggle" data-target="password_confirm" aria-label="Show password" 
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0.2rem; display: flex; align-items: center; justify-content: center; transition: color 0.2s;">
                        <svg class="icon-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 1.2rem; height: 1.2rem;"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 1.2rem; height: 1.2rem; display: none;"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a19.8 19.8 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c6.5 0 10 7 10 7a19.9 19.9 0 0 1-3.17 4.19"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M1 1l22 22"/></svg>
                    </button>
                </div>
                <?php if (isset($errors['password_confirm'])): ?>
                    <div style="color: #ef4444; font-size: 0.85rem; margin-top: 0.3rem; font-weight: 500;"><?php echo sanitize($errors['password_confirm']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-full hover-scale shadow-lg" style="margin-top: 1rem; padding: 1rem; font-size: 1.1rem; border-radius: var(--radius-full); font-weight: bold;">Create Account</button>
        </form>

        <p class="text-center mt-6 text-muted" style="font-size: 0.95rem;">
            Already have an account?
            <a href="<?php echo BASE_URL; ?>/pages/login.php" style="color: var(--primary); font-weight: 600; text-decoration: none; margin-left: 0.25rem;">Log in here</a>
        </p>
    </div>
</div>

<script>
  document.querySelectorAll('.password-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.target);
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      
      const iconShow = btn.querySelector('.icon-show');
      const iconHide = btn.querySelector('.icon-hide');
      
      if (show) {
          iconShow.style.display = 'none';
          iconHide.style.display = 'block';
      } else {
          iconShow.style.display = 'block';
          iconHide.style.display = 'none';
      }
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
</script>

<?php require_once '../includes/footer.php'; ?>
