<?php
/**
 * Report Service
 * Business logic for reports including file uploads.
 */

class ReportService
{
    private ReportModel $reportModel;
    private NotificationService $notificationService;
    private UserModel $userModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->notificationService = new NotificationService();
        $this->userModel = new UserModel();
    }

    /**
     * Create a new report with optional photos.
     *
     * @param array $data   The report text data
     * @param array $files  The $_FILES array (expected to have 'photos')
     * @return array        ['success' => bool, 'message' => string, 'report_id' => int|null]
     */
    public function createReport(array $data, array $files): array
    {
        $photos = [];

        // Handle multiple file uploads
        if (isset($files['photos']) && is_array($files['photos']['name']) && $files['photos']['name'][0] !== '') {
            $fileCount = count($files['photos']['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                // Reconstruct the individual file array for the helper
                $fileArray = [
                    'name'     => $files['photos']['name'][$i],
                    'type'     => $files['photos']['type'][$i],
                    'tmp_name' => $files['photos']['tmp_name'][$i],
                    'error'    => $files['photos']['error'][$i],
                    'size'     => $files['photos']['size'][$i],
                ];

                // Skip empty uploads
                if ($fileArray['error'] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $uploadResult = upload_image($fileArray, 'reports');
                
                if (!$uploadResult['success']) {
                    return [
                        'success' => false,
                        'message' => 'Image upload failed for ' . $fileArray['name'] . ': ' . $uploadResult['error'],
                        'report_id' => null
                    ];
                }

                // Add to our list of successfully uploaded photos
                $photos[] = $uploadResult['filename'];
            }
        }

        try {
            $reportId = $this->reportModel->insert($data, $photos);

            // Trigger Crop Health Alert if applicable
            if ($data['category'] === 'crop' && ($data['severity'] === 'high' || $data['severity'] === 'medium')) {
                $farmId = $data['farm_id'];
                // Notify owner/supervisor
                $targets = $this->userModel->getAllByFarm($farmId);
                foreach ($targets as $target) {
                    if (in_array($target['role'], ['owner', 'supervisor'])) {
                        $this->notificationService->send($target['id'], 'crop_health_alert', [
                            'description' => $data['description'],
                            'severity' => $data['severity']
                        ]);
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Report submitted successfully.',
                'report_id' => $reportId
            ];
        } catch (Exception $e) {
            error_log('Report Creation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'A database error occurred while saving the report.',
                'report_id' => null
            ];
        }
    }
}
