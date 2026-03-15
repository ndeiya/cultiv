<?php
/**
 * Auth Controller
 * Handles login, logout, and session management.
 */

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {

        $this->userModel = new UserModel();
    }

    /**
     * Home route — redirect to role-specific dashboard.
     */
    public function home(): void
    {
        if (!is_logged_in()) {
            redirect('/login');
        }

        $user = current_user();
        redirect(dashboard_url_for_role($user['role']));
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(): void
    {
        // If already logged in, redirect to dashboard
        if (is_logged_in()) {
            $user = current_user();
            redirect(dashboard_url_for_role($user['role']));
        }

        $csrfToken = generate_csrf_token();
        $error = get_flash('login_error');

        view('shared/login', [
            'csrf_token' => $csrfToken,
            'error'      => $error,
        ]);
    }

    /**
     * Process login form submission.
     */
    public function login(): void
    {
        require_csrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Strict rate limit: 5 attempts per 5 minutes
        $limiter = new RedisRateLimiter();
        if (!$limiter->check("login_{$ip}", 5, 300)) {
            $retryAfter = ceil($limiter->getRetryAfter("login_{$ip}") / 60);
            flash('login_error', "Too many login attempts. Please try again in {$retryAfter} minutes.");
            redirect('/login');
        }

        // Validate required fields
        $missing = validate_required(['email', 'password'], $_POST);
        if (!empty($missing)) {
            flash('login_error', 'Please enter your email and password.');
            redirect('/login');
        }

        // Validate email format
        if (!validate_email($email)) {
            flash('login_error', 'Please enter a valid email address.');
            redirect('/login');
        }

        // Find user by email
        $user = $this->userModel->findByEmail($email);
        
        // Verify user and password
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $limiter->increment("login_{$ip}");
            flash('login_error', 'Invalid email or password.');
            redirect('/login');
        }

        // Check if user is active
        if (($user['status'] ?? 'active') !== 'active') {
            flash('login_error', 'Your account has been deactivated. Contact your administrator.');
            redirect('/login');
        }

        // Success - clear rate limit
        $limiter->clear("login_{$ip}");

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store user data in session (exclude sensitive fields)
        $_SESSION['user'] = [
            'id'        => $user['id'],
            'tenant_id' => $user['tenant_id'] ?? 1, // Default to 1 for backward compatibility
            'farm_id'   => $user['farm_id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
        ];

        // Regenerate CSRF token
        unset($_SESSION['csrf_token']);
        generate_csrf_token();

        // Redirect to role-specific dashboard
        redirect(dashboard_url_for_role($user['role']));
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        redirect('/login');
    }

    // ── API Endpoints ───────────────────────────────────

    /**
     * API Login — returns JSON.
     */
    public function apiLogin(): void
    {
        $data = get_json_body();

        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Strict rate limit: 5 attempts per 5 minutes
        $limiter = new RedisRateLimiter();
        if (!$limiter->check("api_login_{$ip}", 5, 300)) {
            $retryAfter = $limiter->getRetryAfter("api_login_{$ip}");
            json_response([
                'error' => true, 
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => $retryAfter
            ], 429);
        }

        // Validate
        if (empty($email) || empty($password)) {
            json_response(['error' => true, 'message' => 'Email and password are required.'], 422);
        }

        // Find user
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $limiter->increment("api_login_{$ip}");
            json_response(['error' => true, 'message' => 'Invalid credentials.'], 401);
        }

        // Check active status
        if (($user['status'] ?? 'active') !== 'active') {
            json_response(['error' => true, 'message' => 'Account deactivated.'], 403);
        }

        // Success
        $limiter->clear("api_login_{$ip}");

        // Start session
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'        => $user['id'],
            'tenant_id' => $user['tenant_id'] ?? 1, // Default to 1 for backward compatibility
            'farm_id'   => $user['farm_id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
        ];

        json_response([
            'success' => true,
            'user'    => $_SESSION['user'],
            'message' => 'Login successful.',
        ]);
    }

    /**
     * API Logout — returns JSON.
     */
    public function apiLogout(): void
    {
        $_SESSION = [];
        session_destroy();

        json_response([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
