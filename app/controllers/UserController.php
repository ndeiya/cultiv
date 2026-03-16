<?php
/**
 * User Controller
 * Handles user management (workers) for farm owners.
 */

class UserController
{
    private UserModel $userModel;
    private PayrollModel $payrollModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->payrollModel = new PayrollModel();
    }

    /**
     * List all workers for the current farm
     */
    public function index(): void
    {
        require_role('owner');
        $user = current_user();

        $workers = $this->userModel->getAllByFarm($user['farm_id']);

        view('owner/workers_list', [
            'workers' => $workers,
            'title' => 'Workforce Management'
        ]);
    }

    /**
     * Show add worker form
     */
    public function create(): void
    {
        require_role('owner');

        view('owner/worker_form', [
            'worker' => null, // null means new worker
            'title' => 'Add Worker'
        ]);
    }

    /**
     * Store new worker
     */
    public function store(): void
    {
        require_role('owner');
        check_csrf();

        $user = current_user();

        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $role = sanitize_input($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($role) || empty($password)) {
            $_SESSION['error'] = "Name, role, and password are required.";
            redirect('/owner/workers/create');
        }

        // Validate role
        $validRoles = ['supervisor', 'worker', 'accountant'];
        if (!in_array($role, $validRoles)) {
            $_SESSION['error'] = "Invalid role selected.";
            redirect('/owner/workers/create');
        }

        // Check if email already exists
        if (!empty($email) && $this->userModel->findByEmail($email)) {
            $_SESSION['error'] = "Email is already registered.";
            redirect('/owner/workers/create');
        }

        $userId = $this->userModel->create([
            'farm_id' => $user['farm_id'],
            'name' => $name,
            'email' => empty($email) ? null : $email,
            'phone' => empty($phone) ? null : $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => 'active'
        ]);

        // Save payment profile
        $this->payrollModel->savePaymentProfile([
            'user_id' => $userId,
            'payment_type' => sanitize_input($_POST['payment_type'] ?? 'hourly'),
            'hourly_rate' => (float)($_POST['hourly_rate'] ?? 0),
            'daily_rate' => (float)($_POST['daily_rate'] ?? 0),
            'monthly_salary' => (float)($_POST['monthly_salary'] ?? 0),
            'unit_rate' => (float)($_POST['unit_rate'] ?? 0),
            'overtime_rate' => (float)($_POST['overtime_rate'] ?? 0),
            'overtime_threshold' => (int)($_POST['overtime_threshold'] ?? 0)
        ]);

        AuditService::logAction('create', 'user');

        $_SESSION['success'] = "Worker added successfully.";
        redirect('/owner/workers');
    }

    /**
     * Show edit worker form
     */
    public function edit(): void
    {
        require_role('owner');
        $user = current_user();

        $id = (int)($_GET['id'] ?? 0);
        $worker = $this->userModel->findById($id);

        // Make sure worker exists and belongs to the owner's farm
        if (!$worker || $worker['farm_id'] !== $user['farm_id']) {
            $_SESSION['error'] = "Worker not found.";
            redirect('/owner/workers');
        }

        // Prevent owner from editing themselves here
        if ($worker['id'] === $user['id']) {
            redirect('/profile'); // or wherever they edit their own profile
        }

        $profile = $this->payrollModel->getPaymentProfile($id);

        view('owner/worker_form', [
            'worker' => $worker,
            'profile' => $profile,
            'title' => 'Edit Worker'
        ]);
    }

    /**
     * Update existing worker
     */
    public function update(): void
    {
        require_role('owner');
        check_csrf();

        $user = current_user();
        
        $id = (int)($_POST['id'] ?? 0);
        $worker = $this->userModel->findById($id);

        if (!$worker || $worker['farm_id'] !== $user['farm_id']) {
            $_SESSION['error'] = "Worker not found.";
            redirect('/owner/workers');
        }

        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $role = sanitize_input($_POST['role'] ?? '');
        
        if (empty($name) || empty($role)) {
            $_SESSION['error'] = "Name and role are required.";
            redirect('/owner/workers/edit?id=' . $id);
        }

        // Validate role
        $validRoles = ['supervisor', 'worker', 'accountant'];
        if (!in_array($role, $validRoles)) {
            $_SESSION['error'] = "Invalid role selected.";
            redirect('/owner/workers/edit?id=' . $id);
        }

        // Check email uniqueness, ignoring current user's email
        if (!empty($email) && $email !== $worker['email']) {
            if ($this->userModel->findByEmail($email)) {
                $_SESSION['error'] = "Email is already registered.";
                redirect('/owner/workers/edit?id=' . $id);
            }
        }

        $this->userModel->update($id, [
            'farm_id' => $user['farm_id'],
            'name' => $name,
            'email' => empty($email) ? null : $email,
            'phone' => empty($phone) ? null : $phone,
            'role' => $role
        ]);

        // Update payment profile
        $this->payrollModel->savePaymentProfile([
            'user_id' => $id,
            'payment_type' => sanitize_input($_POST['payment_type'] ?? 'hourly'),
            'hourly_rate' => (float)($_POST['hourly_rate'] ?? 0),
            'daily_rate' => (float)($_POST['daily_rate'] ?? 0),
            'monthly_salary' => (float)($_POST['monthly_salary'] ?? 0),
            'unit_rate' => (float)($_POST['unit_rate'] ?? 0),
            'overtime_rate' => (float)($_POST['overtime_rate'] ?? 0),
            'overtime_threshold' => (int)($_POST['overtime_threshold'] ?? 0)
        ]);

        AuditService::logAction('update', 'user', $id);

        $_SESSION['success'] = "Worker updated successfully.";
        redirect('/owner/workers');
    }

    /**
     * Toggle a worker's active/inactive status
     */
    public function toggleStatus(): void
    {
        require_role('owner');
        check_csrf();
        
        $user = current_user();

        $id = (int)($_POST['id'] ?? 0);
        $worker = $this->userModel->findById($id);

        if (!$worker || $worker['farm_id'] !== $user['farm_id']) {
            $_SESSION['error'] = "Worker not found.";
            redirect('/owner/workers');
        }

        if ($worker['id'] === $user['id']) {
            $_SESSION['error'] = "You cannot deactivate yourself.";
            redirect('/owner/workers');
        }

        $newStatus = ($worker['status'] === 'active') ? 'inactive' : 'active';
        $this->userModel->setStatus($id, $user['farm_id'], $newStatus);
        AuditService::logAction('toggle_status', 'user', $id);

        $_SESSION['success'] = "Worker status updated to {$newStatus}.";
        redirect('/owner/workers');
    }
}
