<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if application is in maintenance mode.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader.
if (! file_exists(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(500);
    echo "Composer autoloader not found. Run 'composer install' first.";
    exit;
}
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request.
// Laravel 11: Application::configure() returns an Application that exposes
// handleRequest(), replacing the old HttpKernel pattern.
/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
