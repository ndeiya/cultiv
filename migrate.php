<?php
/**
 * Database Migration Runner
 * Executes all SQL migration files in order, then optionally seeds data.
 * 
 * Usage:
 *   php migrate.php          — Run migrations only
 *   php migrate.php --seed   — Run migrations + seed data
 *   php migrate.php --fresh  — Drop all tables, re-migrate, and seed
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

echo "╔══════════════════════════════════════════╗\n";
echo "║   Cultiv — Database Migration Runner     ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

$args = $argv ?? [];
$shouldSeed  = in_array('--seed', $args);
$shouldFresh = in_array('--fresh', $args);

try {
    $db = Database::getInstance();
    echo "✓ Database connection successful.\n\n";

    // Fresh mode: drop all tables first
    if ($shouldFresh) {
        echo "⚠ FRESH mode: dropping all existing tables...\n";
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');

        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $db->exec("DROP TABLE IF EXISTS `{$table}`");
            echo "  ✗ Dropped: {$table}\n";
        }

        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        echo "\n";
    }

    // Run migration files in order
    $migrationsDir = __DIR__ . '/migrations';
    $files = glob($migrationsDir . '/*.sql');

    // Sort by filename (001_, 002_, etc.)
    sort($files);

    // Filter out seed.sql — it's run separately
    $migrationFiles = array_filter($files, fn($f) => basename($f) !== 'seed.sql');

    if (empty($migrationFiles)) {
        echo "No migration files found in /migrations/\n";
        exit(1);
    }

    echo "Running migrations...\n";
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);

        if (empty(trim($content))) {
            echo "  ⊘ Skipped (empty): {$filename}\n";
            continue;
        }

        // Split by semicolon and run each statement
        $statements = array_filter(array_map('trim', explode(';', $content)));
        $successCount = 0;
        $skipCount = 0;

        foreach ($statements as $sql) {
            try {
                $db->exec($sql);
                $successCount++;
            } catch (PDOException $e) {
                // If table or index already exists, that's okay for repeated runs
                if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Duplicate key name')) {
                    $skipCount++;
                } else {
                    echo "  ✗ FAILED: {$filename} (at statement: " . substr($sql, 0, 50) . "...)\n";
                    echo "    Error: " . $e->getMessage() . "\n";
                    exit(1);
                }
            }
        }

        if ($successCount > 0 && $skipCount === 0) {
            echo "  ✓ Migrated: {$filename}\n";
        } elseif ($successCount === 0 && $skipCount > 0) {
            echo "  ⊘ Skipped (all exist): {$filename}\n";
        } else {
            echo "  ◓ Partial: {$filename} ({$successCount} added, {$skipCount} skipped)\n";
        }
    }

    // Run seed data if requested
    if ($shouldSeed || $shouldFresh) {
        $seedFile = $migrationsDir . '/seed.sql';
        if (file_exists($seedFile)) {
            echo "\nSeeding data...\n";
            $sql = file_get_contents($seedFile);

            try {
                $db->exec($sql);
                echo "  ✓ Seed data loaded.\n";
            } catch (PDOException $e) {
                echo "  ✗ Seed failed: " . $e->getMessage() . "\n";
                exit(1);
            }
        } else {
            echo "\n⚠ No seed.sql file found.\n";
        }
    }

    echo "\n══════════════════════════════════════════\n";
    echo "✓ Migration complete!\n";

    if ($shouldSeed || $shouldFresh) {
        echo "\nTest accounts (all passwords: admin123):\n";
        echo "  Owner:      owner@cultiv.com\n";
        echo "  Supervisor: supervisor@cultiv.com\n";
        echo "  Worker:     worker@cultiv.com\n";
        echo "  Accountant: accountant@cultiv.com\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
