<?php
/**
 * Leave Controller
 * Handles leave requests, history, and approvals.
 */

class LeaveController
{
    private LeaveService $leaveService;

    public function __construct()
    {
        $this->leaveService = new LeaveService();
    }

    /**
     * Show leave request form for workers.
     */
    public function create(): void
    {
        require_role('worker');
        $user = current_user();
        $year = (int)date('Y');
        $balances = $this->leaveService->getLeaveBalances($user['id'], $year);

        view('worker/leave_request', [
            'balances' => $balances,
            'title' => 'Request Leave'
        ]);
    }

    /**
     * Store a new leave request.
     */
    public function store(): void
    {
        require_role('worker');
        check_csrf();

        $user = current_user();
        $data = [
            'user_id' => $user['id'],
            'leave_type' => sanitize_input($_POST['leave_type']),
            'start_date' => sanitize_input($_POST['start_date']),
            'end_date' => sanitize_input($_POST['end_date']),
            'reason' => sanitize_input($_POST['reason'] ?? '')
        ];

        try {
            $this->leaveService->createRequest($data);
            AuditService::logAction('request_leave', 'leave_requests', null, true, ['owner', 'supervisor']);
            $_SESSION['success'] = "Leave request submitted successfully.";
            redirect('/worker/leave/history');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect('/worker/leave/request');
        }
    }

    /**
     * Show leave history for a worker.
     */
    public function history(): void
    {
        require_role('worker');
        $user = current_user();
        $requests = $this->leaveService->getUserRequests($user['id']);
        $year = (int)date('Y');
        $balances = $this->leaveService->getLeaveBalances($user['id'], $year);

        view('worker/leave_history', [
            'requests' => $requests,
            'balances' => $balances,
            'title' => 'My Leave History'
        ]);
    }

    /**
     * Show pending leave requests for owners/supervisors.
     */
    public function approvals(): void
    {
        require_role(['owner', 'supervisor']);
        $user = current_user();
        $requests = $this->leaveService->getPendingRequests($user['farm_id']);

        view('owner/leave_approvals', [
            'requests' => $requests,
            'title' => 'Leave Approvals'
        ]);
    }

    /**
     * Update leave request status (Approve/Reject).
     */
    public function updateStatus(): void
    {
        require_role(['owner', 'supervisor']);
        check_csrf();

        $id = (int)$_POST['id'];
        $action = $_POST['action']; // 'approve' or 'reject'
        $reason = sanitize_input($_POST['rejection_reason'] ?? '');
        $user = current_user();

        try {
            if ($action === 'approve') {
                $this->leaveService->approveRequest($id, $user['id']);
                $_SESSION['success'] = "Leave request approved.";
            } else {
                $this->leaveService->rejectRequest($id, $user['id'], $reason);
                $_SESSION['success'] = "Leave request rejected.";
            }
            AuditService::logAction($action . '_leave', 'leave_requests', $id, true, ['worker', 'owner', 'supervisor']);
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating leave request: " . $e->getMessage();
        }

        redirect('/owner/leave/approvals');
    }
}
