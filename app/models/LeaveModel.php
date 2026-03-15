<?php
/**
 * Leave Model
 * Database operations for leave requests and balances.
 */

class LeaveModel extends BaseModel
{
    protected string $table = 'leave_requests';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a leave request.
     */
    public function createRequest(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO leave_requests (tenant_id, user_id, leave_type, start_date, end_date, total_days, reason, status)
            VALUES (:tenant_id, :user_id, :leave_type, :start_date, :end_date, :total_days, :reason, :status)
        ');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'user_id' => $data['user_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $data['total_days'],
            'reason' => $data['reason'] ?? null,
            'status' => $data['status'] ?? 'pending'
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get leave requests for a user.
     */
    public function getUserRequests(int $userId, ?string $status = null): array
    {
        $sql = '
            SELECT * FROM leave_requests 
            WHERE user_id = :user_id AND tenant_id = :tenant_id
        ';
        $params = ['user_id' => $userId];
        
        if ($status) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }
        
        $sql .= ' ORDER BY start_date DESC';
        
        $stmt = $this->scopedQuery($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all pending leave requests for a farm (for supervisors/owners).
     */
    public function getPendingRequests(int $farmId): array
    {
        $stmt = $this->scopedQuery('
            SELECT lr.*, u.name as worker_name, u.role
            FROM leave_requests lr
            JOIN users u ON lr.user_id = u.id
            WHERE u.farm_id = :farm_id 
            AND lr.status = "pending" 
            AND lr.tenant_id = :tenant_id
            ORDER BY lr.created_at ASC
        ', ['farm_id' => $farmId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Approve a leave request.
     */
    public function approveRequest(int $id, int $approvedBy): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE leave_requests 
            SET status = "approved", approved_by = :approved_by, approved_at = NOW(), updated_at = NOW()
            WHERE id = :id AND tenant_id = :tenant_id
        ', [
            'id' => $id,
            'approved_by' => $approvedBy
        ]);
        
        if ($stmt->rowCount() > 0) {
            // Update leave balance
            $request = $this->findById($id);
            if ($request) {
                $this->updateLeaveBalance(
                    $request['user_id'],
                    $request['leave_type'],
                    (int)date('Y', strtotime($request['start_date'])),
                    (float)$request['total_days']
                );
            }
            return true;
        }
        return false;
    }

    /**
     * Reject a leave request.
     */
    public function rejectRequest(int $id, int $rejectedBy, string $reason): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE leave_requests 
            SET status = "rejected", approved_by = :approved_by, rejection_reason = :reason, updated_at = NOW()
            WHERE id = :id AND tenant_id = :tenant_id
        ', [
            'id' => $id,
            'approved_by' => $rejectedBy,
            'reason' => $reason
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get or create leave balance for a user.
     */
    public function getLeaveBalance(int $userId, string $leaveType, int $year): ?array
    {
        $stmt = $this->scopedQuery('
            SELECT * FROM leave_balances 
            WHERE user_id = :user_id AND leave_type = :leave_type AND year = :year AND tenant_id = :tenant_id
            LIMIT 1
        ', [
            'user_id' => $userId,
            'leave_type' => $leaveType,
            'year' => $year
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Update leave balance (used when leave is approved).
     */
    private function updateLeaveBalance(int $userId, string $leaveType, int $year, float $daysUsed): void
    {
        $balance = $this->getLeaveBalance($userId, $leaveType, $year);
        $tenantId = $this->getCurrentTenantId();
        
        if ($balance) {
            // Update existing balance
            $stmt = $this->scopedQuery('
                UPDATE leave_balances 
                SET used_days = used_days + :days, updated_at = NOW()
                WHERE id = :id AND tenant_id = :tenant_id
            ', [
                'id' => $balance['id'],
                'days' => $daysUsed
            ]);
        } else {
            // Create new balance (assumes total_days was set elsewhere, e.g., during onboarding)
            // For now, we'll create with 0 total and increment used
            $stmt = $this->db->prepare('
                INSERT INTO leave_balances (tenant_id, user_id, leave_type, total_days, used_days, year)
                VALUES (:tenant_id, :user_id, :leave_type, 0, :used_days, :year)
                ON DUPLICATE KEY UPDATE used_days = used_days + :used_days
            ');
            $stmt->execute([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'leave_type' => $leaveType,
                'used_days' => $daysUsed,
                'year' => $year
            ]);
        }
    }

    /**
     * Get leave balance for a user (all types for a year).
     */
    public function getAllBalances(int $userId, int $year): array
    {
        $stmt = $this->scopedQuery('
            SELECT * FROM leave_balances 
            WHERE user_id = :user_id AND year = :year AND tenant_id = :tenant_id
            ORDER BY leave_type ASC
        ', [
            'user_id' => $userId,
            'year' => $year
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unpaid leave days for a user in a date range (for payroll deduction).
     */
    public function getUnpaidLeaveDays(int $userId, string $startDate, string $endDate): float
    {
        $stmt = $this->scopedQuery('
            SELECT COALESCE(SUM(total_days), 0) as total
            FROM leave_requests
            WHERE user_id = :user_id 
            AND leave_type = "unpaid"
            AND status = "approved"
            AND start_date <= :end_date
            AND end_date >= :start_date
            AND tenant_id = :tenant_id
        ', [
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }
}
