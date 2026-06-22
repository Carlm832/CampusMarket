<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$base = rtrim(BASE_URL, '/');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /pages/inbox\n";
echo "Disallow: /pages/messages\n";
echo "Disallow: /pages/notifications\n";
echo "Disallow: /pages/wishlist\n";
echo "Disallow: /pages/my_orders\n";
echo "Disallow: /pages/my_reports\n";
echo "Disallow: /pages/edit_profile\n";
echo "Disallow: /pages/create_listing\n";
echo "Disallow: /pages/recycle_bin\n";
echo "Disallow: /pages/login\n";
echo "Disallow: /pages/register\n";
echo "\n";
echo 'Sitemap: ' . $base . "/sitemap.xml\n";
