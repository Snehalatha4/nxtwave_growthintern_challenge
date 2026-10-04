<?php
/**
 * Vercel Serverless Entrypoint & Router
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($uri === '/' || $uri === '/index.php' || $uri === '') {
    require __DIR__ . '/../index.php';
    exit;
}

if ($uri === '/register.php' || $uri === '/register') {
    require __DIR__ . '/../register.php';
    exit;
}

if ($uri === '/success.php' || $uri === '/success') {
    require __DIR__ . '/../success.php';
    exit;
}

if ($uri === '/dashboard.php' || $uri === '/dashboard') {
    require __DIR__ . '/../dashboard.php';
    exit;
}

if ($uri === '/admin.php' || $uri === '/admin') {
    require __DIR__ . '/../admin.php';
    exit;
}

if ($uri === '/experiments.php' || $uri === '/experiments') {
    require __DIR__ . '/../experiments.php';
    exit;
}

// Fallback to index
require __DIR__ . '/../index.php';
