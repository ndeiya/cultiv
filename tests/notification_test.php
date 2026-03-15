<?php
/**
 * Notification Verification Script
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/auth_helper.php';
require_once __DIR__ . '/../app/models/BaseModel.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/models/NotificationModel.php';
require_once __DIR__ . '/../app/services/NotificationService.php';

// Mock session/tenant for testing
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
// BaseModel uses current_user() which checks $_SESSION['user']
$_SESSION['user'] = ['id' => 1, 'tenant_id' => 1, 'role' => 'owner'];

$notificationService = new NotificationService();
$notificationModel = new NotificationModel();

echo "Running Notification System Verification...\n";

// 1. Test In-App Notification Creation
echo "Testing In-App Notification... ";
$testUserId = 1; // Assuming user 1 exists
try {
    $notificationService->send($testUserId, 'shift_reminder', [
        'start_time' => '08:00:00'
    ], ['in_app']);
    
    $unread = $notificationModel->getUnread($testUserId);
    $found = false;
    foreach ($unread as $n) {
        if ($n['type'] === 'shift_reminder' && $n['title'] === 'Upcoming Shift Reminder') {
            $found = true;
            break;
        }
    }
    
    if ($found) {
        echo "OK\n";
    } else {
        echo "FAILED (Record not found)\n";
    }
} catch (Exception $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
}

// 2. Test WhatsApp Dispatch (should log or fail gracefully if tokens are placeholders)
echo "Testing WhatsApp Dispatch (Dry Run)... ";
try {
    $notificationService->send($testUserId, 'payslip_ready', [
        'period_end' => '2026-03-31'
    ], ['whatsapp']);
    echo "OK (Check logs for API response)\n";
} catch (Exception $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
}

// 3. Test Leave Update Integration
echo "Testing Leave Update trigger... ";
try {
    $notificationService->send($testUserId, 'leave_update', [
        'status' => 'approved',
        'start_date' => '2026-04-01'
    ], ['in_app']);
    echo "OK\n";
} catch (Exception $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
}

echo "Verification complete.\n";
