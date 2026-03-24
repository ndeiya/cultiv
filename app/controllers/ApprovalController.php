<?php
/**
 * Approval Controller
 * Handles owner/supervisor approval of worker submissions.
 */

class ApprovalController {

    public function __construct() {
        // No parent constructor call needed if not extending BaseController
    }

    /**
     * Show pending submissions from crops, animals, equipment, and inventory.
     */
    public function index() {
        role_gate(['owner', 'supervisor']);
        $user = current_user();
        $farmId = $user['farm_id'];
        
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
            'reports' => $reportModel->getPendingByFarm($farmId),
            'role' => current_user()['role']
        ];

        view('owner/approvals', $data);
    }

    /**
     * Approve a pending submission.
     */
    public function approve() {
        role_gate(['owner', 'supervisor']);
        require_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $type = $data['type'] ?? '';
        $id = $data['id'] ?? 0;
        $user = current_user();
        $farmId = $user['farm_id'];

        if (!$type || !$id) {
            return json_response(['success' => false, 'message' => 'Invalid request parameters.'], 400);
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
                return json_response(['success' => false, 'message' => 'Invalid item type.'], 400);
            }
            $modelClass = $tableToModel[$type];
            $model = new $modelClass();
            $success = $model->updateApprovalStatus($id, $farmId, 'approved');
        }

        if ($success) {
            AuditService::logAction('approve', $type, $id);
            return json_response(['success' => true, 'message' => ucfirst($type) . ' approved successfully.']);
        } else {
            return json_response(['success' => false, 'message' => 'Failed to approve item.'], 500);
        }
    }

    /**
     * Reject a pending submission.
     */
    public function reject() {
        role_gate(['owner', 'supervisor']);
        require_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $type = $data['type'] ?? '';
        $id = $data['id'] ?? 0;
        $user = current_user();
        $farmId = $user['farm_id'];

        if (!$type || !$id) {
            return json_response(['success' => false, 'message' => 'Invalid request parameters.'], 400);
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
                return json_response(['success' => false, 'message' => 'Invalid item type.'], 400);
            }
            $modelClass = $tableToModel[$type];
            $model = new $modelClass();
            $success = $model->updateApprovalStatus($id, $farmId, 'rejected');
        }

        if ($success) {
            AuditService::logAction('reject', $type, $id);
            return json_response(['success' => true, 'message' => ucfirst($type) . ' rejected.']);
        } else {
            return json_response(['success' => false, 'message' => 'Failed to reject item.'], 500);
        }
    }

    /**
     * Bulk Approve
     */
    public function bulkApprove() {
        role_gate(['owner', 'supervisor']);
        require_csrf();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $items = $input['items'] ?? [];

        if (empty($items)) {
            return json_response(['success' => false, 'message' => 'No items selected.'], 400);
        }

        $tableToModel = [
            'crop' => 'CropModel',
            'animal' => 'AnimalModel',
            'equipment' => 'EquipmentModel',
            'inventory' => 'InventoryModel'
        ];

        $user = current_user();
        $farmId = $user['farm_id'];
        $successCount = 0;
        foreach ($items as $item) {
            $type = $item['type'];
            $id = $item['id'];

            if ($type === 'report') {
                $model = new ReportModel();
                if ($model->updateStatus($id, $farmId, 'open')) {
                    $successCount++;
                    AuditService::logAction('approve', 'report', $id);
                }
            } elseif (isset($tableToModel[$type])) {
                $modelClass = $tableToModel[$type];
                $model = new $modelClass();
                if ($model->updateApprovalStatus($id, $farmId, 'approved')) {
                    $successCount++;
                    AuditService::logAction('approve', $type, $id);
                }
            }
        }

        return json_response([
            'success' => true, 
            'message' => "Successfully approved {$successCount} items."
        ]);
    }

    /**
     * Bulk Reject
     */
    public function bulkReject() {
        role_gate(['owner', 'supervisor']);
        require_csrf();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $items = $input['items'] ?? [];

        if (empty($items)) {
            return json_response(['success' => false, 'message' => 'No items selected.'], 400);
        }

        $tableToModel = [
            'crop' => 'CropModel',
            'animal' => 'AnimalModel',
            'equipment' => 'EquipmentModel',
            'inventory' => 'InventoryModel'
        ];

        $user = current_user();
        $farmId = $user['farm_id'];
        $successCount = 0;
        foreach ($items as $item) {
            $type = $item['type'];
            $id = $item['id'];

            if ($type === 'report') {
                $model = new ReportModel();
                if ($model->updateStatus($id, $farmId, 'rejected')) {
                    $successCount++;
                    AuditService::logAction('reject', 'report', $id);
                }
            } elseif (isset($tableToModel[$type])) {
                $modelClass = $tableToModel[$type];
                $model = new $modelClass();
                if ($model->updateApprovalStatus($id, $farmId, 'rejected')) {
                    $successCount++;
                    AuditService::logAction('reject', $type, $id);
                }
            }
        }

        return json_response([
            'success' => true, 
            'message' => "Successfully rejected {$successCount} items."
        ]);
    }
}
