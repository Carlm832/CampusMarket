<?php
// ============================================================
// CampusMarket — App-Wide Constants
// ============================================================

// Base URL — change if you rename the folder
define('BASE_URL',    'http://localhost/CampusMarket');

// File Paths
define('ROOT_PATH',   __DIR__ . '/../');
define('UPLOAD_PATH', ROOT_PATH . 'public/uploads/');
define('UPLOAD_URL',  BASE_URL  . '/public/uploads/');

// Upload Limits
define('MAX_FILE_SIZE',    5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES',    ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('MAX_IMAGES',       5);               // max images per product

// Pagination
define('ITEMS_PER_PAGE',   12);

// App Meta
define('APP_NAME',         'CampusMarket');
define('APP_TAGLINE',      'Buy & Sell Within Your Campus');
define('APP_CURRENCY',     'KES');

// Session name
define('SESSION_NAME',     'campusmarket_session');
