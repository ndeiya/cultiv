<?php
/**
 * Leave Service
 * Business logic for leave management and approval workflow.
 */

class LeaveService
{
    private LeaveModel $leaveModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->leaveModel = new LeaveModel();
        $this->notificationService = new NotificationService();
    }

    /**
     * Create a leave request.
     */
    public function createRequest(array $data): int
    {
        // Calculate total days
        $start = new DateTime($data['start_date']);
        $end = new DateTime($data['end_date']);
        $end->modify('+1 day'); // Include end date
        $interval = $start->diff($end);
        $totalDays = (float)$interval->days;
        
        // Check if user has sufficient balance (for paid leave types)
        if ($data['leave_type'] !== 'unpaid') {
            $year = (int)date('Y', strtotime($data['start_date']));
            $balance = $this->leaveModel->getLeaveBalance($data['user_id'], $data['leave_type'], $year);
            
            if ($balance) {
                $available = $balance['total_days'] - $balance['used_days'];
                if ($totalDays > $available) {
                    throw new Exception("Insufficient leave balance. Available: {$available} days, Requested: {$totalDays} days.");
                }
            }
        }
        
        $data['total_days'] = $totalDays;
        return $this->leaveModel->createRequest($data);
    }

    /**
     * Approve a leave request.
     */
    public function approveRequest(int $requestId, int $approvedBy): bool
    {
        $result = $this->leaveModel->approveRequest($requestId, $approvedBy);
        if ($result) {
            $request = $this->leaveModel->getRequest($requestId);
            if ($request) {
                $this->notificationService->send($request['user_id'], 'leave_update', [
                    'status' => 'approved',
                    'start_date' => $request['start_date']
                ]);
            }
        }
        return $result;
    }

    /**
     * Reject a leave request.
     */
    public function rejectRequest(int $requestId, int $rejectedBy, string $reason): bool
    {
        $result = $this->leaveModel->rejectRequest($requestId, $rejectedBy, $reason);
        if ($result) {
            $request = $this->leaveModel->getRequest($requestId);
            if ($request) {
                $this->notificationService->send($request['user_id'], 'leave_update', [
                    'status' => 'rejected',
                    'start_date' => $request['start_date']
                ]);
            }
        }
        return $result;
    }

    /**
     * Get leave requests for a user.
     */
    public function getUserRequests(int $userId, ?string $status = null): array
    {
        return $this->leaveModel->getUserRequests($userId, $status);
    }

    /**
     * Get pending leave requests for approval.
     */
    public function getPendingRequests(int $farmId): array
    {
        return $this->leaveModel->getPendingRequests($farmId);
    }

    /**
     * Get leave balances for a user.
     */
    public function getLeaveBalances(int $userId, int $year): array
    {
        return $this->leaveModel->getAllBalances($userId, $year);
    }

    /**
     * Get unpaid leave days for payroll deduction.
     */
    public function getUnpaidLeaveDays(int $userId, string $startDate, string $endDate): float
    {
        return $this->leaveModel->getUnpaidLeaveDays($userId, $startDate, $endDate);
    }
}
