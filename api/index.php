<?php
// api/index.php
// Vercel Front Controller — routes PHP page requests only.
// Static assets (CSS, JS, images) are served directly by Vercel CDN
// via the explicit routes defined in vercel.json.

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl  = parse_url($requestUri);
$path       = $parsedUrl['path'] ?? '/';

// Normalize
$path = ltrim($path, '/');

// Default: homepage
if ($path === '' || $path === 'index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// Security: block directory traversal
if (str_contains($path, '..')) {
    http_response_code(403);
    exit('Forbidden');
}

// Resolve target file
$targetFile = __DIR__ . '/../' . $path;

// If it points to a directory, try its index.php
if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

// Only serve .php files through this handler
if (file_exists($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    $_SERVER['SCRIPT_NAME']     = '/' . $path;
    $_SERVER['SCRIPT_FILENAME'] = $targetFile;
    $_SERVER['PHP_SELF']        = '/' . $path;

    chdir(dirname($targetFile));
    require $targetFile;
    exit;
}

// Everything else: 404
http_response_code(404);
echo '404 Not Found';
