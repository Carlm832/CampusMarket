<?php
// pages/verify_email.php
// Handles Supabase email confirmation callback and keeps a legacy token fallback.

require_once '../config/constants.php';
require_once '../includes/bootstrap.php';

if (($_GET['source'] ?? '') === 'supabase') {
    $tokenHash = trim((string)($_GET['token_hash'] ?? ''));
    $type = trim((string)($_GET['type'] ?? 'email'));

    if ($tokenHash === '') {
        setFlash('error', 'Invalid verification link.');
        redirect(BASE_URL . 'pages/login.php');
    }

    $verify = supabaseAuthRequest('POST', 'verify', [
        'token_hash' => $tokenHash,
        'type' => $type,
    ]);

    if (!$verify['ok']) {
        setFlash('error', 'Verification failed or link expired. Please request a new verification email.');
        redirect(BASE_URL . 'pages/login.php');
    }

    $supabaseUser = $verify['data']['user'] ?? [];
    $verifiedEmail = strtolower(trim((string)($supabaseUser['email'] ?? '')));

    if ($verifiedEmail !== '') {
        $upd = $pdo->prepare('UPDATE users SET is_verified = TRUE WHERE LOWER(email) = LOWER(:e)');
        $upd->execute([':e' => $verifiedEmail]);
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Verified - CampusMarket</title>
        <style>
            body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #0f172a; }
            .card { max-width: 520px; margin: 40px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; }
            h1 { margin: 0 0 12px 0; font-size: 24px; }
            p { margin: 0 0 16px 0; color: #334155; line-height: 1.55; }
            a { display: inline-block; margin-top: 8px; background: #0f172a; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 8px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Email verified</h1>
            <p>Your email is confirmed. You can close this page and go back to CampusMarket.</p>
            <p>If you prefer, you can continue directly to login now.</p>
            <a href="<?php echo BASE_URL; ?>pages/login.php">Go to login</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$token = trim($_GET['token'] ?? '');

// Reject obviously malformed tokens before touching the DB.
if ($token === '' || !preg_match('/^[a-f0-9]{40,128}$/i', $token)) {
    setFlash('error', 'Invalid verification link.');
    redirect(BASE_URL . 'pages/login.php');
}

$stmt = $pdo->prepare('
    SELECT v.id, v.user_id, v.expires_at, u.is_verified, u.email
    FROM email_verifications v
    JOIN users u ON u.id = v.user_id
    WHERE v.token = :t
    LIMIT 1
');
$stmt->execute([':t' => $token]);
$row = $stmt->fetch();

if (!$row) {
    setFlash('error', 'This verification link is invalid or has already been used.');
    redirect(BASE_URL . 'pages/login.php');
}

// Already-verified accounts get a friendly message instead of a confusing error.
if ((int) $row['is_verified'] === 1) {
    $del = $pdo->prepare('DELETE FROM email_verifications WHERE user_id = :u');
    $del->execute([':u' => $row['user_id']]);
    setFlash('success', 'Your email is already verified. Please log in.');
    redirect(BASE_URL . 'pages/login.php');
}

// Expired tokens — clean up and tell the user.
if (strtotime($row['expires_at']) < time()) {
    $del = $pdo->prepare('DELETE FROM email_verifications WHERE id = :id');
    $del->execute([':id' => $row['id']]);
    setFlash('error', 'This verification link has expired. Please register again to receive a new one.');
    redirect(BASE_URL . 'pages/login.php');
}

// Happy path — mark verified + nuke any tokens for this user.
try {
    $pdo->beginTransaction();

    $upd = $pdo->prepare('UPDATE users SET is_verified = TRUE WHERE id = :id');
    $upd->execute([':id' => $row['user_id']]);

    $del = $pdo->prepare('DELETE FROM email_verifications WHERE user_id = :u');
    $del->execute([':u' => $row['user_id']]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[verify_email] DB error: ' . $e->getMessage());
    setFlash('error', 'Something went wrong while verifying your email. Please try again.');
    redirect(BASE_URL . 'pages/login.php');
}

setFlash('success', 'Email verified! You can now log in.');
redirect(BASE_URL . 'pages/login.php');


