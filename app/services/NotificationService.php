<?php
/**
 * Notification Service
 * Centralizes notification dispatch via In-App, WhatsApp, and Push.
 */

class NotificationService {
    private NotificationModel $notificationModel;
    private UserModel $userModel;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    /**
     * Send a notification to a specific user.
     * 
     * @param int $userId Recipient ID
     * @param string $type Notification type (e.g., 'shift_reminder')
     * @param array $data Contextual data for rendering
     * @param array $channels Specific channels to use (default: in_app, whatsapp)
     */
    public function send(int $userId, string $type, array $data, array $channels = ['in_app', 'whatsapp']): void {
        $user = $this->userModel->findById($userId);
        if (!$user) return;

        $title = $this->renderTitle($type, $data);
        $body = $this->renderBody($type, $data);

        // 1. Create In-App Notification (always logged if requested)
        if (in_array('in_app', $channels)) {
            $this->notificationModel->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data_json' => $data,
                'channel' => 'in_app'
            ]);
        }

        // 2. Dispatch to WhatsApp
        if (in_array('whatsapp', $channels) && !empty($user['phone'])) {
            // The new sendWhatsApp expects the full user array and a message string
            $this->sendWhatsApp($user, $body); // Assuming $body is the message to send
        }

        // 3. Dispatch to Push (Placeholder)
        if (in_array('push', $channels)) {
            $this->sendPush($userId, $title, $body, $data);
        }
    }

    /**
     * Send WhatsApp message using Meta Cloud API.
     */
    private function sendWhatsApp(array $user, string $message): bool {
        // Fetch credentials from farm settings for the current tenant
        $farmId = $user['farm_id'] ?? null;
        $phoneNumberId = defined('WA_PHONE_NUMBER_ID') ? WA_PHONE_NUMBER_ID : null;
        $accessToken = defined('WA_ACCESS_TOKEN') ? WA_ACCESS_TOKEN : null;

        if ($farmId) {
            $stmt = $this->db->prepare('SELECT wa_phone_number_id, wa_access_token FROM farms WHERE id = :id');
            $stmt->execute(['id' => $farmId]);
            $farm = $stmt->fetch();
            
            if ($farm) {
                if (!empty($farm['wa_phone_number_id'])) {
                    $phoneNumberId = $farm['wa_phone_number_id'];
                }
                if (!empty($farm['wa_access_token'])) {
                    $accessToken = $farm['wa_access_token'];
                }
            }
        }

        if (empty($phoneNumberId) || empty($accessToken) || $phoneNumberId === 'YOUR_PHONE_NUMBER_ID' || $accessToken === 'YOUR_ACCESS_TOKEN') {
            // Silently return if not configured - WhatsApp is optional
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $user['phone'] ?? '');
        if (empty($phone)) return false;

        $url = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second total timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 2 second connect timeout

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // In a real app, we'd log the response status
    }

    /**
     * Dispatch Push notification (Placeholder implementation).
     */
    private function sendPush(int $userId, string $title, string $body, array $data): void {
        // Implementation for FCM or other push provider
        error_log("Push notification placeholder called for user $userId: $title");
    }

    /**
     * Render notification title based on type.
     */
    private function renderTitle(string $type, array $data): string {
        switch ($type) {
            case 'shift_reminder': return "Upcoming Shift Reminder";
            case 'payslip_ready':   return "Payslip Generated";
            case 'geofence_alert': return "Geofence Violation Alert";
            case 'leave_update':    return "Leave Request Update";
            case 'crop_health_alert': return "Crop Health Alert";
            default:               return "New Notification";
        }
    }

    /**
     * Render notification body based on type.
     */
    private function renderBody(string $type, array $data): string {
        switch ($type) {
            case 'shift_reminder':
                return "Reminder: Your shift starts at " . ($data['start_time'] ?? 'scheduled time') . ".";
            case 'payslip_ready':
                return "Your payslip for period ending " . ($data['period_end'] ?? 'N/A') . " is now available.";
            case 'geofence_alert':
                return "Worker " . ($data['worker_name'] ?? 'Unknown') . " clocked in outside the geofence.";
            case 'leave_update':
                return "Your leave request from " . ($data['start_date'] ?? 'N/A') . " has been " . ($data['status'] ?? 'processed') . ".";
            case 'crop_health_alert':
                return "Health Alert: " . ($data['severity'] ?? 'issue') . " severity incident reported: " . ($data['description'] ?? '');
            default:
                return "You have a new message from Cultiv.";
        }
    }

    /**
     * Build template components for WhatsApp.
     */
    private function buildWhatsAppComponents(string $type, array $data): array {
        // Simplified component builder for WhatsApp templates
        $parameters = [];
        
        if ($type === 'shift_reminder') {
            $parameters[] = ['type' => 'text', 'text' => $data['start_time'] ?? ''];
        } elseif ($type === 'payslip_ready') {
            $parameters[] = ['type' => 'text', 'text' => $data['period_end'] ?? ''];
        }
        
        return [
            [
                'type' => 'body',
                'parameters' => $parameters
            ]
        ];
    }
}
