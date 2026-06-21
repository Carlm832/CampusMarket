<?php
/**
 * Shared helpers for admin report moderation.
 */

if (!function_exists('reportsAccountStatusSupported')) {
    function reportsAccountStatusSupported(PDO $pdo): bool {
        static $supported = null;
        if ($supported !== null) {
            return $supported;
        }
        try {
            $stmt = $pdo->query("
                SELECT 1 FROM information_schema.columns
                WHERE table_schema = 'public' AND table_name = 'users' AND column_name = 'account_status'
                LIMIT 1
            ");
            $supported = (bool)($stmt && $stmt->fetchColumn());
        } catch (Throwable $e) {
            $supported = false;
        }
        return $supported;
    }
}

if (!function_exists('reportFetchForAdmin')) {
    function reportFetchForAdmin(PDO $pdo, int $reportId): ?array {
        try {
            $stmt = $pdo->prepare("
                SELECT r.*,
                       p.title AS product_title,
                       p.user_id AS seller_id,
                       su.username AS seller_name,
                       ru.username AS reported_username,
                       u.username AS reporter_name
                FROM reports r
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users su ON p.user_id = su.id
                LEFT JOIN users ru ON r.reported_user_id = ru.id
                LEFT JOIN users u ON r.reporter_id = u.id
                WHERE r.id = :id
                LIMIT 1
            ");
        } catch (PDOException $e) {
            $stmt = $pdo->prepare("
                SELECT r.*,
                       p.title AS product_title,
                       p.user_id AS seller_id,
                       su.username AS seller_name,
                       NULL AS reported_username,
                       u.username AS reporter_name
                FROM reports r
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users su ON p.user_id = su.id
                LEFT JOIN users u ON r.reporter_id = u.id
                WHERE r.id = :id
                LIMIT 1
            ");
        }
        $stmt->execute([':id' => $reportId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

if (!function_exists('reportTargetUserId')) {
    function reportTargetUserId(array $report): ?int {
        if (!empty($report['reported_user_id'])) {
            return (int)$report['reported_user_id'];
        }
        if (!empty($report['seller_id'])) {
            return (int)$report['seller_id'];
        }
        return null;
    }
}

if (!function_exists('reportSetStatus')) {
    function reportSetStatus(PDO $pdo, int $reportId, string $status): void {
        $allowed = ['pending', 'reviewed', 'dismissed'];
        if (!in_array($status, $allowed, true)) {
            return;
        }
        $pdo->prepare('UPDATE reports SET status = ? WHERE id = ?')->execute([$status, $reportId]);
    }
}

if (!function_exists('reportNotifyReporter')) {
    function reportNotifyReporter(PDO $pdo, array $report, string $title, string $body): void {
        $reporterId = (int)($report['reporter_id'] ?? 0);
        if ($reporterId <= 0) {
            return;
        }
        createNotification($pdo, $reporterId, 'system', $title, $body, (int)$report['id']);
    }
}

if (!function_exists('reportFlagProduct')) {
    function reportFlagProduct(PDO $pdo, int $productId): void {
        if ($productId <= 0) {
            return;
        }
        $pdo->prepare("UPDATE products SET status = 'flagged' WHERE id = ?")->execute([$productId]);
    }
}

if (!function_exists('reportWarnUser')) {
    function reportWarnUser(PDO $pdo, int $userId, string $body): void {
        if ($userId <= 0) {
            return;
        }
        createNotification(
            $pdo,
            $userId,
            'system',
            __('admin.report_warn_user_title'),
            $body
        );
    }
}

if (!function_exists('reportSuspendUser')) {
    function reportSuspendUser(PDO $pdo, int $userId): bool {
        if ($userId <= 0 || !reportsAccountStatusSupported($pdo)) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || ($user['role'] ?? '') === 'admin') {
            return false;
        }

        $pdo->prepare("UPDATE users SET account_status = 'suspended' WHERE id = ?")->execute([$userId]);
        createNotification(
            $pdo,
            $userId,
            'system',
            __('admin.report_suspend_user_title'),
            __('admin.report_suspend_user_body')
        );
        return true;
    }
}

if (!function_exists('reportUnsuspendUser')) {
    function reportUnsuspendUser(PDO $pdo, int $userId): bool {
        if ($userId <= 0 || !reportsAccountStatusSupported($pdo)) {
            return false;
        }
        $pdo->prepare("UPDATE users SET account_status = 'active' WHERE id = ?")->execute([$userId]);
        return true;
    }
}

if (!function_exists('isUserSuspended')) {
    function isUserSuspended(PDO $pdo, int $userId): bool {
        if ($userId <= 0 || !reportsAccountStatusSupported($pdo)) {
            return false;
        }
        $stmt = $pdo->prepare('SELECT account_status FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() === 'suspended';
    }
}

if (!function_exists('enforceActiveAccount')) {
    function enforceActiveAccount(PDO $pdo): void {
        if (!isLoggedIn() || isAdmin()) {
            return;
        }
        $uid = currentUserId();
        if ($uid && isUserSuspended($pdo, $uid)) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            setcookie('campusmarket_sess_stateless', '', ['expires' => time() - 3600, 'path' => '/']);
            session_destroy();
            session_name(SESSION_NAME);
            session_start();
            setFlash('error', __('auth.error_suspended'));
            redirect(BASE_URL . 'pages/login.php');
        }
    }
}
