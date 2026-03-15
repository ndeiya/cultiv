<?php
/**
 * Application Configuration
 * Central configuration constants for the Cultiv application.
 */

// Application
define('APP_NAME', 'Cultiv');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Set to false in production
define('APP_TIMEZONE', 'Africa/Accra');
define('APP_URL', 'http://localhost:8000');

// Paths
define('BASE_PATH', dirname(__DIR__));

// Load .env file
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(sprintf('%s=%s', trim($parts[0]), trim($parts[1])));
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}

define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', BASE_PATH . '/views');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('LOGS_PATH', STORAGE_PATH . '/logs');

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Session
define('SESSION_LIFETIME', 86400); // 24 hours in seconds

// WhatsApp Business API (Meta Cloud API)
// Replace with actual values from Meta Developer Portal
define('WA_PHONE_NUMBER_ID', getenv('WA_PHONE_NUMBER_ID') ?: 'YOUR_PHONE_NUMBER_ID');
define('WA_ACCESS_TOKEN', getenv('WA_ACCESS_TOKEN') ?: 'YOUR_ACCESS_TOKEN');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);
