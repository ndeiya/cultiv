<?php
/**
 * Shift Service
 * Business logic for shift scheduling, rosters, and attendance deviation calculation.
 */

class ShiftService
{
    private ShiftModel $shiftModel;

    public function __construct()
    {
        $this->shiftModel = new ShiftModel();
    }

    /**
     * Get today's shift assignment for a user.
     */
    public function getTodayAssignment(int $userId): ?array
    {
        return $this->shiftModel->getTodayAssignment($userId);
    }

    /**
     * Get roster (all shift assignments) for a specific date and farm.
     */
    public function getRoster(int $farmId, string $date): array
    {
        return $this->shiftModel->getAssignmentsByDate($farmId, $date);
    }

    /**
     * Create a shift template.
     */
    public function createTemplate(array $data): int
    {
        return $this->shiftModel->createTemplate($data);
    }

    /**
     * Generate shift assignments from a template.
     */
    public function generateFromTemplate(int $templateId, string $startDate, string $endDate, array $userIds): int
    {
        return $this->shiftModel->generateFromTemplate($templateId, $startDate, $endDate, $userIds);
    }

    /**
     * Calculate attendance deviation (lateness/earliness) for a shift assignment.
     * 
     * @param array $assignment Shift assignment record
     * @param string $actualClockInTime Actual clock-in time (H:i:s format)
     * @return array ['lateness_minutes' => int, 'earliness_minutes' => int, 'status' => string]
     */
    public function calculateAttendanceDeviation(array $assignment, string $actualClockInTime): array
    {
        $scheduledStart = $assignment['start_time'];
        $actualTime = new DateTime($actualClockInTime);
        $scheduledTime = new DateTime($scheduledStart);
        
        $diffSeconds = $actualTime->getTimestamp() - $scheduledTime->getTimestamp();
        $diffMinutes = (int)round($diffSeconds / 60);
        
        $result = [
            'lateness_minutes' => 0,
            'earliness_minutes' => 0,
            'status' => 'normal'
        ];
        
        if ($diffMinutes > 0) {
            // Late
            $result['lateness_minutes'] = $diffMinutes;
            $result['status'] = $diffMinutes > 15 ? 'late' : 'normal'; // 15 min grace period
        } elseif ($diffMinutes < 0) {
            // Early
            $result['earliness_minutes'] = abs($diffMinutes);
            $result['status'] = 'normal';
        }
        
        return $result;
    }

    /**
     * Get worker's weekly schedule.
     */
    public function getWorkerSchedule(int $userId, string $weekStartDate): array
    {
        $start = new DateTime($weekStartDate);
        $end = clone $start;
        $end->modify('+6 days');
        
        return $this->shiftModel->getUserAssignments(
            $userId,
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        );
    }

    /**
     * Create a one-off shift assignment (not from template).
     */
    public function createOneOffAssignment(array $data): int
    {
        return $this->shiftModel->createAssignment($data);
    }

    /**
     * Update shift assignment status.
     */
    public function updateAssignmentStatus(int $id, string $status): bool
    {
        return $this->shiftModel->updateAssignmentStatus($id, $status);
    }
}
