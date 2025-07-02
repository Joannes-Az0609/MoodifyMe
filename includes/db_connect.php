<?php
/**
 * MoodifyMe - Database Connection
 * Establishes connection to the database (MySQL or PostgreSQL)
 */

// Check if configuration is loaded
if (!defined('DB_HOST')) {
    // Load appropriate config based on environment
    if (isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false) {
        require_once dirname(__DIR__) . '/config.render.php';
    } else {
        require_once dirname(__DIR__) . '/config.php';
    }
}

/**
 * Get database connection
 * @return mysqli|PDO Database connection object
 */
function getDbConnection() {
    static $conn = null;

    if ($conn === null) {
        $dbType = defined('DB_TYPE') ? DB_TYPE : 'mysql';

        if ($dbType === 'postgresql') {
            // PostgreSQL connection using PDO (for Render)
            $port = defined('DB_PORT') ? DB_PORT : '5432';
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";sslmode=require";

            try {
                $conn = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30
                ]);
            } catch (PDOException $e) {
                die("PostgreSQL Connection failed: " . $e->getMessage());
            }
        } else {
            // MySQL connection using mysqli (for local development)
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Check connection
            if ($conn->connect_error) {
                die("MySQL Connection failed: " . $conn->connect_error);
            }

            // Set charset
            $conn->set_charset("utf8mb4");
        }
    }

    return $conn;
}

// Get the database connection
$conn = getDbConnection();
