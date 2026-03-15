<?php
/**
 * Equipment Model
 * Database operations for the equipment table.
 */

class EquipmentModel extends BaseModel
{
    protected string $table = 'equipment';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all equipment for a specific farm (scoped to current tenant).
     */
    public function getAllByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM equipment WHERE farm_id = :farm_id AND tenant_id = :tenant_id ORDER BY next_maintenance ASC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find equipment by ID (scoped to current tenant and farm).
     */
    public function findById(int $id, ?int $farmId = null): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM equipment WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id, 'farm_id' => $farmId]
        );
        $item = $stmt->fetch();
        return $item ?: null;
    }

    /**
     * Create a new equipment record (automatically includes tenant_id).
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO equipment (tenant_id, farm_id, name, status, last_maintenance, next_maintenance)
            VALUES (:tenant_id, :farm_id, :name, :status, :last_maintenance, :next_maintenance)
        ');
        $stmt->execute([
            'tenant_id'       => $tenantId,
            'farm_id'         => $data['farm_id'],
            'name'            => $data['name'],
            'status'          => $data['status'] ?? 'working',
            'last_maintenance'=> $data['last_maintenance'] ?? null,
            'next_maintenance'=> $data['next_maintenance'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing equipment record (scoped to current tenant).
     */
    public function update(int $id, int $farmId, array $data): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE equipment 
            SET name = :name, status = :status, 
                last_maintenance = :last_maintenance, next_maintenance = :next_maintenance
            WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id
        ', [
            'id'              => $id,
            'farm_id'         => $farmId,
            'name'            => $data['name'],
            'status'          => $data['status'],
            'last_maintenance'=> $data['last_maintenance'] ?? null,
            'next_maintenance'=> $data['next_maintenance'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an equipment record (scoped to current tenant).
     */
    public function delete(int $id, ?int $farmId = null): bool
    {
        $stmt = $this->scopedQuery(
            'DELETE FROM equipment WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId]
        );
        return $stmt->rowCount() > 0;
    }
}
