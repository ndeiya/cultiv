<?php
/**
 * Animal Model
 * Database operations for the animals table.
 */

class AnimalModel extends BaseModel
{
    protected string $table = 'animals';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all animals for a specific farm (scoped to current tenant).
     */
    public function getAllByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM animals WHERE farm_id = :farm_id AND tenant_id = :tenant_id ORDER BY created_at DESC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find an animal by ID (scoped to current tenant and farm).
     */
    public function findById(int $id, ?int $farmId = null): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM animals WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id, 'farm_id' => $farmId]
        );
        $animal = $stmt->fetch();
        return $animal ?: null;
    }

    /**
     * Create a new animal record (automatically includes tenant_id).
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO animals (tenant_id, farm_id, tag_number, species, breed, date_of_birth, health_status, weight, vaccination_due, approval_status, created_at, updated_at)
            VALUES (:tenant_id, :farm_id, :tag_number, :species, :breed, :date_of_birth, :health_status, :weight, :vaccination_due, :approval_status, NOW(), NOW())
        ');
        $stmt->execute([
            'tenant_id'       => $tenantId,
            'farm_id'         => $data['farm_id'],
            'tag_number'      => $data['tag_number'],
            'species'         => $data['species'],
            'breed'           => $data['breed'] ?? null,
            'date_of_birth'   => $data['date_of_birth'] ?? null,
            'health_status'   => $data['health_status'] ?? 'good',
            'weight'          => $data['weight'] ?? null,
            'vaccination_due' => $data['vaccination_due'] ?? null,
            'approval_status' => $data['approval_status'] ?? 'approved'
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing animal record (scoped to current tenant).
     */
    public function update(int $id, int $farmId, array $data): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE animals 
            SET tag_number = :tag_number, species = :species, breed = :breed, 
                date_of_birth = :date_of_birth,
                health_status = :health_status, weight = :weight, 
                vaccination_due = :vaccination_due, updated_at = NOW()
            WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id
        ', [
            'id'              => $id,
            'farm_id'         => $farmId,
            'tag_number'      => $data['tag_number'],
            'species'         => $data['species'],
            'breed'           => $data['breed'] ?? null,
            'date_of_birth'   => $data['date_of_birth'] ?? null,
            'health_status'   => $data['health_status'],
            'weight'          => $data['weight'] ?? null,
            'vaccination_due' => $data['vaccination_due'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an animal record (scoped to current tenant).
     */
    public function delete(int $id, ?int $farmId = null): bool
    {
        $stmt = $this->scopedQuery(
            'DELETE FROM animals WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all pending items for a specific farm.
     */
    public function getPendingByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM animals WHERE farm_id = :farm_id AND tenant_id = :tenant_id AND approval_status = \'pending\' ORDER BY created_at DESC',
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
            'UPDATE animals SET approval_status = :status, updated_at = NOW() WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId, 'status' => $status]
        );
        return $stmt->rowCount() > 0;
    }
}
