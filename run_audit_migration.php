<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $migrationFile = __DIR__ . '/migrations/029_add_importance_to_audit_logs.sql';
    if (!file_exists($migrationFile)) {
        die("Migration file not found: $migrationFile\n");
    }
    
    $sql = file_get_contents($migrationFile);
    // Split by semicolon just in case
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        try {
            $db->exec($stmt);
            echo "Executed: " . substr($stmt, 0, 50) . "...\n";
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate column name') || str_contains($e->getMessage(), 'already exists')) {
                echo "Skipped: Column/Index already exists.\n";
            } else {
                throw $e;
            }
        }
    }
    echo "Migration 029 completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
