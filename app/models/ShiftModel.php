<?php
/**
 * Shift Model
 * Database operations for shift templates and assignments.
 */

class ShiftModel extends BaseModel
{
    protected string $table = 'shift_assignments';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a shift template.
     */
    public function createTemplate(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO shift_templates (tenant_id, farm_id, name, start_time, end_time, break_duration_minutes, is_recurring, days_of_week, is_active)
            VALUES (:tenant_id, :farm_id, :name, :start_time, :end_time, :break_duration_minutes, :is_recurring, :days_of_week, :is_active)
        ');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'farm_id' => $data['farm_id'],
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'break_duration_minutes' => $data['break_duration_minutes'] ?? 0,
            'is_recurring' => $data['is_recurring'] ?? true,
            'days_of_week' => $data['days_of_week'] ?? '1,2,3,4,5',
            'is_active' => $data['is_active'] ?? true
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get all shift templates for a farm.
     */
    public function getTemplatesByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM shift_templates WHERE farm_id = :farm_id AND tenant_id = :tenant_id AND is_active = 1 ORDER BY start_time ASC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a shift template by ID.
     */
    public function getTemplateById(int $id): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM shift_templates WHERE id = :id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create a shift assignment.
     */
    public function createAssignment(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO shift_assignments (tenant_id, farm_id, shift_template_id, user_id, assigned_date, start_time, end_time, break_duration_minutes, status, notes, created_by)
            VALUES (:tenant_id, :farm_id, :shift_template_id, :user_id, :assigned_date, :start_time, :end_time, :break_duration_minutes, :status, :notes, :created_by)
        ');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'farm_id' => $data['farm_id'],
            'shift_template_id' => $data['shift_template_id'] ?? null,
            'user_id' => $data['user_id'],
            'assigned_date' => $data['assigned_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'break_duration_minutes' => $data['break_duration_minutes'] ?? 0,
            'status' => $data['status'] ?? 'scheduled',
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get today's shift assignment for a user.
     */
    public function getTodayAssignment(int $userId): ?array
    {
        $today = date('Y-m-d');
        $stmt = $this->scopedQuery('
            SELECT sa.*, st.name as template_name
            FROM shift_assignments sa
            LEFT JOIN shift_templates st ON sa.shift_template_id = st.id
            WHERE sa.user_id = :user_id AND sa.assigned_date = :date AND sa.status != "cancelled" AND sa.tenant_id = :tenant_id
            ORDER BY sa.start_time ASC
            LIMIT 1
        ', [
            'user_id' => $userId,
            'date' => $today
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get shift assignments for a specific date and farm (roster view).
     */
    public function getAssignmentsByDate(int $farmId, string $date): array
    {
        $stmt = $this->scopedQuery('
            SELECT sa.*, u.name as worker_name, u.role, st.name as template_name
            FROM shift_assignments sa
            JOIN users u ON sa.user_id = u.id
            LEFT JOIN shift_templates st ON sa.shift_template_id = st.id
            WHERE sa.farm_id = :farm_id AND sa.assigned_date = :date AND sa.status != "cancelled" AND sa.tenant_id = :tenant_id
            ORDER BY sa.start_time ASC, u.name ASC
        ', [
            'farm_id' => $farmId,
            'date' => $date
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get shift assignments for a user within a date range.
     */
    public function getUserAssignments(int $userId, string $startDate, string $endDate): array
    {
        $stmt = $this->scopedQuery('
            SELECT sa.*, st.name as template_name
            FROM shift_assignments sa
            LEFT JOIN shift_templates st ON sa.shift_template_id = st.id
            WHERE sa.user_id = :user_id AND sa.assigned_date BETWEEN :start_date AND :end_date AND sa.status != "cancelled" AND sa.tenant_id = :tenant_id
            ORDER BY sa.assigned_date ASC, sa.start_time ASC
        ', [
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update shift assignment status.
     */
    public function updateAssignmentStatus(int $id, string $status): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE shift_assignments SET status = :status, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id',
            ['id' => $id, 'status' => $status]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a shift assignment.
     */
    public function deleteAssignment(int $id): bool
    {
        $stmt = $this->scopedQuery(
            'DELETE FROM shift_assignments WHERE id = :id AND tenant_id = :tenant_id',
            ['id' => $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Generate shift assignments from a template for a date range.
     */
    public function generateFromTemplate(int $templateId, string $startDate, string $endDate, array $userIds): int
    {
        $template = $this->getTemplateById($templateId);
        if (!$template) {
            throw new Exception('Shift template not found');
        }

        $tenantId = $this->getCurrentTenantId();
        $user = current_user();
        $created = 0;
        $daysOfWeek = explode(',', $template['days_of_week']);

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        $this->db->beginTransaction();
        try {
            while ($start <= $end) {
                $dayOfWeek = $start->format('N'); // 1=Monday, 7=Sunday
                
                if (in_array($dayOfWeek, $daysOfWeek)) {
                    foreach ($userIds as $userId) {
                        // Check if assignment already exists
                        $existing = $this->scopedQuery('
                            SELECT id FROM shift_assignments 
                            WHERE user_id = :user_id AND assigned_date = :date AND start_time = :start_time AND tenant_id = :tenant_id
                            LIMIT 1
                        ', [
                            'user_id' => $userId,
                            'date' => $start->format('Y-m-d'),
                            'start_time' => $template['start_time']
                        ])->fetch();

                        if (!$existing) {
                            $this->createAssignment([
                                'farm_id' => $template['farm_id'],
                                'shift_template_id' => $templateId,
                                'user_id' => $userId,
                                'assigned_date' => $start->format('Y-m-d'),
                                'start_time' => $template['start_time'],
                                'end_time' => $template['end_time'],
                                'break_duration_minutes' => $template['break_duration_minutes'],
                                'status' => 'scheduled',
                                'created_by' => $user['id'] ?? null
                            ]);
                            $created++;
                        }
                    }
                }
                
                $start->modify('+1 day');
            }
            
            $this->db->commit();
            return $created;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get shifts starting in a specific number of minutes for reminders.
     * Note: This is a simplified version for the cron.
     */
    public function getShiftsStartingInMinutes(int $minutes): array
    {
        // Find shifts starting between (now + minutes - 5) and (now + minutes)
        // This ensures we pick them up in a 5-minute cron window
        $stmt = $this->db->prepare("
            SELECT sa.*, u.name as worker_name, u.phone
            FROM shift_assignments sa
            JOIN users u ON sa.user_id = u.id
            WHERE sa.assigned_date = CURDATE()
            AND sa.status = 'scheduled'
            AND sa.start_time BETWEEN 
                SUBTIME(ADDTIME(CURTIME(), SEC_TO_TIME(:mins * 60)), '00:05:00')
                AND ADDTIME(CURTIME(), SEC_TO_TIME(:mins2 * 60))
        ");
        $stmt->execute(['mins' => $minutes, 'mins2' => $minutes]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get shifts that are no-shows (started X minutes ago with no clock-in).
     */
    public function getNoShowsSinceMinutes(int $minutes): array
    {
        $stmt = $this->db->prepare("
            SELECT sa.*, u.name as worker_name, f.owner_id as supervisor_id
            FROM shift_assignments sa
            JOIN users u ON sa.user_id = u.id
            JOIN farms f ON sa.farm_id = f.id
            LEFT JOIN attendance a ON sa.id = a.shift_assignment_id
            WHERE sa.assigned_date = CURDATE()
            AND sa.status = 'scheduled'
            AND sa.start_time < SUBTIME(CURTIME(), SEC_TO_TIME(:mins * 60))
            AND a.id IS NULL
        ");
        $stmt->execute(['mins' => $minutes]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific shift assignment by ID.
     */
    public function getById(int $id): ?array {
        $stmt = $this->scopedQuery(
            'SELECT * FROM shift_assignments WHERE id = :id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
