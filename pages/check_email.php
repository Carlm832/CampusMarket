<?php
// pages/check_email.php — Post-signup verification instructions + resend link

require_once '../config/constants.php';
require_once '../includes/bootstrap.php';
require_once '../includes/functions_member2.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'pages/profile.php');
}

$pendingEmail = strtolower(trim((string) ($_SESSION['pending_verify_email'] ?? '')));
if ($pendingEmail === '' || !filter_var($pendingEmail, FILTER_VALIDATE_EMAIL)) {
    redirect(BASE_URL . 'pages/register.php');
}

$resendMessage = '';
$resendError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $result = resendSignupVerificationEmail($pendingEmail);
    if ($result['ok']) {
        $resendMessage = $result['message'] ?? 'Verification email sent.';
    } else {
        $resendError = $result['error'] ?? 'Could not resend verification email.';
    }
}

$pageTitle = 'Verify your email';
require_once '../includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-head text-center mb-8">
      <div class="verify-email-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="20" height="16" x="2" y="4" rx="2"/>
          <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
        </svg>
      </div>
      <h1>Check your email</h1>
      <p>We sent a verification link to <strong><?php echo sanitize($pendingEmail); ?></strong>.</p>
    </div>

    <?php if ($resendMessage !== ''): ?>
      <div class="flash flash-success mb-6"><?php echo sanitize($resendMessage); ?></div>
    <?php endif; ?>
    <?php if ($resendError !== ''): ?>
      <div class="flash flash-error mb-6"><?php echo sanitize($resendError); ?></div>
    <?php endif; ?>

    <div class="verify-email-tips mb-8">
      <p><strong>Next steps</strong></p>
      <ol>
        <li>Open your university inbox and look for an email from CampusMarket.</li>
        <li>Click the verification link in that email.</li>
        <li>Come back here and log in once verified.</li>
      </ol>
      <p class="verify-email-spam-tip">
        Don&rsquo;t see it within a few minutes? Check your <strong>Spam</strong> or <strong>Junk</strong> folder and mark the message as &ldquo;Not spam&rdquo; so future emails arrive in your inbox.
      </p>
      <p class="verify-email-spam-tip" style="border-top: none; padding-top: 0.5rem;">
        After you log in, you can enable <strong>phone notifications</strong> for new messages and marketplace activity.
      </p>
    </div>

    <form method="post" class="mb-6">
      <?php echo csrfTokenField(); ?>
      <button type="submit" class="btn btn-secondary w-full py-3" style="border-radius: var(--radius-md); font-weight: 600;">
        Resend verification email
      </button>
    </form>

    <a href="<?php echo BASE_URL; ?>pages/login.php?redirect=/pages/profile.php" class="btn btn-primary w-full py-4 shadow-sm" style="border-radius: var(--radius-md); font-weight: 600; font-size: 1.05rem; letter-spacing: 0.01em; text-align: center; display: block; text-decoration: none;">
      I&rsquo;ve verified &mdash; log in
    </a>

    <p class="auth-foot mt-10">
      Wrong email?
      <a href="<?php echo BASE_URL; ?>pages/register.php" style="font-weight: 600;">Sign up again</a>
    </p>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
