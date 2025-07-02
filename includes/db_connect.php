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

            // Try multiple connection approaches for Render PostgreSQL
            $connectionAttempts = [];

            // First try DATABASE_URL if available
            if (defined('DATABASE_URL')) {
                $databaseUrl = DATABASE_URL;
                // Convert postgres:// to pgsql:// for PDO
                $dsn = str_replace('postgres://', 'pgsql://', $databaseUrl);

                // Try different SSL modes
                $connectionAttempts[] = ['dsn' => $dsn . (strpos($dsn, '?') !== false ? '&' : '?') . 'sslmode=disable', 'method' => 'DATABASE_URL (no SSL)'];
                $connectionAttempts[] = ['dsn' => $dsn . (strpos($dsn, '?') !== false ? '&' : '?') . 'sslmode=prefer', 'method' => 'DATABASE_URL (prefer SSL)'];
                $connectionAttempts[] = ['dsn' => $dsn . (strpos($dsn, '?') !== false ? '&' : '?') . 'sslmode=require', 'method' => 'DATABASE_URL (require SSL)'];
            }

            // Fallback to individual parameters
            $port = defined('DB_PORT') ? DB_PORT : '5432';
            $connectionAttempts[] = ['dsn' => "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";sslmode=disable", 'method' => 'Individual params (no SSL)', 'user' => DB_USER, 'pass' => DB_PASS];
            $connectionAttempts[] = ['dsn' => "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";sslmode=prefer", 'method' => 'Individual params (prefer SSL)', 'user' => DB_USER, 'pass' => DB_PASS];

            $lastError = '';
            foreach ($connectionAttempts as $attempt) {
                try {
                    $user = isset($attempt['user']) ? $attempt['user'] : null;
                    $pass = isset($attempt['pass']) ? $attempt['pass'] : null;

                    $conn = new PDO($attempt['dsn'], $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 10
                    ]);

                    // Success! Break out of loop
                    break;
                } catch (PDOException $e) {
                    $lastError = $attempt['method'] . ': ' . $e->getMessage();
                    $conn = null; // Reset connection
                    continue;
                }
            }

            if ($conn === null) {
                die("PostgreSQL Connection failed after all attempts. Last error: " . $lastError);
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
