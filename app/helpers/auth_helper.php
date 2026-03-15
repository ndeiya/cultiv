<?php
/**
 * Authentication Helper
 * Functions for session-based auth, user retrieval, and CSRF protection.
 */

/**
 * Get the currently logged-in user from session.
 * Returns null if not logged in.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Ensure the current session has a tenant_id.
 * If user is logged in but tenant_id is missing, patch it from database.
 */
function ensure_session_tenant_id(): void
{
    if (isset($_SESSION['user']) && !isset($_SESSION['user']['tenant_id'])) {
        $userModel = new UserModel();
        $user = $userModel->findByIdUnscoped($_SESSION['user']['id']);
        if ($user) {
            $_SESSION['user']['tenant_id'] = $user['tenant_id'];
        }
    }
}

/**
 * Check if a user is currently logged in.
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Require the current user to have a specific role.
 * Redirects to login if not authenticated, aborts with 403 if unauthorized.
 *
 * @param string|array $roles  Single role string or array of allowed roles
 */
function require_role(string|array $roles): void
{
    if (!is_logged_in()) {
        if (is_api_request()) {
            json_response(['error' => true, 'message' => 'Authentication required'], 401);
        }
        redirect('/login');
    }

    $allowedRoles = is_array($roles) ? $roles : [$roles];
    $user = current_user();

    if (!in_array($user['role'], $allowedRoles, true)) {
        if (is_api_request()) {
            json_response(['error' => true, 'message' => 'Access denied'], 403);
        }
        http_response_code(403);
        die('Access denied. You do not have permission to view this page.');
    }
}

/**
 * Generate a CSRF token and store it in the session.
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 */
function check_csrf(?string $token = null): bool
{
    // Get token from parameter, POST data, or header
    $token = $token
        ?? $_POST['_csrf_token'] ?? $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? null;

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF token and abort if invalid.
 */
function require_csrf(): void
{
    if (!check_csrf()) {
        if (is_api_request()) {
            json_response(['error' => true, 'message' => 'Invalid CSRF token'], 403);
        }
        http_response_code(403);
        die('Invalid CSRF token. Please refresh the page and try again.');
    }
}

/**
 * Check if the current request is an API request.
 */
function is_api_request(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return str_starts_with(parse_url($uri, PHP_URL_PATH), '/api/');
}

/**
 * Get the dashboard URL for a given role.
 */
function dashboard_url_for_role(string $role): string
{
    return match ($role) {
        'owner'      => '/owner/dashboard',
        'supervisor' => '/supervisor/dashboard',
        'worker'     => '/worker/dashboard',
        'accountant' => '/accountant/dashboard',
        default      => '/login',
    };
}
