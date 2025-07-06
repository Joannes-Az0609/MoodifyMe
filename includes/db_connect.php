<?php
/**
 * MoodifyMe - Database Connection
 * Establishes connection to the MySQL database
 */

// Load appropriate configuration based on environment
$http_host = $_SERVER['HTTP_HOST'] ?? '';

if (isset($_ENV['VERCEL']) || strpos($http_host, '.vercel.app') !== false) {
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
 * @return mysqli Database connection object
 */
function getDbConnection() {
    static $conn = null;

    if ($conn === null) {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Set charset
        $conn->set_charset("utf8mb4");
    }

    return $conn;
}

// Get the database connection
$conn = getDbConnection();
