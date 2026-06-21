<?php
// admin/reports.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
require_once __DIR__ . '/../includes/admin_audit.php';
require_once __DIR__ . '/../includes/report_moderation.php';

$pageTitle = __('admin.moderation_queue');
$currentAdminId = currentUserId();

$tab = sanitize($_GET['tab'] ?? 'pending');
$allowedTabs = ['pending', 'reviewed', 'dismissed'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'pending';
}

function reportsListForTab(PDO $pdo, string $tab): array {
    $status = in_array($tab, ['pending', 'reviewed', 'dismissed'], true) ? $tab : 'pending';
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
            WHERE r.status = :status
            ORDER BY r.created_at DESC
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
            WHERE r.status = :status
            ORDER BY r.created_at DESC
        ");
    }
    $stmt->execute([':status' => $status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['report_id'])) {
    verifyCsrfToken();
    $action = sanitize($_POST['action']);
    $reportId = (int)$_POST['report_id'];
    $returnTab = sanitize($_POST['return_tab'] ?? 'pending');
    if (!in_array($returnTab, $allowedTabs, true)) {
        $returnTab = 'pending';
    }

    $report = $reportId > 0 ? reportFetchForAdmin($pdo, $reportId) : null;
    if (!$report) {
        setFlash('error', __('admin.report_not_found'));
        redirect(BASE_URL . 'admin/reports.php?tab=' . urlencode($returnTab));
    }

    $targetUserId = reportTargetUserId($report);
    $productId = (int)($report['product_id'] ?? 0);

    if ($action === 'dismiss') {
        reportSetStatus($pdo, $reportId, 'dismissed');
        reportNotifyReporter($pdo, $report, __('admin.report_reporter_dismissed_title'), __('admin.report_reporter_dismissed_body'));
        setFlash('success', __('admin.flash_report_dismissed'));
        logAdminAction($pdo, 'dismiss_report', 'report', $reportId);
    } elseif ($action === 'resolve') {
        reportSetStatus($pdo, $reportId, 'reviewed');
        reportNotifyReporter($pdo, $report, __('admin.report_reporter_resolved_title'), __('admin.report_reporter_resolved_body'));
        setFlash('success', __('admin.flash_report_resolved'));
        logAdminAction($pdo, 'resolve_report', 'report', $reportId);
    } elseif ($action === 'flag' && $productId > 0) {
        reportFlagProduct($pdo, $productId);
        reportSetStatus($pdo, $reportId, 'reviewed');
        reportNotifyReporter($pdo, $report, __('admin.report_reporter_flagged_title'), __('admin.report_reporter_flagged_body'));
        if ($targetUserId > 0) {
            createNotification(
                $pdo,
                $targetUserId,
                'system',
                __('admin.report_seller_flagged_title'),
                __('admin.report_seller_flagged_body', ['title' => $report['product_title'] ?? __('admin.report_listing_fallback')]),
                $productId
            );
        }
        setFlash('success', __('admin.flash_report_flagged'));
        logAdminAction($pdo, 'flag_report', 'report', $reportId, ['product_id' => $productId]);
    } elseif ($action === 'warn_user' && $targetUserId > 0) {
        if ($targetUserId === $currentAdminId) {
            setFlash('error', __('admin.report_cannot_target_self'));
        } else {
            reportWarnUser($pdo, $targetUserId, __('admin.report_warn_user_body'));
            reportSetStatus($pdo, $reportId, 'reviewed');
            reportNotifyReporter($pdo, $report, __('admin.report_reporter_resolved_title'), __('admin.report_reporter_warned_body'));
            setFlash('success', __('admin.flash_report_warned'));
            logAdminAction($pdo, 'warn_user_report', 'report', $reportId, ['user_id' => $targetUserId]);
        }
    } elseif ($action === 'suspend_user' && $targetUserId > 0) {
        if ($targetUserId === $currentAdminId) {
            setFlash('error', __('admin.report_cannot_target_self'));
        } elseif (reportSuspendUser($pdo, $targetUserId)) {
            reportSetStatus($pdo, $reportId, 'reviewed');
            reportNotifyReporter($pdo, $report, __('admin.report_reporter_resolved_title'), __('admin.report_reporter_suspended_body'));
            setFlash('success', __('admin.flash_report_suspended'));
            logAdminAction($pdo, 'suspend_user_report', 'report', $reportId, ['user_id' => $targetUserId]);
        } else {
            setFlash('error', __('admin.report_suspend_failed'));
        }
    } else {
        setFlash('error', __('admin.report_invalid_action'));
    }

    redirect(BASE_URL . 'admin/reports.php?tab=' . urlencode($returnTab === 'pending' ? 'reviewed' : $returnTab));
}

$reports = reportsListForTab($pdo, $tab);
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.report-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.report-tab {
    padding: 0.5rem 1rem;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
    background: var(--bg-surface);
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.88rem;
}
.report-tab.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.report-tab .count {
    margin-left: 0.35rem;
    opacity: 0.85;
}
.admin-reports-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    justify-content: flex-end;
}
</style>

<div class="container mt-24 mb-16">
    <div class="flex justify-between items-end mb-8">
        <div>
            <div class="admin-breadcrumb mb-2"><a href="index.php">Dashboard</a> › Moderation</div>
            <h1 class="mb-0"><?= __('admin.moderation_queue') ?></h1>
        </div>
        <div class="badge" style="background: var(--bg-main); color: var(--text-muted); border: 1px solid var(--border-light); font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: var(--radius-lg);">
            <?= __('admin.report_tab_count', ['count' => count($reports), 'tab' => __('admin.report_tab_' . $tab)]) ?>
        </div>
    </div>

    <nav class="report-tabs" aria-label="Report status tabs">
        <?php foreach ($allowedTabs as $tabKey): ?>
            <a href="reports.php?tab=<?= urlencode($tabKey) ?>" class="report-tab <?= $tab === $tabKey ? 'active' : '' ?>">
                <?= __('admin.report_tab_' . $tabKey) ?>
                <?php if ($tabKey === 'pending' && $pendingCount > 0): ?>
                    <span class="count">(<?= (int)$pendingCount ?>)</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="glass-panel table-responsive" style="border-radius: var(--radius-lg); border: 1px solid rgba(0,0,0,0.05); box-shadow: var(--shadow-md);">
        <table class="table w-full text-left" style="border-collapse: collapse; margin: 0;">
            <thead>
                <tr style="background: rgba(248, 250, 252, 0.8);">
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);"><?= __('admin.report_col_target') ?></th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);"><?= __('admin.report_col_reason') ?></th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);"><?= __('admin.report_col_reporter') ?></th>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider" style="border-bottom: 2px solid var(--border-light);"><?= __('admin.report_col_submitted') ?></th>
                    <?php if ($tab === 'pending'): ?>
                    <th class="p-4 uppercase text-xs text-muted font-bold tracking-wider text-right" style="border-bottom: 2px solid var(--border-light);"><?= __('admin.report_col_actions') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="<?= $tab === 'pending' ? 5 : 4 ?>" class="p-16 text-center text-muted" style="border-bottom: none;">
                            <div class="mb-4 opacity-70"><svg style="width: 48px; height: 48px; display: inline-block; color: var(--success);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                            <h3 style="color: var(--success); font-weight: 600;"><?= __('admin.report_queue_clear_title') ?></h3>
                            <p><?= __('admin.report_queue_clear_' . $tab) ?></p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r):
                        $targetUserId = reportTargetUserId($r);
                        $hasProduct = !empty($r['product_id']);
                        $hasUserTarget = !empty($r['reported_user_id']) || !empty($r['seller_id']);
                    ?>
                        <tr>
                            <td class="p-4" style="border-bottom: 1px solid var(--border-light);">
                                <?php if ($hasProduct): ?>
                                    <div class="font-bold text-main"><?php echo sanitize($r['product_title'] ?? __('admin.report_listing_fallback')); ?></div>
                                    <a href="../pages/product.php?id=<?php echo (int)$r['product_id']; ?>" target="_blank" class="small text-primary inline-block mt-1" style="text-decoration: none; font-weight: 600;"><?= __('admin.report_view_listing') ?> ↗</a>
                                    <?php if (!empty($r['seller_name'])): ?>
                                        <div class="text-muted small mt-1">@<?= sanitize($r['seller_name']) ?></div>
                                    <?php endif; ?>
                                <?php elseif (!empty($r['reported_user_id'])): ?>
                                    <div class="font-bold text-main">@<?php echo sanitize($r['reported_username'] ?? __('admin.report_user_fallback')); ?></div>
                                    <a href="../pages/profile.php?id=<?php echo (int)$r['reported_user_id']; ?>" target="_blank" class="small text-primary inline-block mt-1" style="text-decoration: none; font-weight: 600;"><?= __('admin.report_view_profile') ?> ↗</a>
                                <?php else: ?>
                                    <div class="font-bold text-main"><?= __('admin.report_general_issue') ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4" style="border-bottom: 1px solid var(--border-light);">
                                <div style="background: rgba(239,68,68,0.05); border-left: 3px solid #ef4444; padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.9rem; color: #7f1d1d; white-space: pre-wrap;"><?php echo sanitize($r['reason']); ?></div>
                            </td>
                            <td class="p-4 font-medium" style="border-bottom: 1px solid var(--border-light);">
                                @<?php echo sanitize($r['reporter_name'] ?? '—'); ?>
                            </td>
                            <td class="p-4 text-muted small" style="border-bottom: 1px solid var(--border-light);">
                                <span style="background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid var(--border-light); font-size: 0.75rem;"><?php echo timeAgo($r['created_at']); ?></span>
                            </td>
                            <?php if ($tab === 'pending'): ?>
                            <td class="p-4 text-right" style="border-bottom: 1px solid var(--border-light);">
                                <form method="POST" class="admin-reports-actions m-0">
                                    <?php echo csrfTokenField(); ?>
                                    <input type="hidden" name="report_id" value="<?php echo (int)$r['id']; ?>">
                                    <input type="hidden" name="return_tab" value="<?= sanitize($tab) ?>">
                                    <button type="submit" name="action" value="dismiss" class="btn btn-secondary btn-sm"><?= __('admin.report_action_dismiss') ?></button>
                                    <?php if ($hasProduct): ?>
                                        <button type="submit" name="action" value="flag" class="btn btn-danger btn-sm" onclick="return confirm('<?= htmlspecialchars(__('admin.report_confirm_flag'), ENT_QUOTES) ?>')"><?= __('admin.report_action_flag') ?></button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="resolve" class="btn btn-success btn-sm"><?= __('admin.report_action_resolve') ?></button>
                                    <?php endif; ?>
                                    <?php if ($hasUserTarget && $targetUserId > 0 && (int)$targetUserId !== $currentAdminId): ?>
                                        <button type="submit" name="action" value="warn_user" class="btn btn-warning btn-sm" style="background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;"><?= __('admin.report_action_warn') ?></button>
                                        <?php if (reportsAccountStatusSupported($pdo)): ?>
                                        <button type="submit" name="action" value="suspend_user" class="btn btn-danger btn-sm" onclick="return confirm('<?= htmlspecialchars(__('admin.report_confirm_suspend'), ENT_QUOTES) ?>')"><?= __('admin.report_action_suspend') ?></button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
