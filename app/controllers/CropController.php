<?php
/**
 * Crop Controller
 * Handles web and API requests for crop management.
 */

class CropController
{
    private CropModel $cropModel;

    public function __construct()
    {
        $this->cropModel = new CropModel();
    }

    /**
     * Web: List all crops for the farm
     */
    public function index(): void
    {
        role_gate(['owner', 'supervisor', 'worker']);
        $user = current_user();

        $crops = $this->cropModel->getAllByFarm($user['farm_id']);

        // Phase 5.2: Cost-per-Harvest Analytics
        foreach ($crops as &$crop) {
            $crop['total_cost'] = $this->cropModel->calculateCostPerHarvest($crop['id']);
        }

        view('shared/crops', [
            'crops' => $crops,
            'title' => 'Crops Management'
        ]);
    }

    /**
     * Web: Store a new crop
     */
    public function store(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $data = sanitize_array($_POST);

        $missing = validate_required(['name', 'growth_stage', 'health_status'], $data);
        if ($missing) {
            flash('error', 'Please fill in all required fields: ' . implode(', ', $missing));
            redirect('/' . $user['role'] . '/crops');
            return;
        }

        $data['farm_id'] = $user['farm_id'];
        $data['updated_by'] = $user['id'];

        $id = $this->cropModel->create($data);
        AuditService::logAction('create', 'crop', $id);

        flash('success', 'Crop added successfully.');
        redirect('/' . $user['role'] . '/crops');
    }

    /**
     * Web: Update an existing crop
     */
    public function update(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $data = sanitize_array($_POST);
        $id = (int)($data['id'] ?? 0);

        $crop = $this->cropModel->findById($id, $user['farm_id']);
        if (!$crop) {
            flash('error', 'Crop not found.');
            redirect('/' . $user['role'] . '/crops');
            return;
        }

        $missing = validate_required(['name', 'growth_stage', 'health_status'], $data);
        if ($missing) {
            flash('error', 'Please fill in all required fields: ' . implode(', ', $missing));
            redirect('/' . $user['role'] . '/crops');
            return;
        }

        $data['updated_by'] = $user['id'];
        $this->cropModel->update($id, $user['farm_id'], $data);
        AuditService::logAction('update', 'crop', $id);

        flash('success', 'Crop updated successfully.');
        redirect('/' . $user['role'] . '/crops');
    }

    /**
     * Web: Delete a crop
     */
    public function delete(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);

        if ($this->cropModel->delete($id, $user['farm_id'])) {
            AuditService::logAction('delete', 'crop', $id);
            flash('success', 'Crop deleted successfully.');
        } else {
            flash('error', 'Failed to delete crop.');
        }

        redirect('/' . $user['role'] . '/crops');
    }

    /**
     * API: List all crops
     */
    public function apiIndex(): void
    {
        require_auth();
        $user = current_user();
        $crops = $this->cropModel->getAllByFarm($user['farm_id']);
        json_response($crops);
    }

    /**
     * API: Create a crop
     */
    public function apiStore(): void
    {
        require_auth();
        role_gate(['owner', 'supervisor']);
        
        $user = current_user();
        $data = sanitize_array(get_json_body());

        $missing = validate_required(['name', 'growth_stage', 'health_status'], $data);
        if ($missing) {
            json_response(['error' => true, 'message' => 'Missing fields: ' . implode(', ', $missing)], 400);
        }

        $data['farm_id'] = $user['farm_id'];
        $data['updated_by'] = $user['id'];

        $id = $this->cropModel->create($data);
        json_response(['success' => true, 'id' => $id, 'message' => 'Crop created successfully']);
    }
}
