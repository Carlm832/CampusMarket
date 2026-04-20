<?php
/**
 * includes/functions_member2.php
 *
 * Member 2 — Authentication & User Management helpers.
 * Only functions specific to profile pages live here. General helpers
 * (sanitize, setFlash, requireLogin, uploadImage…) come from Member 1's
 * includes/functions.php.
 */

/**
 * Build a public URL for a user avatar.
 * $avatarPath is whatever uploadImage() stored in users.avatar
 * (e.g. "uploads/avatars/img_680...jpg").
 * Falls back to a neutral Gravatar placeholder when empty.
 */
function avatarUrl(?string $avatarPath): string {
    if (!empty($avatarPath)) {
        return BASE_URL . '/public/' . ltrim($avatarPath, '/');
    }
    return 'https://www.gravatar.com/avatar/?d=mp&s=200';
}

/**
 * Seller rating summary. Returns [avg, count].
 */
function sellerRatingSummary(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare('
        SELECT AVG(rating) AS avg_rating, COUNT(*) AS rating_count
        FROM ratings
        WHERE seller_id = :uid
    ');
    $stmt->execute([':uid' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'avg'   => $row && $row['avg_rating'] !== null ? (float) $row['avg_rating'] : 0.0,
        'count' => $row ? (int) $row['rating_count'] : 0,
    ];
}

/**
 * Render star glyphs for a rating (0–5, half-star supported).
 * Returns a plain HTML string using ★/☆ characters so no icon font needed.
 */
function renderStars(float $avg): string {
    $full = (int) floor($avg);
    $half = ($avg - $full) >= 0.5;
    $html = '';
    for ($i = 0; $i < $full; $i++)              $html .= '★';
    if ($half)                                  $html .= '⯨';
    for ($i = $full + ($half ? 1 : 0); $i < 5; $i++) $html .= '☆';
    return $html;
}

/**
 * "Joined Apr 2026" style date for profile header.
 */
function formatJoinDate(string $timestamp): string {
    $ts = strtotime($timestamp);
    return $ts ? date('M Y', $ts) : 'Unknown';
}
