<?php
/**
 * Report Controller
 * Handles report submissions and viewing.
 */

class ReportController
{
    private ReportModel $reportModel;
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->reportService = new ReportService();
    }

    /**
     * View: Show the submit report form.
     */
    public function create()
    {
        role_gate(['worker', 'supervisor', 'owner']); // Usually workers submit, but others can too
        view('worker/submit_report', ['title' => 'Submit Report']);
    }

    /**
     * Web/API: Store a new report.
     */
    public function store()
    {
        $user = current_user();
        if (!$user) {
            return json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_response(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        // Enforce CSRF for non-API (form) requests
        if (!is_api_request()) {
            require_csrf();
        }

        $data = [
            'farm_id'      => $user['farm_id'],
            'user_id'      => $user['id'],
            'category'     => $_POST['category'] ?? 'general',
            'related_type' => $_POST['related_type'] ?? null,
            'related_id'   => !empty($_POST['related_id']) ? (int) $_POST['related_id'] : null,
            'description'  => $_POST['description'] ?? '',
            'severity'     => $_POST['severity'] ?? 'low',
        ];

        // Basic validation
        if (empty($data['description'])) {
            return json_response(['success' => false, 'message' => 'Description is required']);
        }

        $result = $this->reportService->createReport($data, $_FILES);

        // API or form handler
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            if ($result['success']) AuditService::logAction('create', 'report', $result['report_id'] ?? null);
            return json_response($result);
        }

        // Web fallback
        if ($result['success']) {
            AuditService::logAction('create', 'report', $result['report_id'] ?? null);
            $_SESSION['flash_success'] = 'Report submitted successfully.';
            redirect('/worker/reports');
        } else {
            $_SESSION['flash_error'] = $result['message'];
            redirect('/worker/reports/create');
        }
    }

    /**
     * View: Show report history/management.
     */
    public function index()
    {
        $user = current_user();
        role_gate(['worker', 'supervisor', 'owner']);

        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'status'   => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null,
            'worker'   => $_GET['worker'] ?? null,
            'date'     => $_GET['date'] ?? null,
        ];

        if ($user['role'] === 'worker') {
            $result = $this->reportModel->getByUser($user['id'], $filters, $page);
            view('worker/report_history', ['title' => 'My Reports', 'reports' => $result['data'], 'pagination' => $result, 'filters' => $filters]);
        } elseif ($user['role'] === 'supervisor') {
            $result = $this->reportModel->getByFarm($user['farm_id'], $filters, $page);
            view('supervisor/reports_management', ['title' => 'Reports Management', 'reports' => $result['data'], 'pagination' => $result, 'filters' => $filters]);
        } elseif ($user['role'] === 'owner') {
            $result = $this->reportModel->getByFarm($user['farm_id'], $filters, $page);
            view('owner/reports_management', ['title' => 'Reports Overview', 'reports' => $result['data'], 'pagination' => $result, 'filters' => $filters]);
        }
    }

    /**
     * API: Get reports as JSON.
     */
    public function apiIndex()
    {
        $user = current_user();
        if (!$user) {
            return json_response(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'status'   => $_GET['status'] ?? null,
            'category' => $_GET['category'] ?? null,
            'worker'   => $_GET['worker'] ?? null,
            'date'     => $_GET['date'] ?? null,
        ];

        if ($user['role'] === 'worker') {
            $result = $this->reportModel->getByUser($user['id'], $filters, $page);
        } else {
            $result = $this->reportModel->getByFarm($user['farm_id'], $filters, $page);
        }

        return json_response(['success' => true, 'data' => $result['data'], 'pagination' => $result]);
    }

    /**
     * API: Mark a report as resolved.
     */
    public function resolve()
    {
        $user = current_user();
        role_gate(['supervisor', 'owner']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_response(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        // Enforce CSRF for non-API (form) requests
        if (!is_api_request()) {
            require_csrf();
        }
        
        // Handle JSON or Form data
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $reportId = (int) ($input['id'] ?? 0);
        $status = $input['status'] ?? 'resolved';

        if (!$reportId) {
            return json_response(['success' => false, 'message' => 'Report ID is required']);
        }

        $success = $this->reportModel->updateStatus($reportId, $user['farm_id'], $status);

        if ($success) {
            AuditService::logAction('resolve', 'report', $reportId);
            return json_response(['success' => true, 'message' => 'Report status updated successfully.']);
        }

        return json_response(['success' => false, 'message' => 'Failed to update report status or report not found.']);
    }
}
