<?php
/**
 * Notification Cron Script
 * Runs every 5 minutes to check for shift reminders and no-shows.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/models/BaseModel.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/models/ShiftModel.php';
require_once __DIR__ . '/../app/models/NotificationModel.php';
require_once __DIR__ . '/../app/services/NotificationService.php';
require_once __DIR__ . '/../app/services/ShiftService.php';

// Initialize services
$shiftService = new ShiftService();
$notificationService = new NotificationService();
$shiftModel = new ShiftModel();

echo "Starting notification cron [" . date('Y-m-d H:i:s') . "]\n";

// 1. Shift Reminders (Starts in ~60 minutes)
$remindersBatch = $shiftModel->getShiftsStartingInMinutes(60);
echo "Processing " . count($remindersBatch) . " shift reminders...\n";
foreach ($remindersBatch as $shift) {
    $notificationService->send($shift['worker_id'], 'shift_reminder', [
        'start_time' => $shift['start_time']
    ], ['push', 'whatsapp']);
}

// 2. No-Show Alerts (30 minutes after start time, no clock-in)
$noShowsBatch = $shiftModel->getNoShowsSinceMinutes(30);
echo "Processing " . count($noShowsBatch) . " no-show alerts...\n";
foreach ($noShowsBatch as $shift) {
    // Notify supervisor
    if (!empty($shift['supervisor_id'])) {
        $notificationService->send($shift['supervisor_id'], 'no_show_alert', [
            'worker_name' => $shift['worker_name'],
            'start_time' => $shift['start_time']
        ], ['in_app', 'push']);
    }
}

echo "Cron finished [" . date('Y-m-d H:i:s') . "]\n";
