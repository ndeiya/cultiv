<?php
/**
 * Payroll Controller
 * Handles payroll management, generation, and payments.
 */

class PayrollController
{
    private PayrollModel $payrollModel;
    private PayrollService $payrollService;
    private UserModel $userModel;
    private ?PayslipService $payslipService = null;
    private PaystackService $paystackService;

    public function __construct()
    {
        $this->payrollModel = new PayrollModel();
        $this->payrollService = new PayrollService();
        $this->userModel = new UserModel();
        $this->paystackService = new PaystackService();
    }

    /**
     * Get or instantiate PayslipService.
     */
    private function getPayslipService(): PayslipService
    {
        if ($this->payslipService === null) {
            $this->payslipService = new PayslipService();
        }
        return $this->payslipService;
    }

    /**
     * Show payroll dashboard/periods for Owner or Accountant
     */
    public function index(): void
    {
        require_role(['owner', 'accountant']);
        $user = current_user();

        $periods = $this->payrollModel->getPeriods($user['farm_id']);

        if ($user['role'] === 'accountant') {
            view('accountant/payroll_dashboard', [
                'periods' => $periods,
                'title' => 'Payroll Dashboard'
            ]);
        } else {
            view('owner/payroll_periods', [
                'periods' => $periods,
                'title' => 'Payroll Management'
            ]);
        }
    }

    /**
     * Create a new payroll period
     */
    public function createPeriod(): void
    {
        require_role('owner');
        check_csrf();

        $user = current_user();
        $start = sanitize_input($_POST['period_start'] ?? '');
        $end = sanitize_input($_POST['period_end'] ?? '');

        if (empty($start) || empty($end)) {
            $_SESSION['error'] = "Start and end dates are required.";
            redirect('/owner/payroll');
        }

        $this->payrollModel->createPeriod($user['farm_id'], $start, $end);
        AuditService::logAction('create_period', 'payroll_periods');

        $_SESSION['success'] = "Payroll period created successfully.";
        redirect('/owner/payroll');
    }

    /**
     * Show payroll generation interface
     */
    public function showGenerate(): void
    {
        require_role('owner');
        $id = (int)($_GET['period_id'] ?? 0);
        $period = $this->payrollModel->getPeriod($id);

        if (!$period) {
            $_SESSION['error'] = "Period not found.";
            redirect('/owner/payroll');
        }

        view('owner/payroll_generation', [
            'period' => $period,
            'title' => 'Generate Payroll'
        ]);
    }

    /**
     * Generate payroll for a period
     */
    public function generate(): void
    {
        require_role('owner');
        check_csrf();

        $periodId = (int)($_POST['period_id'] ?? 0);
        
        try {
            $results = $this->payrollService->generatePayroll($periodId);
            AuditService::logAction('generate_payroll', 'payroll_periods', $periodId, true, ['owner', 'accountant']);
            $_SESSION['success'] = "Payroll generated for {$results['processed']} workers.";
            if (!empty($results['errors'])) {
                $_SESSION['warning'] = "Failed for " . count($results['errors']) . " workers.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error generating payroll: " . $e->getMessage();
        }

        redirect('/owner/payroll/records?period_id=' . $periodId);
    }

    /**
     * Download a payslip as PDF
     */
    public function downloadPayslip(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $record = $this->payrollModel->getRecord($id);
        $user = current_user();

        if (!$record) {
            $_SESSION['error'] = "Record not found.";
            redirect('/');
        }

        // Authorization: Only own payslip (worker) or owner/accountant of the record
        if ($user['role'] === 'worker' && $record['user_id'] !== $user['id']) {
            $_SESSION['error'] = "Unauthorized.";
            redirect('/');
        }

        // Generate if not already generated
        if (empty($record['payslip_path']) || !file_exists(LOCAL_STORAGE_PATH . '/' . $record['payslip_path'])) {
            try {
                $path = $this->getPayslipService()->generate($record);
                $this->payrollModel->updateRecord($id, ['payslip_path' => $path, 'payslip_generated_at' => date('Y-m-d H:i:s')]);
                $record['payslip_path'] = $path;
            } catch (Throwable $e) {
                $_SESSION['error'] = "Payslip generation error: " . $e->getMessage();
                redirect($_SERVER['HTTP_REFERER'] ?? '/');
                return;
            }
        }

        $fullPath = dirname(__DIR__, 2) . '/' . $record['payslip_path'];
        
        if (file_exists($fullPath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="payslip_' . $id . '.pdf"');
            readfile($fullPath);
            exit;
        } else {
            $_SESSION['error'] = "Payslip file not found.";
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    /**
     * View payroll records for a period
     */
    public function viewRecords(): void
    {
        require_role(['owner', 'accountant']);
        $periodId = (int)($_GET['period_id'] ?? 0);
        $period = $this->payrollModel->getPeriod($periodId);
        
        if (!$period) {
            $_SESSION['error'] = "Period not found.";
            redirect('/owner/payroll');
        }

        $records = $this->payrollModel->getRecordsByPeriod($periodId);

        $viewPath = current_user()['role'] === 'owner' ? 'owner/payroll_records' : 'accountant/payroll_records';
        
        view($viewPath, [
            'period' => $period,
            'records' => $records,
            'title' => 'Payroll Records'
        ]);
    }

    /**
     * Show payment profiles for users
     */
    public function profiles(): void
    {
        require_role('owner');
        $user = current_user();
        
        $stmt = Database::getInstance()->prepare('
            SELECT u.id, u.name, u.role, p.payment_type, p.hourly_rate, p.daily_rate, p.monthly_salary, p.unit_rate
            FROM users u
            LEFT JOIN worker_payment_profiles p ON u.id = p.user_id
            WHERE u.farm_id = :farm_id AND u.role = "worker"
            ORDER BY u.name ASC
        ');
        $stmt->execute(['farm_id' => $user['farm_id']]);
        $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        view('owner/payroll_profiles', [
            'workers' => $workers,
            'title' => 'Worker Payment Profiles'
        ]);
    }

    /**
     * Show edit form for a payment profile
     */
    public function editProfile(): void
    {
        require_role('owner');
        $userId = (int)($_GET['user_id'] ?? 0);
        $profile = $this->payrollModel->getPaymentProfile($userId);
        $worker = $this->userModel->findById($userId);

        if (!$worker) {
            $_SESSION['error'] = "Worker not found.";
            redirect('/owner/payroll/profiles');
        }

        view('owner/payroll_profile_form', [
            'worker' => $worker,
            'profile' => $profile,
            'title' => 'Edit Payment Profile'
        ]);
    }

    /**
     * Save payment profile
     */
    public function saveProfile(): void
    {
        require_role('owner');
        check_csrf();

        $data = [
            'user_id' => (int)$_POST['user_id'],
            'payment_type' => sanitize_input($_POST['payment_type']),
            'hourly_rate' => (float)$_POST['hourly_rate'],
            'daily_rate' => (float)$_POST['daily_rate'],
            'monthly_salary' => (float)$_POST['monthly_salary'],
            'unit_rate' => (float)$_POST['unit_rate'],
            'overtime_rate' => (float)$_POST['overtime_rate'],
            'overtime_threshold' => (int)$_POST['overtime_threshold']
        ];

        $this->payrollModel->savePaymentProfile($data);
        AuditService::logAction('update_payment_profile', 'worker_payment_profiles', $data['user_id']);

        $_SESSION['success'] = "Payment profile saved.";
        redirect('/owner/payroll/profiles');
    }

    /**
     * Mark a record as paid
     */
    public function payRecord(): void
    {
        require_role(['owner', 'accountant']);
        check_csrf();

        $data = [
            'payroll_record_id' => (int)$_POST['payroll_record_id'],
            'payment_method' => sanitize_input($_POST['payment_method']),
            'transaction_reference' => sanitize_input($_POST['transaction_reference'] ?? '')
        ];

        if ($data['payment_method'] === 'mobile_money') {
            try {
                $record = $this->payrollModel->getRecord($data['payroll_record_id']);
                $worker = $this->userModel->findById($record['user_id']);
                
                $transfer = $this->paystackService->initiateTransfer([
                    'name' => $worker['name'],
                    'phone' => $worker['phone'],
                    'amount' => $record['net_pay'],
                    'momo_provider' => $_POST['momo_provider'] ?? 'MTN',
                    'record_id' => $data['payroll_record_id']
                ]);
                
                $data['transaction_reference'] = $transfer['data']['reference'];
            } catch (Exception $e) {
                $_SESSION['error'] = "Paystack Error: " . $e->getMessage();
                redirect($_SERVER['HTTP_REFERER'] ?? '/owner/payroll');
                return;
            }
        }

        if ($this->payrollModel->createPayment($data)) {
            AuditService::logAction('record_payment', 'payments', $data['payroll_record_id'], true, ['owner', 'accountant', 'worker']);
            $_SESSION['success'] = "Payment recorded successfully.";
        } else {
            $_SESSION['error'] = "Failed to record payment.";
        }

        redirect($_SERVER['HTTP_REFERER'] ?? '/owner/payroll');
    }

    /**
     * Worker view for payslips
     */
    public function workerPayslips(): void
    {
        require_role('worker');
        $user = current_user();
        $payslips = $this->payrollModel->getWorkerPayslips($user['id']);

        view('worker/payslips', [
            'payslips' => $payslips,
            'title' => 'My Payslips'
        ]);
    }
}
