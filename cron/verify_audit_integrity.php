<?php
/**
 * Audit Log Integrity Verification Cron Job
 * 
 * This script should be run daily via cron to verify the integrity of audit logs.
 * 
 * Usage (add to crontab):
 * 0 2 * * * /usr/bin/php /path/to/cultiv/cron/verify_audit_integrity.php >> /var/log/cultiv/audit_verification.log 2>&1
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/services/AuditService.php';

// Initialize database connection
$db = Database::getInstance();
$auditService = new AuditService();

// Get all tenants
$stmt = $db->query('SELECT id, name FROM tenants WHERE status = "active"');
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];
$totalErrors = 0;

foreach ($tenants as $tenant) {
    $result = $auditService->verifyIntegrity($tenant['id']);
    $results[$tenant['id']] = [
        'tenant_name' => $tenant['name'],
        'valid' => $result['valid'],
        'error_count' => count($result['errors']),
        'total_logs' => $result['total_logs']
    ];
    
    if (!$result['valid']) {
        $totalErrors += count($result['errors']);
        error_log(sprintf(
            "[AUDIT INTEGRITY FAILURE] Tenant %d (%s): %d errors found in %d logs",
            $tenant['id'],
            $tenant['name'],
            count($result['errors']),
            $result['total_logs']
        ));
        
        // Log detailed errors
        foreach ($result['errors'] as $error) {
            error_log(sprintf(
                "  - Log ID %d: %s",
                $error['log_id'],
                $error['message']
            ));
        }
    }
}

// Summary
echo sprintf(
    "[%s] Audit integrity verification completed. %d tenants checked, %d total errors found.\n",
    date('Y-m-d H:i:s'),
    count($tenants),
    $totalErrors
);

// Exit with error code if any issues found
exit($totalErrors > 0 ? 1 : 0);
