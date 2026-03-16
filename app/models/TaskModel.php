<?php
/**
 * Task Model
 * Handles database operations for the Task Assignment module.
 */

require_once __DIR__ . '/BaseModel.php';

class TaskModel extends BaseModel {
    protected string $table = 'tasks';

    /**
     * Get all tasks for a farm, optionally filtered by user or status
     */
    public function getFilteredTasks(int $farmId, array $filters = []): array {
        $sql = "SELECT t.*, u.name as assigned_to_name, creator.name as created_by_name 
                FROM {$this->table} t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN users creator ON t.created_by = creator.id
                WHERE t.farm_id = ? AND t.tenant_id = ?";
        
        $params = [$farmId, $this->getCurrentTenantId()];

        if (isset($filters['assigned_to'])) {
            $sql .= " AND t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (isset($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['due_date'])) {
            $sql .= " AND t.due_date = ?";
            $params[] = $filters['due_date'];
        }

        $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tasks assigned to a specific worker
     */
    public function getTasksByWorker(int $userId): array {
        $sql = "SELECT t.*, creator.name as created_by_name 
                FROM {$this->table} t
                LEFT JOIN users creator ON t.created_by = creator.id
                WHERE t.assigned_to = ? AND t.tenant_id = ?
                ORDER BY t.due_date ASC, t.priority DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $this->getCurrentTenantId()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a single task by ID
     */
    public function findById(int $id, ?int $farmId = null): ?array {
        $sql = "SELECT t.*, u.name as assigned_to_name, creator.name as created_by_name 
                FROM {$this->table} t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN users creator ON t.created_by = creator.id
                WHERE t.id = ? AND t.tenant_id = ?";
        
        $params = [$id, $this->getCurrentTenantId()];
        if ($farmId !== null) {
            $sql .= " AND t.farm_id = ?";
            $params[] = $farmId;
        }
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        return $task ?: null;
    }

    /**
     * Create a new task
     */
    public function create(array $data): int {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing task
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }
        $params[] = $id;
        $params[] = $this->getCurrentTenantId();

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ? AND tenant_id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    /**
     * Update task status
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ? AND tenant_id = ?";
        return $this->db->prepare($sql)->execute([$status, $id, $this->getCurrentTenantId()]);
    }

    /**
     * Delete a task
     */
    public function delete(int $id, ?int $farmId = null): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND tenant_id = ?";
        $params = [$id, $this->getCurrentTenantId()];
        if ($farmId !== null) {
            $sql .= " AND farm_id = ?";
            $params[] = $farmId;
        }
        return $this->db->prepare($sql)->execute($params);
    }
}
