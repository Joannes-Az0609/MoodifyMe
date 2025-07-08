<?php
/**
 * MoodifyMe - Database Connection
 * Establishes connection to the database (MySQL or PostgreSQL)
 */

// Load appropriate configuration based on environment
$http_host = $_SERVER['HTTP_HOST'] ?? '';

if (isset($_ENV['RENDER']) || strpos($http_host, '.onrender.com') !== false) {
    require_once dirname(__DIR__) . '/config.render.php';
} elseif (isset($_ENV['VERCEL']) || strpos($http_host, '.vercel.app') !== false) {
    require_once dirname(__DIR__) . '/config.vercel.php';
} elseif (strpos($http_host, '.epizy.com') !== false ||
          strpos($http_host, '.rf.gd') !== false ||
          strpos($http_host, '.42web.io') !== false ||
          strpos($http_host, '.kesug.com') !== false) {
    require_once dirname(__DIR__) . '/config.infinityfree.php';
} else {
    // Default to localhost configuration
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDbConnection() {
    static $conn = null;

    if ($conn === null) {
        // Check if we're using PostgreSQL (Render) or MySQL (local/other)
        $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';

        if ($dbType === 'pgsql') {
            // PostgreSQL connection for Render
            try {
                $port = defined('DB_PORT') ? DB_PORT : 5432;
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME;
                $conn = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("PostgreSQL Connection failed: " . $e->getMessage());
            }
        } else {
            // MySQL connection for local/other environments using PDO
            try {
                $port = defined('DB_PORT') ? DB_PORT : 3306;
                $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $conn = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("MySQL Connection failed: " . $e->getMessage());
            }
        }
    }

    return $conn;
}

// Get the database connection
$conn = getDbConnection();
