<?php
/**
 * Response Helper
 * Functions for sending JSON responses, redirects, and rendering views.
 */

/**
 * Send a JSON response and exit.
 */
function json_response(mixed $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Redirect to another URL and exit.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Render a PHP view template with data.
 *
 * @param string $template  Path relative to views/ directory (e.g., 'shared/login')
 * @param array  $data      Associative array of variables to extract into the view
 */
function view(string $template, array $data = []): void
{
    $filePath = VIEWS_PATH . '/' . $template . '.php';

    if (!file_exists($filePath)) {
        http_response_code(500);
        die('View not found: ' . $template);
    }

    // Extract data as variables available to the view
    extract($data);

    // Include the view file
    require $filePath;
}

/**
 * Get the current request body as an associative array (for JSON APIs).
 */
function get_json_body(): array
{
    $body = file_get_contents('php://input');
    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Flash a message into the session for the next request.
 */
function flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

/**
 * Get and clear a flash message.
 */
function get_flash(string $key): mixed
{
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

/**
 * Escape a string for safe HTML output (XSS prevention).
 * Shorthand for htmlspecialchars with ENT_QUOTES and UTF-8.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
