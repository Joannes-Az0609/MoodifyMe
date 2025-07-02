<?php
/**
 * MoodifyMe - Database Connection
 * Establishes connection to the MySQL database
 */

// Load appropriate configuration based on environment
if (isset($_ENV['VERCEL']) || strpos($_SERVER['HTTP_HOST'], '.vercel.app') !== false) {
    require_once dirname(__DIR__) . '/config.vercel.php';
} elseif (strpos($_SERVER['HTTP_HOST'], '.epizy.com') !== false ||
          strpos($_SERVER['HTTP_HOST'], '.rf.gd') !== false ||
          strpos($_SERVER['HTTP_HOST'], '.42web.io') !== false) {
    require_once dirname(__DIR__) . '/config.infinityfree.php';
} else {
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
