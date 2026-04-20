<?php
// ============================================================
// CampusMarket — Shared Utility Functions
// ============================================================

// ─── Input & Output ──────────────────────────────────────

/**
 * Sanitize user input to prevent XSS
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize user input (from local work)
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Redirect to a URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Set a flash message in session
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Display and clear the flash message (call in header or page)
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ─── Auth Helpers ────────────────────────────────────────

/**
 * Check if a user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get the current logged-in user's ID
 */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Require login — redirect to login page if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'You must be logged in to access that page.');
        redirect(BASE_URL . '/pages/login.php');
    }
}

/**
 * Require admin role — redirect to home if not admin
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'You do not have permission to access that page.');
        redirect(BASE_URL . '/index.php');
    }
}

// ─── Formatting ──────────────────────────────────────────

/**
 * Format a price in Turkish Lira (Local work)
 */
function formatPrice($amount): string {
    return number_format((float)$amount) . ' TL';
}

/**
 * Human-readable time ago (e.g., "3 hours ago")
 */
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year'   . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month'  . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'    . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'   . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get a condition badge label and CSS class
 */
function conditionBadge(string $condition): array {
    return match($condition) {
        'new'      => ['label' => 'New',      'class' => 'badge-new'],
        'like_new' => ['label' => 'Like New', 'class' => 'badge-like-new'],
        'used'     => ['label' => 'Used',     'class' => 'badge-used'],
        'poor'     => ['label' => 'Poor',     'class' => 'badge-poor'],
        default    => ['label' => 'Unknown',  'class' => 'badge-used'],
    };
}

// ─── File Upload ─────────────────────────────────────────

/**
 * Upload an image file and return the saved path (relative to public/uploads/)
 * Returns false on failure.
 */
function uploadImage(array $file, string $subfolder = 'products'): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size']  > MAX_FILE_SIZE)   return false;
    if (!in_array($file['type'], ALLOWED_TYPES)) return false;

    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename  = uniqid('img_', true) . '.' . strtolower($ext);
    $targetDir = UPLOAD_PATH . $subfolder . '/';
    $targetPath = $targetDir . $filename;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $subfolder . '/' . $filename;
    }
    return false;
}

// ─── Pagination ──────────────────────────────────────────

/**
 * Calculate offset for SQL LIMIT/OFFSET
 */
function getOffset(int $page, int $perPage = ITEMS_PER_PAGE): int {
    return ($page - 1) * $perPage;
}

/**
 * Render simple prev/next pagination links
 */
function paginationLinks(int $totalItems, int $currentPage, string $baseUrl): string {
    $totalPages = (int) ceil($totalItems / ITEMS_PER_PAGE);
    if ($totalPages <= 1) return '';

    $html  = '<div class="pagination">';
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="btn-page">← Prev</a>';
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $html  .= '<a href="' . $baseUrl . '?page=' . $i . '" class="btn-page' . $active . '">' . $i . '</a>';
    }
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="btn-page">Next →</a>';
    }
    $html .= '</div>';
    return $html;
}

// ─── Notifications ───────────────────────────────────────

/**
 * Create a notification for a user
 */
function createNotification(PDO $pdo, int $userId, string $type, string $title, string $body, ?int $referenceId = null): void {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, body, reference_id)
        VALUES (:user_id, :type, :title, :body, :ref_id)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':type'    => $type,
        ':title'   => $title,
        ':body'    => $body,
        ':ref_id'  => $referenceId,
    ]);
}

/**
 * Count unread notifications for a user
 */
function countUnreadNotifications(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
    $stmt->execute([':uid' => $userId]);
    return (int) $stmt->fetchColumn();
}
