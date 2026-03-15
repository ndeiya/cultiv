<?php

class AttendanceModel extends BaseModel {
    protected string $table = 'attendance';

    public function __construct() {
        parent::__construct();
    }

    public function insertClockIn($farmId, $userId, $lat, $lng, $status = 'normal', $shiftAssignmentId = null) {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare("
            INSERT INTO attendance (tenant_id, farm_id, user_id, shift_assignment_id, clock_in, clock_in_lat, clock_in_lng, status)
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?)
        ");
        $stmt->execute([$tenantId, $farmId, $userId, $shiftAssignmentId, $lat, $lng, $status]);
        return $this->db->lastInsertId();
    }

    public function updateClockOut($id, $lat, $lng, $totalMinutes) {
        $stmt = $this->scopedQuery("
            UPDATE attendance
            SET clock_out = CURRENT_TIMESTAMP, clock_out_lat = :lat, clock_out_lng = :lng, total_minutes = :total_minutes
            WHERE id = :id AND tenant_id = :tenant_id
        ", [
            'id' => $id,
            'lat' => $lat,
            'lng' => $lng,
            'total_minutes' => $totalMinutes
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getOpenSession($userId) {
        $stmt = $this->scopedQuery("
            SELECT * FROM attendance
            WHERE user_id = :user_id AND clock_out IS NULL AND tenant_id = :tenant_id
            ORDER BY clock_in DESC
            LIMIT 1
        ", ['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getHistory($userId, $dateRange = null, int $page = 1, int $perPage = 20) {
        $sql = "SELECT * FROM attendance WHERE user_id = :user_id AND tenant_id = :tenant_id";
        $params = ['user_id' => $userId];

        if ($dateRange && isset($dateRange['from']) && isset($dateRange['to'])) {
            $sql .= " AND DATE(clock_in) BETWEEN :from AND :to";
            $params['from'] = $dateRange['from'];
            $params['to'] = $dateRange['to'];
        }

        $sql .= " ORDER BY clock_in DESC";

        // Add tenant_id to params
        $params['tenant_id'] = $this->getCurrentTenantId();
        return paginate($this->db, $sql, $params, $page, $perPage);
    }

    public function getDailySummary($farmId, $date = null, int $page = 1, int $perPage = 20) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $sql = "
            SELECT a.*, u.name, u.role
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            WHERE a.farm_id = :farm_id AND a.tenant_id = :tenant_id AND DATE(a.clock_in) = :date
            ORDER BY a.clock_in DESC
        ";
        $params = [
            'farm_id' => $farmId,
            'date' => $date,
            'tenant_id' => $this->getCurrentTenantId()
        ];

        return paginate($this->db, $sql, $params, $page, $perPage);
    }
}
