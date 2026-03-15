<?php
/**
 * Profile Controller
 * Handles profile viewing/editing, password changes, and notifications for all roles.
 */

class ProfileController
{
    private UserModel $userModel;

    public function __construct()
    {
        require_once __DIR__ . '/../models/UserModel.php';
        $this->userModel = new UserModel();
    }

    /**
     * Show user profile page.
     */
    public function show(): void
    {
        require_role(['owner', 'supervisor', 'worker', 'accountant']);
        $user = current_user();

        // Fetch fresh user data from DB
        $profile = $this->userModel->findById($user['id']);

        view('shared/profile', [
            'page_title' => 'My Profile',
            'active_nav' => 'profile',
            'user' => $user,
            'profile' => $profile
        ]);
    }

    /**
     * Update user profile.
     */
    public function update(): void
    {
        require_role(['owner', 'supervisor', 'worker', 'accountant']);
        require_csrf();
        $user = current_user();
        $role = $user['role'];

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Validation
        if (empty($name)) {
            $_SESSION['error'] = 'Name is required.';
            redirect("/$role/profile");
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            redirect("/$role/profile");
        }

        // Check email uniqueness (excluding current user)
        if (!empty($email)) {
            $existing = $this->userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== (int)$user['id']) {
                $_SESSION['error'] = 'This email is already in use by another user.';
                redirect("/$role/profile");
            }
        }

        $this->userModel->updateProfile($user['id'], [
            'name'  => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
        ]);

        // Update session data
        $_SESSION['user']['name']  = $name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;

        // Audit log
        require_once __DIR__ . '/../services/AuditService.php';
        AuditService::logAction('update_profile', 'user', $user['id']);

        $_SESSION['success'] = 'Profile updated successfully.';
        redirect("/$role/profile");
    }

    /**
     * Show change password form.
     */
    public function showChangePassword(): void
    {
        require_role(['owner', 'supervisor', 'worker', 'accountant']);
        $user = current_user();

        view('shared/change_password', [
            'page_title' => 'Change Password',
            'active_nav' => 'profile',
            'user' => $user
        ]);
    }

    /**
     * Process password change.
     */
    public function changePassword(): void
    {
        require_role(['owner', 'supervisor', 'worker', 'accountant']);
        require_csrf();
        $user = current_user();
        $role = $user['role'];

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword      = $_POST['new_password'] ?? '';
        $confirmPassword  = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All fields are required.';
            redirect("/$role/change-password");
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match.';
            redirect("/$role/change-password");
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'New password must be at least 6 characters.';
            redirect("/$role/change-password");
        }

        // Verify current password
        $dbUser = $this->userModel->findById($user['id']);
        if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
            $_SESSION['error'] = 'Current password is incorrect.';
            redirect("/$role/change-password");
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userModel->updatePassword($user['id'], $hashedPassword);

        // Audit log
        require_once __DIR__ . '/../services/AuditService.php';
        AuditService::logAction('change_password', 'user', $user['id']);

        $_SESSION['success'] = 'Password changed successfully.';
        redirect("/$role/profile");
    }

    /**
     * Show notifications / activity feed.
     */
    public function notifications(): void
    {
        require_role(['owner', 'supervisor', 'worker', 'accountant']);
        $user = current_user();

        $db = Database::getInstance();

        // Fetch recent audit logs for the user's farm (last 50 entries)
        $stmt = $db->prepare('
            SELECT al.*, u.name as user_name, u.role as user_role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE u.farm_id = :farm_id
            ORDER BY al.created_at DESC
            LIMIT 50
        ');
        $stmt->execute(['farm_id' => $user['farm_id']]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('shared/notifications', [
            'page_title' => 'Notifications',
            'active_nav' => 'profile',
            'user' => $user,
            'logs' => $logs
        ]);
    }
}
