<?php
/**
 * Base Controller
 * Provides common functionality for all controllers, including tenant isolation checks.
 */

abstract class BaseController
{
    /**
     * Get the current tenant ID from session.
     */
    protected function getCurrentTenantId(): int
    {
        $user = current_user();
        if (!$user || !isset($user['tenant_id'])) {
            if (is_api_request()) {
                json_response(['error' => true, 'message' => 'Tenant ID not available'], 401);
            }
            redirect('/login');
        }
        return (int) $user['tenant_id'];
    }

    /**
     * Assert that a resource belongs to the current tenant.
     * This prevents cross-tenant data access.
     *
     * @param string $table Table name
     * @param int $resourceId Resource ID to check
     * @param string $idColumn Column name for the ID (default: 'id')
     * @return array The resource data if it belongs to the tenant
     * @throws RuntimeException If resource doesn't exist or doesn't belong to tenant
     */
    protected function assertTenantOwns(string $table, int $resourceId, string $idColumn = 'id'): array
    {
        $tenantId = $this->getCurrentTenantId();
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT * FROM {$table} 
            WHERE {$idColumn} = :id AND tenant_id = :tenant_id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $resourceId, 'tenant_id' => $tenantId]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resource) {
            if (is_api_request()) {
                json_response([
                    'error' => true, 
                    'message' => 'Resource not found or access denied'
                ], 404);
            }
            http_response_code(404);
            die('Resource not found or access denied.');
        }

        return $resource;
    }

    /**
     * Assert that a resource belongs to the current tenant and farm.
     * Useful for resources that have both tenant_id and farm_id.
     *
     * @param string $table Table name
     * @param int $resourceId Resource ID to check
     * @param int $farmId Farm ID to check
     * @return array The resource data if it belongs to the tenant and farm
     */
    protected function assertTenantAndFarmOwns(string $table, int $resourceId, int $farmId): array
    {
        $tenantId = $this->getCurrentTenantId();
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT * FROM {$table} 
            WHERE id = :id AND tenant_id = :tenant_id AND farm_id = :farm_id 
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $resourceId, 
            'tenant_id' => $tenantId,
            'farm_id' => $farmId
        ]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resource) {
            if (is_api_request()) {
                json_response([
                    'error' => true, 
                    'message' => 'Resource not found or access denied'
                ], 404);
            }
            http_response_code(404);
            die('Resource not found or access denied.');
        }

        return $resource;
    }
}
