<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

try {
    $db = Database::connection();
    
    echo "Checking migrations table...\n";
    // Check if the database has other tables (meaning it's an existing setup)
    $existingTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $hasExistingData = false;
    foreach ($existingTables as $tbl) {
        if ($tbl !== 'migrations') {
            $hasExistingData = true;
            break;
        }
    }
    
    $check = in_array('migrations', $existingTables);
    $isNewMigrationsTable = !$check;
    
    if ($isNewMigrationsTable) {
        echo "Creating migrations table...\n";
        $db->exec("
            CREATE TABLE `migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL UNIQUE,
                `run_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    $stmt = $db->query("SELECT migration FROM migrations");
    $runMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $migrationsDir = __DIR__ . '/../database/migrations';
    if (!is_dir($migrationsDir)) {
        echo "Migrations directory not found.\n";
        exit(1);
    }

    $files = scandir($migrationsDir);
    $sqlFiles = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
    });
    
    sort($sqlFiles);
    
    $ranAny = false;

    foreach ($sqlFiles as $file) {
        if (!in_array($file, $runMigrations)) {
            if ($isNewMigrationsTable && $hasExistingData) {
                // Baseline: assume already run because it's an old DB getting the new migration system
                echo "Baselining migration (already applied manually): $file\n";
                $insertStmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $insertStmt->execute([$file]);
                $ranAny = true;
            } else {
                echo "Running migration: $file\n";
                $sql = file_get_contents($migrationsDir . '/' . $file);
                
                // execute the SQL
                $db->exec($sql);
                
                $insertStmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $insertStmt->execute([$file]);
                
                echo "Successfully ran: $file\n";
                $ranAny = true;
            }
        }
    }
    
    if (!$ranAny) {
        echo "Nothing to migrate.\n";
    }

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
