<?php
/**
 * Settings Controller
 * Owner-only farm settings management.
 */

class SettingsController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Display farm settings page.
     */
    public function index(): void
    {
        role_gate(['owner']);
        $user = current_user();
        $farmId = $user['farm_id'];

        $stmt = $this->db->prepare('SELECT * FROM farms WHERE id = :id');
        $stmt->execute(['id' => $farmId]);
        $farm = $stmt->fetch();

        view('owner/system_settings', ['farm' => $farm]);
    }

    /**
     * Update farm settings.
     */
    public function update(): void
    {
        role_gate(['owner']);
        require_csrf();
        $user = current_user();
        $farmId = $user['farm_id'];

        // Validate
        $name = sanitize_input($_POST['name'] ?? '');
        $geofenceRadius = (int)($_POST['geofence_radius_metres'] ?? 200);
        $latitude = $_POST['latitude'] !== '' ? filter_var($_POST['latitude'], FILTER_VALIDATE_FLOAT) : null;
        $longitude = $_POST['longitude'] !== '' ? filter_var($_POST['longitude'], FILTER_VALIDATE_FLOAT) : null;
        $overtimeThreshold = (int)($_POST['overtime_threshold'] ?? 40);
        $defaultPaymentType = sanitize_input($_POST['default_payment_type'] ?? 'hourly');
        $waPhoneNumberId = sanitize_input($_POST['wa_phone_number_id'] ?? '');
        $waAccessToken = sanitize_input($_POST['wa_access_token'] ?? '');

        if (empty($name)) {
            $_SESSION['error'] = 'Farm name is required.';
            redirect('/owner/settings');
            return;
        }

        $validTypes = ['hourly', 'daily', 'monthly', 'unit'];
        if (!in_array($defaultPaymentType, $validTypes)) {
            $defaultPaymentType = 'hourly';
        }

        $stmt = $this->db->prepare('
            UPDATE farms SET 
                name = :name, 
                geofence_radius_metres = :radius,
                latitude = :latitude,
                longitude = :longitude,
                overtime_threshold = :overtime,
                default_payment_type = :payment_type,
                wa_phone_number_id = :wa_phone_number_id,
                wa_access_token = :wa_access_token
            WHERE id = :id
        ');
        
        $success = $stmt->execute([
            'name' => $name,
            'radius' => $geofenceRadius,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'overtime' => $overtimeThreshold,
            'payment_type' => $defaultPaymentType,
            'wa_phone_number_id' => $waPhoneNumberId,
            'wa_access_token' => $waAccessToken,
            'id' => $farmId
        ]);

        if ($success) {
            AuditService::logAction('updated_farm_settings', 'farms', $farmId);
            $_SESSION['success'] = 'Farm settings updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update farm settings.';
        }

        redirect('/owner/settings');
    }
}
