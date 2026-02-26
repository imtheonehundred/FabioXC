<?php

/**
 * Streaming-only entry point.
 *
 * Point nginx (or reverse proxy) streaming locations here for a dedicated entry.
 * Uses the same Laravel bootstrap as index.php; streaming routes bypass web
 * middleware (no session, CSRF, Inertia) for a lighter hot path.
 *
 * Example nginx:
 *   location ~ ^/(live|movie|series|timeshift|streaming)/ {
 *       fastcgi_param SCRIPT_FILENAME $document_root/stream.php;
 *       fastcgi_pass unix:/run/php/php-fpm.sock;
 *       include fastcgi_params;
 *   }
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());
