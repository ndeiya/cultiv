<?php
/**
 * Crop Model
 * Database operations for the crops table.
 */

class CropModel extends BaseModel
{
    protected string $table = 'crops';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all crops for a specific farm (scoped to current tenant).
     */
    public function getAllByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM crops WHERE farm_id = :farm_id AND tenant_id = :tenant_id ORDER BY planting_date DESC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a crop by ID (scoped to current tenant and farm).
     */
    public function findById(int $id, ?int $farmId = null): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM crops WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id, 'farm_id' => $farmId]
        );
        $crop = $stmt->fetch();
        return $crop ?: null;
    }

    /**
     * Create a new crop record (automatically includes tenant_id).
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO crops (tenant_id, farm_id, name, field_name, planting_date, expected_harvest, growth_stage, health_status, approval_status, updated_by, updated_at)
            VALUES (:tenant_id, :farm_id, :name, :field_name, :planting_date, :expected_harvest, :growth_stage, :health_status, :approval_status, :updated_by, NOW())
        ');
        $stmt->execute([
            'tenant_id'        => $tenantId,
            'farm_id'          => $data['farm_id'],
            'name'             => $data['name'],
            'field_name'       => $data['field_name'] ?? null,
            'planting_date'    => $data['planting_date'] ?? null,
            'expected_harvest' => $data['expected_harvest'] ?? null,
            'growth_stage'     => $data['growth_stage'] ?? 'Planting',
            'health_status'    => $data['health_status'] ?? 'good',
            'approval_status'  => $data['approval_status'] ?? 'approved',
            'updated_by'       => $data['updated_by']
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing crop record (scoped to current tenant).
     */
    public function update(int $id, int $farmId, array $data): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE crops 
            SET name = :name, field_name = :field_name, planting_date = :planting_date, 
                expected_harvest = :expected_harvest, growth_stage = :growth_stage, 
                health_status = :health_status, updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id
        ', [
            'id' => $id,
            'farm_id' => $farmId,
            'name' => $data['name'],
            'field_name' => $data['field_name'] ?? null,
            'planting_date' => $data['planting_date'] ?? null,
            'expected_harvest' => $data['expected_harvest'] ?? null,
            'growth_stage' => $data['growth_stage'],
            'health_status' => $data['health_status'],
            'updated_by' => $data['updated_by']
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a crop record (scoped to current tenant).
     */
    public function delete(int $id, ?int $farmId = null): bool
    {
        $stmt = $this->scopedQuery(
            'DELETE FROM crops WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Phase 5.2: Analytics
     * Calculate total expenses linked to this crop.
     */
    public function calculateCostPerHarvest(int $cropId): float
    {
        $stmt = $this->scopedQuery(
            'SELECT SUM(amount) FROM farm_expenses WHERE crop_id = :crop_id AND tenant_id = :tenant_id',
            ['crop_id' => $cropId]
        );
        return (float) $stmt->fetchColumn() ?: 0.0;
    }

    /**
     * Get all pending items for a specific farm.
     */
    public function getPendingByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT r.*, u.name as updated_by_name 
             FROM ' . $this->table . ' r 
             LEFT JOIN users u ON r.updated_by = u.id 
             WHERE r.farm_id = :farm_id AND r.tenant_id = :tenant_id AND r.approval_status = \'pending\' 
             ORDER BY r.updated_at DESC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update approval status (scoped to tenant and farm).
     */
    public function updateApprovalStatus(int $id, int $farmId, string $status): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE ' . $this->table . ' SET approval_status = :status, updated_at = NOW() WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId, 'status' => $status]
        );
        return $stmt->rowCount() > 0;
    }
}
