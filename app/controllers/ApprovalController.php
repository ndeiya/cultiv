<?php
/**
 * Approval Controller
 * Handles owner/supervisor approval of worker submissions.
 */

class ApprovalController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->role_gate(['owner', 'supervisor']);
    }

    /**
     * Show pending submissions from crops, animals, equipment, and inventory.
     */
    public function index() {
        $farmId = $_SESSION['farm_id'];
        
        // Fetch pending items from different tables
        $cropModel = new CropModel();
        $animalModel = new AnimalModel();
        $equipmentModel = new EquipmentModel();
        $inventoryModel = new InventoryModel();
        $reportModel = new ReportModel();

        $data = [
            'crops' => $cropModel->getPendingByFarm($farmId),
            'animals' => $animalModel->getPendingByFarm($farmId),
            'equipment' => $equipmentModel->getPendingByFarm($farmId),
            'inventory' => $inventoryModel->getPendingByFarm($farmId),
            'reports' => $reportModel->scopedQuery("SELECT r.*, u.name as reporter_name 
                                                   FROM reports r 
                                                   JOIN users u ON r.user_id = u.id 
                                                   WHERE r.status = 'pending' AND r.farm_id = ?", [$farmId])
        ];

        $this->view('owner/approvals', $data);
    }

    /**
     * Approve a pending submission.
     */
    public function approve() {
        $this->require_csrf();
        $data = $this->get_json_input();
        
        $type = $data['type'] ?? '';
        $id = $data['id'] ?? 0;
        $farmId = $_SESSION['farm_id'];

        if (!$type || !$id) {
            $this->json_error('Invalid request parameters.');
        }

        $tableToModel = [
            'crop' => 'CropModel',
            'animal' => 'AnimalModel',
            'equipment' => 'EquipmentModel',
            'inventory' => 'InventoryModel'
        ];

        if ($type === 'report') {
            $model = new ReportModel();
            $success = $model->updateStatus($id, $farmId, 'open');
        } else {
            if (!isset($tableToModel[$type])) {
                $this->json_error('Invalid item type.');
            }
            $modelClass = $tableToModel[$type];
            $model = new $modelClass();
            $success = $model->updateApprovalStatus($id, $farmId, 'approved');
        }

        if ($success) {
            $this->json_success(['message' => ucfirst($type) . ' approved successfully.']);
        } else {
            $this->json_error('Failed to approve item.');
        }
    }

    /**
     * Reject a pending submission.
     */
    public function reject() {
        $this->require_csrf();
        $data = $this->get_json_input();
        
        $type = $data['type'] ?? '';
        $id = $data['id'] ?? 0;
        $farmId = $_SESSION['farm_id'];

        if (!$type || !$id) {
            $this->json_error('Invalid request parameters.');
        }

        $tableToModel = [
            'crop' => 'CropModel',
            'animal' => 'AnimalModel',
            'equipment' => 'EquipmentModel',
            'inventory' => 'InventoryModel'
        ];

        if ($type === 'report') {
            $model = new ReportModel();
            $success = $model->updateStatus($id, $farmId, 'rejected');
        } else {
            if (!isset($tableToModel[$type])) {
                $this->json_error('Invalid item type.');
            }
            $modelClass = $tableToModel[$type];
            $model = new $modelClass();
            $success = $model->updateApprovalStatus($id, $farmId, 'rejected');
        }

        if ($success) {
            $this->json_success(['message' => ucfirst($type) . ' rejected.']);
        } else {
            $this->json_error('Failed to reject item.');
        }
    }
}
