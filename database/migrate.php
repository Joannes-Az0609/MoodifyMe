<?php
/**
 * MoodifyMe Database Migration Script
 * Handles database setup for both MySQL and PostgreSQL
 */

// Include configuration
if (isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false) {
    require_once dirname(__DIR__) . '/config.render.php';
} else {
    require_once dirname(__DIR__) . '/config.php';
}

require_once dirname(__DIR__) . '/includes/db_connect.php';

function runMigration() {
    $conn = getDbConnection();
    $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';
    
    echo "Starting database migration for $dbType...\n";
    
    if ($dbType === 'postgresql') {
        return runPostgreSQLMigration($conn);
    } else {
        return runMySQLMigration($conn);
    }
}

function runPostgreSQLMigration($conn) {
    $schemaFile = __DIR__ . '/schema.postgresql.sql';
    
    if (!file_exists($schemaFile)) {
        echo "Error: PostgreSQL schema file not found!\n";
        return false;
    }
    
    $sql = file_get_contents($schemaFile);
    
    try {
        // Split SQL into individual statements
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            $conn->exec($statement);
        }
        
        echo "PostgreSQL migration completed successfully!\n";
        return true;
        
    } catch (PDOException $e) {
        echo "PostgreSQL migration failed: " . $e->getMessage() . "\n";
        return false;
    }
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
        $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';
        
        if ($dbType === 'postgresql') {
            $result = $conn->query("SELECT version()");
            $version = $result->fetch()['version'];
            echo "Connected to PostgreSQL: $version\n";
        } else {
            $result = $conn->query("SELECT VERSION()");
            $version = $result->fetch_row()[0];
            echo "Connected to MySQL: $version\n";
        }
        
        return true;
    } catch (Exception $e) {
        echo "Database connection failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Debug connection if requested
if (isset($_GET['debug'])) {
    echo "MoodifyMe Database Connection Debug\n";
    echo "===================================\n\n";

    echo "Environment Info:\n";
    echo "- PHP Version: " . phpversion() . "\n";
    echo "- PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Available' : 'NOT Available') . "\n";
    echo "- DB_TYPE: " . (defined('DB_TYPE') ? DB_TYPE : 'Not defined') . "\n";
    echo "- DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "\n";
    echo "- DATABASE_URL: " . (defined('DATABASE_URL') ? 'Set (' . strlen(DATABASE_URL) . ' chars)' : 'Not set') . "\n\n";

    // Test basic connection
    try {
        $conn = getDbConnection();
        echo "✅ Connection successful!\n";

        if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
            $result = $conn->query("SELECT version()");
            $version = $result->fetch()['version'];
            echo "PostgreSQL Version: " . substr($version, 0, 50) . "...\n";
        }
    } catch (Exception $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
    }

    exit;
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
