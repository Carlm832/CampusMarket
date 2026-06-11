<?php
// includes/web_push.php

/**
 * Queue a web push to run after the HTTP response is sent (avoids serverless cutoff).
 */
function triggerWebPushBestEffort(int $userId, string $title, string $body, string $url = '/pages/notifications.php'): void {
    $internalKey = getenv('INTERNAL_PUSH_KEY') ?: '';
    if (!$internalKey || !function_exists('curl_init')) {
        return;
    }

    $base = rtrim(BASE_URL, '/');
    $endpoint = $base . '/api/web-push/send';

    $payload = json_encode([
        'userId' => $userId,
        'title' => $title,
        'body' => $body,
        'url' => $url,
    ]);

    if (!$payload) {
        return;
    }

    $dispatch = static function () use ($endpoint, $payload, $internalKey): void {
        try {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Internal-Push-Key: ' . $internalKey,
                ],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 20,
            ]);
            curl_exec($ch);
        } catch (Throwable $e) {
            // Push is best-effort.
        }
    };

    if (function_exists('fastcgi_finish_request')) {
        register_shutdown_function($dispatch);
        return;
    }

    register_shutdown_function($dispatch);
}

/**
 * Sync local marketplace user id into Supabase Auth app_metadata for Realtime RLS.
 */
function syncSupabaseAppUserMetadata(PDO $pdo, int $localUserId, string $role = 'user'): void {
    if ($localUserId <= 0 || supabaseServiceRoleKey() === '') {
        return;
    }

    $emailStmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
    $emailStmt->execute([$localUserId]);
    $email = $emailStmt->fetchColumn();
    if (!$email) {
        return;
    }

    $authResponse = supabaseAdminRequest('GET', 'admin/users?per_page=1000');
    if (!$authResponse['ok'] || empty($authResponse['data']['users'])) {
        return;
    }

    $supabaseUserUuid = null;
    foreach ($authResponse['data']['users'] as $su) {
        if (isset($su['email']) && strtolower((string)$su['email']) === strtolower((string)$email)) {
            $supabaseUserUuid = $su['id'] ?? null;
            break;
        }
    }

    if (!$supabaseUserUuid) {
        return;
    }

    $existingMeta = [];
    foreach ($authResponse['data']['users'] as $su) {
        if (($su['id'] ?? '') === $supabaseUserUuid) {
            $existingMeta = is_array($su['app_metadata'] ?? null) ? $su['app_metadata'] : [];
            break;
        }
    }

    $nextMeta = array_merge($existingMeta, [
        'user_id' => (string)$localUserId,
        'role' => $role,
    ]);

    if (($existingMeta['user_id'] ?? null) === (string)$localUserId && ($existingMeta['role'] ?? null) === $role) {
        return;
    }

    supabaseAdminRequest('PUT', 'admin/users/' . $supabaseUserUuid, [
        'app_metadata' => $nextMeta,
    ]);
}
