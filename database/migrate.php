<?php
/**
 * MoodifyMe Database Migration Script
 * Handles database setup for MySQL
 */

// Load appropriate configuration based on environment
if (isset($_ENV['VERCEL']) || strpos($_SERVER['HTTP_HOST'], '.vercel.app') !== false) {
    require_once dirname(__DIR__) . '/config.vercel.php';
} else {
    require_once dirname(__DIR__) . '/config.php';
}

require_once dirname(__DIR__) . '/includes/db_connect.php';

function runMigration() {
    $conn = getDbConnection();

    echo "Starting database migration for MySQL...\n";

    return runMySQLMigration($conn);
}



function runMySQLMigration($conn) {
    $schemaFile = __DIR__ . '/schema.sql';
    
    if (!file_exists($schemaFile)) {
        echo "Error: MySQL schema file not found!\n";
        return false;
    }
    
    $sql = file_get_contents($schemaFile);
    
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "MySQL migration completed successfully!\n";
        return true;
    } else {
        echo "MySQL migration failed: " . $conn->error . "\n";
        return false;
    }
}

function checkDatabaseConnection() {
    try {
        $conn = getDbConnection();
        $result = $conn->query("SELECT VERSION()");
        $version = $result->fetch_row()[0];
        echo "Connected to MySQL: $version\n";

        return true;
    } catch (Exception $e) {
        echo "Database connection failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli' || isset($_GET['migrate'])) {
    echo "MoodifyMe Database Migration\n";
    echo "============================\n\n";
    
    if (checkDatabaseConnection()) {
        if (runMigration()) {
            echo "\n✅ Migration completed successfully!\n";
        } else {
            echo "\n❌ Migration failed!\n";
            exit(1);
        }
    } else {
        echo "\n❌ Cannot connect to database!\n";
        exit(1);
    }
}
?>
