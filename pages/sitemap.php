<?php
/**
 * Dynamic XML sitemap for public indexable pages.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(BASE_URL, '/');
$now = gmdate('c');

/** @param array<int, array{loc: string, lastmod?: string, changefreq?: string, priority?: string}> $urls */
$emit = static function (array $urls) use ($now): void {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $row) {
        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($row['loc'], ENT_XML1) . "</loc>\n";
        echo '    <lastmod>' . htmlspecialchars($row['lastmod'] ?? $now, ENT_XML1) . "</lastmod>\n";
        if (!empty($row['changefreq'])) {
            echo '    <changefreq>' . htmlspecialchars($row['changefreq'], ENT_XML1) . "</changefreq>\n";
        }
        if (!empty($row['priority'])) {
            echo '    <priority>' . htmlspecialchars($row['priority'], ENT_XML1) . "</priority>\n";
        }
        echo "  </url>\n";
    }
    echo "</urlset>\n";
};

$urls = [];

$staticPages = [
    ['path' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['path' => '/pages/browse', 'priority' => '0.9', 'changefreq' => 'hourly'],
    ['path' => '/pages/categories', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['path' => '/pages/donate', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['path' => '/pages/terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['path' => '/pages/privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['path' => '/pages/cookies', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['path' => '/pages/rules', 'priority' => '0.4', 'changefreq' => 'yearly'],
    ['path' => '/pages/safety', 'priority' => '0.4', 'changefreq' => 'yearly'],
];

foreach ($staticPages as $page) {
    $urls[] = [
        'loc' => $base . $page['path'],
        'priority' => $page['priority'],
        'changefreq' => $page['changefreq'],
        'lastmod' => $now,
    ];
}

try {
    $catStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
    while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
        $urls[] = [
            'loc' => $base . '/pages/browse?category=' . (int)$cat['id'],
            'changefreq' => 'daily',
            'priority' => '0.7',
            'lastmod' => $now,
        ];
    }

    $prodStmt = $pdo->query("
        SELECT id, updated_at, created_at
        FROM products
        WHERE status = 'active'
        ORDER BY id ASC
    ");
    while ($prod = $prodStmt->fetch(PDO::FETCH_ASSOC)) {
        $ts = $prod['updated_at'] ?? $prod['created_at'] ?? null;
        $lastmod = $ts ? gmdate('c', strtotime((string)$ts)) : $now;
        $urls[] = [
            'loc' => $base . '/pages/product?id=' . (int)$prod['id'],
            'changefreq' => 'weekly',
            'priority' => '0.6',
            'lastmod' => $lastmod,
        ];
    }
} catch (Throwable $e) {
    error_log('[sitemap] ' . $e->getMessage());
}

$emit($urls);
