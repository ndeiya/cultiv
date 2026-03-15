<?php
/**
 * Animal Controller
 * Handles web and API requests for livestock management.
 */

class AnimalController
{
    private AnimalModel $animalModel;

    public function __construct()
    {
        $this->animalModel = new AnimalModel();
    }

    /**
     * Web: List all animals for the farm
     */
    public function index(): void
    {
        role_gate(['owner', 'supervisor', 'worker', 'accountant']);
        $user = current_user();

        $animals = $this->animalModel->getAllByFarm($user['farm_id']);

        view('shared/animals', [
            'animals' => $animals,
            'title' => 'Livestock Management'
        ]);
    }

    /**
     * Web: Store a new animal
     */
    public function store(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $data = sanitize_array($_POST);

        $missing = validate_required(['tag_number', 'species', 'health_status'], $data);
        if ($missing) {
            flash('error', 'Please fill in all required fields: ' . implode(', ', $missing));
            redirect('/' . $user['role'] . '/animals');
            return;
        }

        $data['farm_id'] = $user['farm_id'];

        $id = $this->animalModel->create($data);
        AuditService::logAction('create', 'animal', $id);

        flash('success', 'Animal added successfully.');
        redirect('/' . $user['role'] . '/animals');
    }

    /**
     * Web: Update an existing animal
     */
    public function update(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $data = sanitize_array($_POST);
        $id = (int)($data['id'] ?? 0);

        $animal = $this->animalModel->findById($id, $user['farm_id']);
        if (!$animal) {
            flash('error', 'Animal not found.');
            redirect('/' . $user['role'] . '/animals');
            return;
        }

        $missing = validate_required(['tag_number', 'species', 'health_status'], $data);
        if ($missing) {
            flash('error', 'Please fill in all required fields: ' . implode(', ', $missing));
            redirect('/' . $user['role'] . '/animals');
            return;
        }

        $this->animalModel->update($id, $user['farm_id'], $data);
        AuditService::logAction('update', 'animal', $id);

        flash('success', 'Animal updated successfully.');
        redirect('/animals');
    }

    /**
     * Web: Delete an animal record
     */
    public function delete(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);

        if ($this->animalModel->delete($id, $user['farm_id'])) {
            AuditService::logAction('delete', 'animal', $id);
            flash('success', 'Animal record deleted.');
        } else {
            flash('error', 'Failed to delete record.');
        }

        redirect('/' . $user['role'] . '/animals');
    }

    /**
     * API: List all animals
     */
    public function apiIndex(): void
    {
        require_auth();
        $user = current_user();
        $animals = $this->animalModel->getAllByFarm($user['farm_id']);
        json_response($animals);
    }
}
