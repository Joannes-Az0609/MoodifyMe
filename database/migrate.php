<?php
/**
 * MoodifyMe Database Migration Script
 * Handles database setup for MySQL
 */

// Include configuration and database connection
require_once dirname(__DIR__) . '/includes/db_connect.php';

function runMigration() {
    $conn = getDbConnection();

    echo "Starting database migration for MySQL...\n";

    return runMySQLMigration($conn);
}



function runMySQLMigration($conn) {
    $schemaFile = __DIR__ . '/schema.sql';

    if (!file_exists($schemaFile)) {
        echo "Error: Schema file not found!\n";
        return false;
    }

    $sql = file_get_contents($schemaFile);

    try {
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $conn->exec($statement);
            }
        }

        echo "Database migration completed successfully!\n";
        return true;
    } catch (PDOException $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        return false;
    }
}

function checkDatabaseConnection() {
    global $conn;

    try {
        $stmt = $conn->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        echo "Connected to database: " . $result['version'] . "\n";

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
