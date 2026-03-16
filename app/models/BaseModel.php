<?php
/**
 * Base Model
 * Provides multi-tenant isolation through automatic tenant_id scoping.
 * All models should extend this class to ensure data isolation.
 */

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $tenantColumn = 'tenant_id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get the current tenant ID from session.
     * Throws exception if not available (should not happen in authenticated context).
     */
    protected function getCurrentTenantId(): int
    {
        $user = current_user();
        if (!$user || !isset($user['tenant_id'])) {
            throw new RuntimeException('Tenant ID not available in session. User must be authenticated.');
        }
        return (int) $user['tenant_id'];
    }

    /**
     * Create a scoped query that automatically includes tenant_id filter.
     * This ensures all queries are automatically scoped to the current tenant.
     *
     * @param string $sql SQL query with :tenant_id placeholder
     * @param array $params Parameters array (tenant_id will be automatically added)
     * @return PDOStatement
     */
    protected function scopedQuery(string $sql, array $params = []): PDOStatement
    {
        // Automatically inject tenant_id into all queries
        $params['tenant_id'] = $this->getCurrentTenantId();
        
        // If SQL doesn't already have tenant_id condition, add it
        // This is a safety measure - queries should explicitly include tenant_id
        if (stripos($sql, 'tenant_id') === false && stripos($sql, 'INSERT') === false) {
            // For SELECT/UPDATE/DELETE queries, we should warn but still add it
            // In production, all queries should explicitly include tenant_id
            $wherePos = stripos($sql, 'WHERE');
            if ($wherePos !== false) {
                // If WHERE exists, insert tenant_id check right after it
                $sql = substr_replace($sql, ' ' . $this->tenantColumn . ' = :tenant_id AND ', $wherePos + 6, 0);
            } else {
                // No WHERE clause - this is unusual, but we'll add one
                $sql .= ' WHERE ' . $this->tenantColumn . ' = :tenant_id';
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a raw query (use with caution - no automatic tenant scoping).
     * Only use this for queries that don't need tenant scoping (e.g., tenant table itself).
     */
    protected function rawQuery(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Find a record by ID, automatically scoped to current tenant.
     * Optionally scoped to a specific farm.
     */
    public function findById(int $id, ?int $farmId = null): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND {$this->tenantColumn} = :tenant_id";
        $params = ['id' => $id];

        if ($farmId !== null) {
            $sql .= " AND farm_id = :farm_id";
            $params['farm_id'] = $farmId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->scopedQuery($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all records for the current tenant.
     */
    public function getAll(): array
    {
        $stmt = $this->scopedQuery("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a record by ID, automatically scoped to current tenant.
     * Optionally scoped to a specific farm.
     */
    public function delete(int $id, ?int $farmId = null): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND {$this->tenantColumn} = :tenant_id";
        $params = ['id' => $id];

        if ($farmId !== null) {
            $sql .= " AND farm_id = :farm_id";
            $params['farm_id'] = $farmId;
        }

        $stmt = $this->scopedQuery($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update approval status of a record.
     */
    public function updateApprovalStatus(int $id, int $farmId, string $status): bool
    {
        $statusColumn = ($this->table === 'reports') ? 'status' : 'approval_status';
        
        $sql = "UPDATE {$this->table} SET {$statusColumn} = :status WHERE id = :id AND farm_id = :farm_id AND {$this->tenantColumn} = :tenant_id";
        $params = [
            'id' => $id,
            'farm_id' => $farmId,
            'status' => $status
        ];

        $stmt = $this->scopedQuery($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all pending records for a farm.
     */
    public function getPendingByFarm(int $farmId): array
    {
        $statusColumn = ($this->table === 'reports') ? 'status' : 'approval_status';
        
        $sql = "SELECT * FROM {$this->table} WHERE farm_id = :farm_id AND {$statusColumn} = 'pending' AND {$this->tenantColumn} = :tenant_id ORDER BY id DESC";
        $stmt = $this->scopedQuery($sql, ['farm_id' => $farmId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
