<?php
/**
 * Shift Controller
 * Handles shift scheduling, templates, and roster management.
 */

class ShiftController extends BaseController
{
    private ShiftService $shiftService;
    private UserModel $userModel;

    public function __construct()
    {
        $this->shiftService = new ShiftService();
        $this->userModel = new UserModel();
    }

    /**
     * API: Get roster for a specific date
     */
    public function apiGetRoster(): void
    {
        require_role(['supervisor', 'owner']);
        $user = current_user();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $roster = $this->shiftService->getRoster($user['farm_id'], $date);
        
        json_response([
            'success' => true,
            'date' => $date,
            'roster' => $roster
        ]);
    }

    /**
     * API: Create shift template
     */
    public function apiCreateTemplate(): void
    {
        require_role(['supervisor', 'owner']);
        require_csrf();
        $user = current_user();
        
        $data = get_json_body();
        
        $templateId = $this->shiftService->createTemplate([
            'farm_id' => $user['farm_id'],
            'name' => $data['name'] ?? '',
            'start_time' => $data['start_time'] ?? '08:00:00',
            'end_time' => $data['end_time'] ?? '17:00:00',
            'break_duration_minutes' => $data['break_duration_minutes'] ?? 0,
            'is_recurring' => $data['is_recurring'] ?? true,
            'days_of_week' => $data['days_of_week'] ?? '1,2,3,4,5',
            'is_active' => true
        ]);
        
        AuditService::logAction('create', 'shift_template', $templateId);
        
        json_response([
            'success' => true,
            'template_id' => $templateId,
            'message' => 'Shift template created successfully'
        ]);
    }

    /**
     * API: Generate shifts from template
     */
    public function apiGenerateFromTemplate(): void
    {
        require_role(['supervisor', 'owner']);
        require_csrf();
        
        $data = get_json_body();
        $templateId = (int)($data['template_id'] ?? 0);
        $startDate = $data['start_date'] ?? date('Y-m-d');
        $endDate = $data['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $userIds = $data['user_ids'] ?? [];
        
        if (empty($userIds) || !is_array($userIds)) {
            json_response(['error' => true, 'message' => 'User IDs required'], 400);
            return;
        }
        
        $created = $this->shiftService->generateFromTemplate($templateId, $startDate, $endDate, $userIds);
        AuditService::logAction('generate', 'shift_assignments');
        
        json_response([
            'success' => true,
            'created' => $created,
            'message' => "Generated {$created} shift assignments"
        ]);
    }

    /**
     * API: Get worker's schedule
     */
    public function apiGetWorkerSchedule(): void
    {
        $user = current_user();
        $userId = (int)($_GET['user_id'] ?? $user['id']);
        
        // Workers can only see their own schedule
        if ($user['role'] === 'worker' && $userId !== $user['id']) {
            json_response(['error' => true, 'message' => 'Access denied'], 403);
            return;
        }
        
        $weekStart = $_GET['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
        $schedule = $this->shiftService->getWorkerSchedule($userId, $weekStart);
        
        json_response([
            'success' => true,
            'week_start' => $weekStart,
            'schedule' => $schedule
        ]);
    }

    /**
     * View: Supervisor roster grid
     */
    public function roster(): void
    {
        require_role(['supervisor', 'owner']);
        $user = current_user();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $roster = $this->shiftService->getRoster($user['farm_id'], $date);
        
        view('supervisor/roster', [
            'user' => $user,
            'page_title' => 'Shift Roster',
            'roster' => $roster,
            'current_date' => $date
        ]);
    }
}
