<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$applicationPathHelper = __DIR__.'/sys/bootstrap/application-path.php';

if (! is_file($applicationPathHelper)) {
    throw new RuntimeException('No se encontró sys/bootstrap/application-path.php');
}

require $applicationPathHelper;

$applicationPath = resolve_application_path(__DIR__);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $applicationPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $applicationPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $applicationPath.'/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
