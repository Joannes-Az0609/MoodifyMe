<?php
/**
 * Health Check Endpoint for Render
 * Returns JSON status for monitoring
 */

// Set content type to JSON
header('Content-Type: application/json');

// Basic health check
$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'service' => 'MoodifyMe',
    'version' => '1.0.0'
];

// Check if we can connect to database (optional)
try {
    // Load configuration and test database connection
    // Note: db_connect.php will load the appropriate config file
    require_once 'includes/db_connect.php';
    $conn = getDbConnection();
    
    if ($conn) {
        $health['database'] = 'connected';
        
        // Test a simple query
        if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
            // PostgreSQL
            $stmt = $conn->prepare("SELECT 1 as test");
            $stmt->execute();
            $result = $stmt->fetch();
        } else {
            // MySQL
            $result = $conn->query("SELECT 1 as test");
        }
        
        if ($result) {
            $health['database_query'] = 'success';
        }
    }
} catch (Exception $e) {
    $health['database'] = 'error';
    $health['database_error'] = $e->getMessage();
    // Don't fail health check for database issues during initial setup
}

// Return health status
echo json_encode($health, JSON_PRETTY_PRINT);
exit;
?>
