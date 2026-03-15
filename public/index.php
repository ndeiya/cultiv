<?php
/**
 * Front Controller — Entry Point
 * All requests are routed through this file.
 */

// Load application configuration
require_once __DIR__ . '/../config/app.php';

// Load composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load database configuration
require_once __DIR__ . '/../config/database.php';

// Load helper utilities
require_once BASE_PATH . '/app/helpers/response_helper.php';
require_once BASE_PATH . '/app/helpers/validation_helper.php';
require_once BASE_PATH . '/app/helpers/auth_helper.php';
require_once BASE_PATH . '/app/helpers/file_upload_helper.php';
require_once BASE_PATH . '/app/helpers/middleware.php';
require_once BASE_PATH . '/app/helpers/RateLimiter.php';
require_once BASE_PATH . '/app/helpers/pagination_helper.php';

// Load the router
require_once BASE_PATH . '/app/helpers/Router.php';

// Simple autoloader for models and services
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/models/',
        BASE_PATH . '/app/services/',
        BASE_PATH . '/app/controllers/',
        BASE_PATH . '/app/helpers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly'  => true,
        'samesite'  => 'Lax',
    ]);
    session_start();
}

// Create the router instance
$router = new Router();

// Load route definitions
require_once BASE_PATH . '/routes/web.php';
require_once BASE_PATH . '/routes/api.php';

// Dispatch the request
$router->dispatch();
