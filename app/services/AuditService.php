<?php
/**
 * Audit Service
 * Handles logging of system actions (CRUD operations) with hash-chain integrity.
 */

class AuditService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log an action to the audit_logs table with hash-chain integrity.
     * Each log entry includes a hash of the row data plus the previous entry's hash.
     */
    public function log(int $userId, string $action, string $entity, ?int $entityId = null): void
    {
        $tenantId = current_user()['tenant_id'] ?? 1;
        
        // Get the previous hash (from the most recent log entry for this tenant)
        $previousHash = $this->getPreviousHash($tenantId);
        
        // Prepare row data for hashing (exclude hash fields themselves)
        $rowData = [
            'tenant_id' => $tenantId,
            'user_id'   => $userId,
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Create hash of row data + previous hash
        $rowHash = $this->computeRowHash($rowData, $previousHash);
        
        $stmt = $this->db->prepare('
            INSERT INTO audit_logs (tenant_id, user_id, action, entity, entity_id, previous_hash, row_hash, created_at)
            VALUES (:tenant_id, :user_id, :action, :entity, :entity_id, :previous_hash, :row_hash, NOW())
        ');
        $stmt->execute([
            'tenant_id'    => $tenantId,
            'user_id'      => $userId,
            'action'       => $action,
            'entity'       => $entity,
            'entity_id'    => $entityId,
            'previous_hash' => $previousHash,
            'row_hash'     => $rowHash
        ]);
    }

    /**
     * Get the hash of the most recent audit log entry for the tenant.
     */
    private function getPreviousHash(int $tenantId): ?string
    {
        $stmt = $this->db->prepare('
            SELECT row_hash FROM audit_logs 
            WHERE tenant_id = :tenant_id 
            ORDER BY id DESC 
            LIMIT 1
        ');
        $stmt->execute(['tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['row_hash'] ?? null;
    }

    /**
     * Compute SHA-256 hash of row data concatenated with previous hash.
     */
    private function computeRowHash(array $rowData, ?string $previousHash): string
    {
        // Serialize row data in a consistent order
        $dataString = sprintf(
            '%d|%d|%s|%s|%d|%s|%s',
            $rowData['tenant_id'],
            $rowData['user_id'] ?? 0,
            $rowData['action'],
            $rowData['entity'],
            $rowData['entity_id'] ?? 0,
            $rowData['created_at'],
            $previousHash ?? ''
        );
        
        return hash('sha256', $dataString);
    }

    /**
     * Verify the integrity of audit logs for a tenant.
     * Returns array with 'valid' boolean and 'errors' array.
     */
    public function verifyIntegrity(int $tenantId): array
    {
        $errors = [];
        $stmt = $this->db->prepare('
            SELECT id, tenant_id, user_id, action, entity, entity_id, previous_hash, row_hash, created_at
            FROM audit_logs 
            WHERE tenant_id = :tenant_id 
            ORDER BY id ASC
        ');
        $stmt->execute(['tenant_id' => $tenantId]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $previousHash = null;
        foreach ($logs as $index => $log) {
            // Recompute hash
            $rowData = [
                'tenant_id' => $log['tenant_id'],
                'user_id'   => $log['user_id'],
                'action'    => $log['action'],
                'entity'    => $log['entity'],
                'entity_id' => $log['entity_id'],
                'created_at' => $log['created_at']
            ];
            $expectedHash = $this->computeRowHash($rowData, $previousHash);
            
            // Check if hash matches
            if ($log['row_hash'] !== $expectedHash) {
                $errors[] = [
                    'log_id' => $log['id'],
                    'expected_hash' => $expectedHash,
                    'actual_hash' => $log['row_hash'],
                    'message' => 'Hash mismatch detected'
                ];
            }
            
            // Check if previous_hash matches
            if ($index > 0 && $log['previous_hash'] !== $previousHash) {
                $errors[] = [
                    'log_id' => $log['id'],
                    'expected_previous_hash' => $previousHash,
                    'actual_previous_hash' => $log['previous_hash'],
                    'message' => 'Previous hash mismatch detected'
                ];
            }
            
            $previousHash = $log['row_hash'];
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'total_logs' => count($logs)
        ];
    }

    /**
     * Helper to log an action for the currently logged-in user.
     */
    public static function logAction(string $action, string $entity, ?int $entityId = null): void
    {
        $user = current_user();
        if (!$user) return;

        $instance = new self();
        $instance->log($user['id'], $action, $entity, $entityId);
    }
}
