<?php
// TEMPORARY DEBUG — REMOVE BEFORE MERGING
require_once __DIR__ . '/config/supabase.php';

header('Content-Type: application/json');

$result = supabaseAuthRequest('POST', 'signup', [
    'email'    => 'debug_probe_' . time() . '@std.neu.edu.tr',
    'password' => 'DebugProbe123!',
    'options'  => [
        'emailRedirectTo' => 'https://www.campusmarketplace.site/pages/verify_email.php?source=supabase',
    ],
    'data' => [
        'username' => 'debug_probe',
        'phone'    => '',
    ],
]);

echo json_encode([
    'ok'     => $result['ok'],
    'status' => $result['status'],
    'error'  => $result['error'] ?? null,
    'data'   => array_keys($result['data'] ?? []),
    'supabase_url_configured' => supabaseUrl() !== '',
    'anon_key_configured'     => supabaseAnonKey() !== '',
    'supabase_url_prefix'     => substr(supabaseUrl(), 0, 30) . '...',
], JSON_PRETTY_PRINT);
