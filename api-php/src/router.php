<?php
// Router script for PHP development server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// For download routes, always route through index.php
if (strpos($uri, '/api/download/') === 0) {
    require_once __DIR__ . '/public/index.php';
    return;
}

// If the request is for a static file that exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false; // Let PHP serve the static file
}

// Otherwise, route through index.php
require_once __DIR__ . '/public/index.php';
