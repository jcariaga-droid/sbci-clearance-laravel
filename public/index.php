<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

register_shutdown_function(function (): void {
    $headers = array_map(function ($header) {
        [$name, $value] = array_pad(explode(':', $header, 2), 2, '');

        if (strtolower(trim($name)) === 'set-cookie') {
            $cookieName = trim(explode('=', trim($value), 2)[0]);

            return 'Set-Cookie: ' . $cookieName . '=[redacted]';
        }

        return trim($name);
    }, headers_list());

    error_log('SBCI PHP OUTGOING HEADERS ' . json_encode($headers));
});

$app->handleRequest(Request::capture());
