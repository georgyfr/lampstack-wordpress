<?php
/**
 * Router for PHP built-in server
 * Handles WordPress pretty permalinks and static files
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static files - serve directly
if (preg_match('/\.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot|mp4|webm|mp3|wav|pdf|zip|tar|gz)$/i', $uri)) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        return false; // Let PHP serve it
    }
}

// WordPress handles everything else
require __DIR__ . '/index.php';
