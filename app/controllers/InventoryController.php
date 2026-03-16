<?php
/**
 * Inventory Controller
 * Handles web and API requests for inventory management.
 */

class InventoryController
{
    private InventoryModel $inventoryModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
    }

    /**
     * Web: List all inventory items
     */
    public function index(): void
    {
        role_gate(['owner', 'supervisor', 'worker', 'accountant']);
        $user = current_user();

        $inventory = $this->inventoryModel->getAllByFarm($user['farm_id']);

        view('shared/inventory', [
            'inventory' => $inventory,
            'title' => 'Inventory Management'
        ]);
    }

    /**
     * Web: Store new item
     */
    public function store(): void
    {
        role_gate(['owner', 'supervisor', 'worker']);
        require_csrf();
 
        $user = current_user();
        $data = sanitize_array($_POST);
 
        $missing = validate_required(['item_name', 'quantity', 'unit'], $data);
        if ($missing) {
            flash('error', 'Please fill in all required fields.');
            redirect('/' . $user['role'] . '/inventory');
            return;
        }
 
        $data['farm_id'] = $user['farm_id'];
        $data['approval_status'] = ($user['role'] === 'worker') ? 'pending' : 'approved';
 
        $id = $this->inventoryModel->create($data);
        AuditService::logAction('create', 'inventory', $id);
 
        if ($user['role'] === 'worker') {
            flash('success', 'Item submitted for approval.');
        } else {
            flash('success', 'Item added to inventory.');
        }
        redirect('/' . $user['role'] . '/inventory');
    }

    /**
     * Web: Update quantity from quick-update
     */
    public function updateQuantity(): void
    {
        role_gate(['owner', 'supervisor', 'worker']);
        require_csrf();

        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 0);

        if ($this->inventoryModel->updateQuantity($id, $user['farm_id'], $quantity)) {
            AuditService::logAction('update_quantity', 'inventory', $id);
            flash('success', 'Quantity updated.');
        } else {
            flash('error', 'Failed to update quantity.');
        }

        redirect('/' . $user['role'] . '/inventory');
    }

    /**
     * Web: Update item details
     */
    public function update(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $data = sanitize_array($_POST);
        $id = (int)($data['id'] ?? 0);

        $item = $this->inventoryModel->findById($id, $user['farm_id']);
        if (!$item) {
            flash('error', 'Item not found.');
            redirect('/' . $user['role'] . '/inventory');
            return;
        }

        $this->inventoryModel->update($id, $user['farm_id'], $data);
        AuditService::logAction('update', 'inventory', $id);

        flash('success', 'Inventory item updated.');
        redirect('/' . $user['role'] . '/inventory');
    }

    /**
     * Web: Delete item
     */
    public function delete(): void
    {
        role_gate(['owner', 'supervisor']);
        require_csrf();

        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);

        if ($this->inventoryModel->delete($id, $user['farm_id'])) {
            AuditService::logAction('delete', 'inventory', $id);
            flash('success', 'Item removed from inventory.');
        } else {
            flash('error', 'Failed to remove item.');
        }

        redirect('/' . $user['role'] . '/inventory');
    }

    /**
     * API: List inventory
     */
    public function apiIndex(): void
    {
        require_auth();
        $user = current_user();
        $inventory = $this->inventoryModel->getAllByFarm($user['farm_id']);
        json_response($inventory);
    }

    /**
     * API: Update quantity
     */
    public function apiUpdateQuantity(): void
    {
        require_auth();
        $user = current_user();
        $data = get_json_body();
        $id = (int)($data['id'] ?? 0);
        $quantity = (float)($data['quantity'] ?? 0);

        if ($this->inventoryModel->updateQuantity($id, $user['farm_id'], $quantity)) {
            AuditService::logAction('api_update_quantity', 'inventory', $id);
            json_response(['success' => true, 'message' => 'Quantity updated']);
        } else {
            json_response(['error' => true, 'message' => 'Failed to update'], 400);
        }
    }
}
