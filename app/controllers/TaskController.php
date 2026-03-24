<?php
/**
 * Task Controller
 * Handles task assignment, list, and status updates.
 */

class TaskController extends BaseController {
    private TaskModel $taskModel;
    private UserModel $userModel;

    public function __construct() {
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
    }

    /**
     * View: Task management for supervisors and owners
     */
    public function index(): void {
        require_role(['supervisor', 'owner']);
        $user = current_user();
        
        $filters = [
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'status' => $_GET['status'] ?? null,
            'due_date' => $_GET['due_date'] ?? null
        ];
        
        $tasks = $this->taskModel->getFilteredTasks($user['farm_id'], array_filter($filters));
        $workers = $this->userModel->getAllByFarm($user['farm_id'], 'worker');

        view('shared/tasks', [
            'user' => $user,
            'page_title' => 'Task Management',
            'tasks' => $tasks,
            'workers' => $workers,
            'filters' => $filters
        ]);
    }

    /**
     * API: Get all tasks
     */
    public function apiGetTasks(): void {
        require_role(['supervisor', 'owner', 'worker']);
        $user = current_user();
        
        if ($user['role'] === 'worker') {
            $tasks = $this->taskModel->getTasksByWorker($user['id']);
        } else {
            $filters = array_filter([
                'assigned_to' => $_GET['assigned_to'] ?? null,
                'status' => $_GET['status'] ?? null,
                'due_date' => $_GET['due_date'] ?? null
            ]);
            $tasks = $this->taskModel->getFilteredTasks($user['farm_id'], $filters);
        }

        json_response([
            'success' => true,
            'tasks' => $tasks
        ]);
    }

    /**
     * API: Create a new task
     */
    public function apiCreate(): void {
        require_role(['supervisor', 'owner']);
        require_csrf();
        $user = current_user();
        
        $data = get_json_body();
        
        if (empty($data['title']) || empty($data['assigned_to'])) {
            json_response(['error' => true, 'message' => 'Title and assigned worker are required'], 400);
            return;
        }

        $taskId = $this->taskModel->create([
            'tenant_id' => $user['tenant_id'],
            'farm_id' => $user['farm_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'pending',
            'assigned_to' => $data['assigned_to'],
            'due_date' => $data['due_date'] ?? date('Y-m-d'),
            'related_type' => $data['related_type'] ?? 'general',
            'related_id' => $data['related_id'] ?? null,
            'created_by' => $user['id']
        ]);

        AuditService::logAction('create', 'task', $taskId, true, ['supervisor', 'owner', 'worker']);
        
        json_response([
            'success' => true,
            'task_id' => $taskId,
            'message' => 'Task assigned successfully'
        ]);
    }

    /**
     * API: Update task status
     */
    public function apiUpdateStatus(): void {
        require_role(['supervisor', 'owner', 'worker']);
        require_csrf();
        $user = current_user();
        
        $data = get_json_body();
        $taskId = (int)($data['id'] ?? 0);
        $status = $data['status'] ?? '';
        
        if (!$taskId || !$status) {
            json_response(['error' => true, 'message' => 'ID and Status are required'], 400);
            return;
        }

        $task = $this->taskModel->findById($taskId);
        if (!$task) {
            json_response(['error' => true, 'message' => 'Task not found'], 404);
            return;
        }

        // Workers can only update tasks assigned to them
        if ($user['role'] === 'worker' && $task['assigned_to'] !== $user['id']) {
            json_response(['error' => true, 'message' => 'Access denied'], 403);
            return;
        }

        $success = $this->taskModel->updateStatus($taskId, $status);
        if ($success) {
            $isImportant = ($status === 'completed');
            AuditService::logAction('update_status', 'task', $taskId, $isImportant, ['supervisor', 'owner', 'worker']);
            json_response(['success' => true, 'message' => 'Task status updated']);
        } else {
            json_response(['error' => true, 'message' => 'Failed to update task status'], 500);
        }
    }

    /**
     * API: Delete task
     */
    public function apiDelete(): void {
        require_role(['supervisor', 'owner']);
        require_csrf();
        
        $data = get_json_body();
        $taskId = (int)($data['id'] ?? 0);
        
        if (!$taskId) {
            json_response(['error' => true, 'message' => 'ID is required'], 400);
            return;
        }

        $success = $this->taskModel->delete($taskId);
        if ($success) {
            AuditService::logAction('delete', 'task', $taskId);
            json_response(['success' => true, 'message' => 'Task deleted']);
        } else {
            json_response(['error' => true, 'message' => 'Failed to delete task'], 500);
        }
    }
}
