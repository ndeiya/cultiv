<?php
/**
 * Inventory Model
 * Database operations for the inventory table.
 */

class InventoryModel extends BaseModel
{
    protected string $table = 'inventory';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all inventory items for a specific farm (scoped to current tenant).
     */
    public function getAllByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM inventory WHERE farm_id = :farm_id AND tenant_id = :tenant_id ORDER BY item_name ASC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find an inventory item by ID (scoped to current tenant and farm).
     */
    public function findById(int $id, ?int $farmId = null): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM inventory WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id, 'farm_id' => $farmId]
        );
        $item = $stmt->fetch();
        return $item ?: null;
    }

    /**
     * Update item quantity (scoped to current tenant).
     */
    public function updateQuantity(int $id, int $farmId, float $quantity): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE inventory SET quantity = :quantity, updated_at = NOW() WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId, 'quantity' => $quantity]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Create a new inventory item (automatically includes tenant_id).
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO inventory (tenant_id, farm_id, item_name, quantity, unit, storage_location, updated_at)
            VALUES (:tenant_id, :farm_id, :item_name, :quantity, :unit, :storage_location, NOW())
        ');
        $stmt->execute([
            'tenant_id'        => $tenantId,
            'farm_id'          => $data['farm_id'],
            'item_name'        => $data['item_name'],
            'quantity'         => $data['quantity'] ?? 0,
            'unit'             => $data['unit'],
            'storage_location' => $data['storage_location'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an inventory item (scoped to current tenant).
     */
    public function update(int $id, int $farmId, array $data): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE inventory 
            SET item_name = :item_name, quantity = :quantity, unit = :unit, 
                storage_location = :storage_location, updated_at = NOW()
            WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id
        ', [
            'id'               => $id,
            'farm_id'          => $farmId,
            'item_name'        => $data['item_name'],
            'quantity'         => $data['quantity'],
            'unit'             => $data['unit'],
            'storage_location' => $data['storage_location'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an inventory item (scoped to current tenant).
     */
    public function delete(int $id, ?int $farmId = null): bool
    {
        $stmt = $this->scopedQuery(
            'DELETE FROM inventory WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId]
        );
        return $stmt->rowCount() > 0;
    }
}
