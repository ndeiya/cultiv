<?php
/**
 * Role Middleware
 * Provides role-based access control for routes.
 */

/**
 * Gate access to a page/endpoint based on allowed roles.
 * Aborts with 401 (not logged in) or 403 (wrong role).
 *
 * @param string|array $allowedRoles  One or more roles allowed to access the resource
 */
function role_gate(string|array $allowedRoles): void
{
    ensure_session_tenant_id();
    require_role($allowedRoles);
}

/**
 * Require the user to be logged in (any role).
 * Redirects to login or returns 401 JSON for API.
 */
function require_auth(): void
{
    if (!is_logged_in()) {
        if (is_api_request()) {
            json_response(['error' => true, 'message' => 'Authentication required'], 401);
        }
        redirect('/login');
    }
    
    ensure_session_tenant_id();
}
