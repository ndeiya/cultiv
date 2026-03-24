<?php



class AttendanceController {
    private AttendanceService $attendanceService;

    public function __construct() {
        $this->attendanceService = new AttendanceService();
    }

    // --- API Endpoints ---

    public function apiClockIn() {
        try {
            role_gate(['worker', 'supervisor']);
            require_csrf();
            $user = current_user();
            
            // Strict rate limit: 10 clock-ins per minute
            $limiter = new RedisRateLimiter();
            $rateLimitKey = "clock_in_{$user['id']}";
            if (!$limiter->check($rateLimitKey, 10, 60)) {
                $retryAfter = $limiter->getRetryAfter($rateLimitKey);
                return json_response([
                    'error' => true,
                    'message' => 'Too many clock-in attempts. Please wait before trying again.',
                    'retry_after' => $retryAfter
                ], 429);
            }
            
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = filter_var($input['user_id'] ?? $user['id'], FILTER_VALIDATE_INT);
            $lat = filter_var($input['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
            $lng = filter_var($input['longitude'] ?? null, FILTER_VALIDATE_FLOAT);

            if ($lat === null || $lng === null) {
                return json_response(['error' => 'GPS coordinates required'], 400);
            }

            $result = $this->attendanceService->clockIn($user['farm_id'], $userId, $lat, $lng);
            $isImportant = ($result['status'] !== 'normal');
            AuditService::logAction('clock_in', 'attendance', $result['id'] ?? null, $isImportant, ['supervisor', 'owner']);
            return json_response($result, 201);
        } catch (Exception $e) {
            return json_response(['error' => $e->getMessage()], 400);
        }
    }

    public function apiClockOut() {
        try {
            role_gate(['worker', 'supervisor']);
            require_csrf();
            $user = current_user();
            
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = filter_var($input['user_id'] ?? $user['id'], FILTER_VALIDATE_INT);
            $lat = filter_var($input['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
            $lng = filter_var($input['longitude'] ?? null, FILTER_VALIDATE_FLOAT);

            if ($lat === null || $lng === null) {
                return json_response(['error' => 'GPS coordinates required'], 400);
            }

            $result = $this->attendanceService->clockOut($userId, $lat, $lng);
            AuditService::logAction('clock_out', 'attendance', $result['id'] ?? null);
            return json_response($result, 200);
        } catch (Exception $e) {
            return json_response(['error' => $e->getMessage()], 400);
        }
    }

    public function apiHistory() {
        try {
            role_gate(['worker', 'supervisor', 'owner', 'accountant']);
            $userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?? current_user()['id'];
            $dateFrom = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_STRING);
            $dateTo = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_STRING);

            $history = $this->attendanceService->getHistory($userId, $dateFrom, $dateTo);
            return json_response($history, 200);
        } catch (Exception $e) {
            return json_response(['error' => $e->getMessage()], 400);
        }
    }

    // --- View Endpoints ---

    public function workerHistory() {
        role_gate(['worker']);
        $user = current_user();
        $page = (int)($_GET['page'] ?? 1);
        $result = $this->attendanceService->getHistory($user['id'], null, $page);

        view('worker/attendance_history', [
            'user' => $user,
            'page_title' => 'Attendance History',
            'history' => $result['data'],
            'pagination' => $result
        ]);
    }

    public function supervisorOverview() {
        role_gate(['supervisor', 'owner']);
        $user = current_user();
        $page = (int)($_GET['page'] ?? 1);
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
        $result = $this->attendanceService->getDailySummary($user['farm_id'], $date, $page);

        view('supervisor/attendance_overview', [
            'user' => $user,
            'page_title' => 'Team Attendance',
            'summary' => $result['data'],
            'pagination' => $result,
            'current_date' => $date
        ]);
    }

    public function supervisorWorkerDetail() {
        role_gate(['supervisor', 'owner']);
        $user = current_user();
        $workerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $page = (int)($_GET['page'] ?? 1);
        $dateFrom = filter_input(INPUT_GET, 'date_from', FILTER_SANITIZE_STRING);
        $dateTo = filter_input(INPUT_GET, 'date_to', FILTER_SANITIZE_STRING);

        if (!$workerId) {
            redirect('/supervisor/dashboard');
            exit;
        }

        $userModel = new UserModel();
        $worker = $userModel->findById($workerId);

        if (!$worker || $worker['farm_id'] !== $user['farm_id']) {
            redirect('/supervisor/dashboard');
            exit;
        }

        $result = $this->attendanceService->getHistory($workerId, ['from' => $dateFrom, 'to' => $dateTo], $page);

        view('supervisor/worker_attendance_detail', [
            'user' => $user,
            'worker' => $worker,
            'page_title' => 'Worker Attendance Detail',
            'history' => $result['data'],
            'pagination' => $result
        ]);
    }

    public function ownerManagement() {
        role_gate(['owner']);
        $user = current_user();
        $page = (int)($_GET['page'] ?? 1);
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
        $result = $this->attendanceService->getDailySummary($user['farm_id'], $date, $page);

        view('owner/attendance_management', [
            'user' => $user,
            'page_title' => 'Farm Attendance Overview',
            'summary' => $result['data'],
            'pagination' => $result,
            'current_date' => $date
        ]);
    }
}
