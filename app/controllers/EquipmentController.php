<?php
/**
 * Equipment Controller
 * Handles web and API requests for equipment management.
 */

class EquipmentController
{
    private EquipmentModel $equipmentModel;

    public function __construct()
    {
        $this->equipmentModel = new EquipmentModel();
    }

    /**
     * Web: List all equipment for the farm
     */
    public function index(): void
    {
        role_gate(['owner', 'supervisor', 'worker', 'accountant']);
        $user = current_user();

        $equipment = $this->equipmentModel->getAllByFarm($user['farm_id']);

        view('shared/equipment', [
            'equipment' => $equipment,
            'title' => 'Equipment Management'
        ]);
    }

    /**
     * Web: Store new equipment
     */
    public function store(): void
    {
        role_gate(['owner', 'supervisor', 'worker']);
        require_csrf();
 
        $user = current_user();
        $data = sanitize_array($_POST);
 
        $missing = validate_required(['name', 'status'], $data);
        if ($missing) {
            flash('error', 'Please fill in all required fields.');
            redirect('/' . $user['role'] . '/equipment');
            return;
        }
 
        $data['farm_id'] = $user['farm_id'];
        $data['approval_status'] = ($user['role'] === 'worker') ? 'pending' : 'approved';
 
        $id = $this->equipmentModel->create($data);
        AuditService::logAction('create', 'equipment', $id);
 
        if ($user['role'] === 'worker') {
            flash('success', 'Entry sent for approval.');
        } else {
            flash('success', 'Equipment added successfully.');
        }
        redirect('/' . $user['role'] . '/equipment');
    }

    /**
     * Web: Update equipment
     */
    public function update(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $data = sanitize_array($_POST);
        $id = (int)($data['id'] ?? 0);

        $item = $this->equipmentModel->findById($id, $user['farm_id']);
        if (!$item) {
            flash('error', 'Equipment not found.');
            redirect('/' . $user['role'] . '/equipment');
            return;
        }

        $this->equipmentModel->update($id, $user['farm_id'], $data);
        AuditService::logAction('update', 'equipment', $id);

        flash('success', 'Equipment updated.');
        redirect('/' . $user['role'] . '/equipment');
    }

    /**
     * Web: Delete equipment
     */
    public function delete(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);

        if ($this->equipmentModel->delete($id, $user['farm_id'])) {
            AuditService::logAction('delete', 'equipment', $id);
            flash('success', 'Equipment deleted.');
        } else {
            flash('error', 'Failed to delete equipment.');
        }

        redirect('/' . $user['role'] . '/equipment');
    }

    /**
     * Web: Bulk Delete equipment
     */
    public function bulkDelete(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            json_response(['success' => false, 'message' => 'No items selected.'], 400);
            return;
        }

        $successCount = 0;
        foreach ($ids as $id) {
            if ($this->equipmentModel->delete((int)$id, $user['farm_id'])) {
                $successCount++;
                AuditService::logAction('delete', 'equipment', $id);
            }
        }

        json_response([
            'success' => true, 
            'message' => "Successfully deleted {$successCount} equipment items."
        ]);
    }

    /**
     * API: List all equipment
     */
    public function apiIndex(): void
    {
        require_auth();
        $user = current_user();
        $equipment = $this->equipmentModel->getAllByFarm($user['farm_id']);
        json_response($equipment);
    }
}
