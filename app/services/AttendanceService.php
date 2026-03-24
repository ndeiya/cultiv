<?php

class AttendanceService {
    private AttendanceModel $attendanceModel;
    private UserModel $userModel;
    private ShiftService $shiftService;
    private NotificationService $notificationService;
    private PDO $db;

    public function __construct() {
        $this->attendanceModel = new AttendanceModel();
        $this->userModel = new UserModel();
        $this->shiftService = new ShiftService();
        $this->notificationService = new NotificationService();
        $this->db = Database::getInstance();
    }

    public function getOpenSession($userId) {
        return $this->attendanceModel->getOpenSession($userId);
    }

    public function getHistory($userId, $dateFrom = null, $dateTo = null) {
        $dateRange = null;
        if ($dateFrom && $dateTo) {
            $dateRange = ['from' => $dateFrom, 'to' => $dateTo];
        }
        return $this->attendanceModel->getHistory($userId, $dateRange);
    }

    public function getDailySummary($farmId, $date = null) {
        return $this->attendanceModel->getDailySummary($farmId, $date);
    }

    /**
     * Clock in with geofence validation and shift assignment linking.
     */
    public function clockIn($farmId, $userId, $lat, $lng) {
        $openSession = $this->attendanceModel->getOpenSession($userId);
        if ($openSession) {
            throw new Exception("You are already clocked in.");
        }

        // Get farm data for geofence validation
        $user = current_user();
        $tenantId = $user['tenant_id'] ?? 1;
        $stmt = $this->db->prepare('
            SELECT id, name, latitude, longitude, geofence_radius_metres, geofence_polygon 
            FROM farms 
            WHERE id = :farm_id AND tenant_id = :tenant_id 
            LIMIT 1
        ');
        $stmt->execute(['farm_id' => $farmId, 'tenant_id' => $tenantId]);
        $farm = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$farm) {
            throw new Exception("Farm not found.");
        }

        // Validate geofence
        $geofenceResult = GeofenceHelper::validateFarmGeofence($lat, $lng, $farm);
        if (!$geofenceResult['valid']) {
            // Notify supervisor of geofence violation ONLY if farm is configured
            if (!empty($farm['latitude']) && !empty($farm['longitude'])) {
                $supervisor = $this->userModel->getSupervisorForFarm($farmId);
                if ($supervisor) {
                    $this->notificationService->send($supervisor['id'], 'geofence_alert', [
                        'worker_name' => $user['name'],
                        'farm_name' => $farm['name']
                    ]);
                }
            }
            throw new Exception($geofenceResult['message']);
        }

        // Get today's shift assignment
        $shiftAssignment = $this->shiftService->getTodayAssignment($userId);
        $shiftAssignmentId = $shiftAssignment['id'] ?? null;
        
        $status = 'normal';
        $latenessMinutes = 0;

        // Calculate lateness if shift assignment exists
        if ($shiftAssignment) {
            $actualClockInTime = date('H:i:s');
            $deviation = $this->shiftService->calculateAttendanceDeviation($shiftAssignment, $actualClockInTime);
            $latenessMinutes = $deviation['lateness_minutes'];
            $status = $deviation['status'];
        }

        $id = $this->attendanceModel->insertClockIn($farmId, $userId, $lat, $lng, $status, $shiftAssignmentId);
        
        return [
            'id' => $id,
            'message' => 'Clocked in successfully',
            'geofence_valid' => true,
            'shift_assignment_id' => $shiftAssignmentId,
            'lateness_minutes' => $latenessMinutes,
            'status' => $status
        ];
    }

    public function clockOut($userId, $lat, $lng) {
        $openSession = $this->attendanceModel->getOpenSession($userId);
        if (!$openSession) {
            throw new Exception("No open attendance session found.");
        }

        // Calculate total minutes
        $clockInTime = new DateTime($openSession['clock_in']);
        $clockOutTime = new DateTime(); // Defaults to current time
        $interval = $clockInTime->diff($clockOutTime);
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        $this->attendanceModel->updateClockOut($openSession['id'], $lat, $lng, $totalMinutes);

        return [
            'id' => $openSession['id'],
            'total_minutes' => $totalMinutes,
            'message' => 'Clocked out successfully'
        ];
    }
}
