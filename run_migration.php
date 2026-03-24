<?php
require_once __DIR__ . '/app/bootstrap.php';

try {
    $db = Database::getInstance();
    $migrationPath = __DIR__ . '/migrations/029_add_importance_to_audit_logs.sql';
    if (!file_exists($migrationPath)) {
        throw new Exception("Migration file not found at: $migrationPath");
    }
    $sql = file_get_contents($migrationPath);
    $db->exec($sql);
    echo "Migration successful\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
